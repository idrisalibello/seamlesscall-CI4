<?php

namespace App\Modules\Customers\Controllers;

use App\Controllers\BaseController;
use App\Modules\Customers\Models\ChatMessageModel;
use CodeIgniter\API\ResponseTrait;
use Exception;

class ChatController extends BaseController
{
    use ResponseTrait;

    protected ChatMessageModel $chatModel;

    public function __construct()
    {
        $this->chatModel = new ChatMessageModel();
    }

    // ─── Auth guard ───────────────────────────────────────────────────────────

    private function requireChatAccess(): mixed
    {
        $user = service('request')->auth_payload ?? null;

        if (!$user || !isset($user->id) || !isset($user->role)) {
            return $this->failUnauthorized('Authentication required.');
        }

        if (!in_array($user->role, ['Customer', 'Admin'], true)) {
            return $this->failForbidden('Chat access is restricted to customers and admins.');
        }

        return null;
    }

    // ─── Normalise output ─────────────────────────────────────────────────────

    /**
     * Null-safe normalizer. Returns null when $row is null so callers can handle it
     * without a PHP TypeError from the array type-hint.
     */
    private function normalizeMessage(?array $row): ?array
    {
        if ($row === null) {
            return null;
        }

        return [
            'id'              => (int) ($row['id'] ?? 0),
            'customer_id'     => (int) ($row['customer_id'] ?? 0),
            'sender_role'     => (string) ($row['sender_role'] ?? 'customer'),
            'sender_id'       => isset($row['sender_id']) ? (int) $row['sender_id'] : null,
            'sender_name'     => $row['sender_name'] ?? null,
            'body'            => $row['body'] ?? null,
            'message_type'    => (string) ($row['message_type'] ?? 'text'),
            'attachment_url'  => $row['attachment_url'] ?? null,
            'attachment_name' => $row['attachment_name'] ?? null,
            'is_read'         => (bool) ($row['is_read'] ?? false),
            'created_at'      => $row['created_at'] ?? null,
            'updated_at'      => $row['updated_at'] ?? null,
        ];
    }

    /**
     * Build a normalised message from just the insert ID.
     * Re-fetches from DB so the data is always authoritative.
     * Falls back to a minimal object built from the insert data if find() returns null
     * (edge case: replication lag or strict transaction isolation).
     */
    private function normalizeFromInsertId(int $insertId, array $insertData): array
    {
        $row = $this->chatModel->find($insertId);

        if ($row !== null) {
            return $this->normalizeMessage($row);
        }

        // Fallback: build from the data we just inserted
        log_message('warning', "[ChatController] find($insertId) returned null after successful insert — using insert data as fallback");

        return [
            'id'              => $insertId,
            'customer_id'     => (int) ($insertData['customer_id'] ?? 0),
            'sender_role'     => (string) ($insertData['sender_role'] ?? 'customer'),
            'sender_id'       => isset($insertData['sender_id']) ? (int) $insertData['sender_id'] : null,
            'sender_name'     => null,
            'body'            => $insertData['body'] ?? null,
            'message_type'    => (string) ($insertData['message_type'] ?? 'text'),
            'attachment_url'  => $insertData['attachment_url'] ?? null,
            'attachment_name' => $insertData['attachment_name'] ?? null,
            'is_read'         => false,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => null,
        ];
    }

    // ─── Endpoints ────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/customer/chat/messages
     *
     * Returns paginated message history for the authenticated customer.
     * Query params: limit (default 50), offset (default 0)
     */
    public function getMessages(): mixed
    {
        if ($resp = $this->requireChatAccess()) {
            return $resp;
        }

        $user       = service('request')->auth_payload;
        $customerId = (int) $user->id;

        // Admins can query any customer's chat with ?customer_id=X
        if ($user->role === 'Admin') {
            $qId = (int) ($this->request->getGet('customer_id') ?? 0);
            if ($qId > 0) {
                $customerId = $qId;
            }
        }

        $limit  = max(1, min(100, (int) ($this->request->getGet('limit')  ?? 50)));
        $offset = max(0, (int) ($this->request->getGet('offset') ?? 0));

        try {
            // Mark agent messages as read when customer loads the chat
            if ($user->role === 'Customer') {
                $this->chatModel->markReadForCustomer($customerId);
            }

            $rows = $this->chatModel->getForCustomer($customerId, $limit, $offset);

            $normalized = [];
            foreach ($rows as $row) {
                $n = $this->normalizeMessage($row);
                if ($n !== null) {
                    $normalized[] = $n;
                }
            }

            return $this->respond([
                'data' => $normalized,
                'meta' => [
                    'customer_id' => $customerId,
                    'limit'       => $limit,
                    'offset'      => $offset,
                    'count'       => count($normalized),
                ],
            ]);
        } catch (Exception $e) {
            log_message('error', '[ChatController::getMessages] ' . $e->getMessage());
            return $this->failServerError('Could not load messages.');
        }
    }

    /**
     * POST /api/v1/customer/chat/messages
     *
     * Body: { "body": "..." }
     * Customer: sender_role = 'customer', customer_id = their own id.
     * Admin acting as agent: sender_role = 'agent', must include customer_id in body.
     */
    public function sendMessage(): mixed
    {
        if ($resp = $this->requireChatAccess()) {
            return $resp;
        }

        $user  = service('request')->auth_payload;
        $input = (array) ($this->request->getJSON(true) ?? []);

        if ($user->role === 'Customer') {
            $customerId = (int) $user->id;
            $senderRole = 'customer';
            $senderId   = null;
        } else {
            // Admin replying as agent — requires customer_id in body
            $customerId = (int) ($input['customer_id'] ?? 0);
            if ($customerId <= 0) {
                return $this->failValidationErrors(['customer_id' => 'customer_id is required when sending as an agent.']);
            }
            $senderRole = 'agent';
            $senderId   = (int) $user->id;
        }

        $rules = [
            'body' => 'required|min_length[1]|max_length[4000]',
        ];

        if (!$this->validateData($input, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $insertData = [
            'conversation_id' => 0,
            'customer_id'  => $customerId,
            'sender_role'  => $senderRole,
            'sender_id'    => $senderId,
            'body'         => trim((string) $input['body']),
            'message_type' => 'text',
            'is_read'      => 0,
        ];

        try {
            $insertId = $this->chatModel->insert($insertData, true);

            if ($insertId === false || $insertId === null) {
                $errors = $this->chatModel->errors();
                log_message('error', '[ChatController::sendMessage] insert() failed. Model errors: ' . json_encode($errors));
                return $this->failServerError('Failed to send message.');
            }

            $insertId = (int) $insertId;

            return $this->respondCreated([
                'message' => 'Message sent.',
                'data'    => $this->normalizeFromInsertId($insertId, $insertData),
            ]);
        } catch (Exception $e) {
            log_message('error', '[ChatController::sendMessage] ' . $e->getMessage());
            return $this->failServerError('Could not send message.');
        }
    }

    /**
     * POST /api/v1/customer/chat/attachments  (multipart, field: "file")
     */
    public function uploadAttachment(): mixed
    {
        if ($resp = $this->requireChatAccess()) {
            return $resp;
        }

        $user = service('request')->auth_payload;

        if ($user->role === 'Customer') {
            $customerId = (int) $user->id;
            $senderRole = 'customer';
            $senderId   = null;
        } else {
            $customerId = (int) ($this->request->getPost('customer_id') ?? 0);
            if ($customerId <= 0) {
                return $this->failValidationErrors(['customer_id' => 'customer_id is required.']);
            }
            $senderRole = 'agent';
            $senderId   = (int) $user->id;
        }

        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->failValidationErrors(['file' => 'A valid file is required.']);
        }

        if ($file->getSizeByUnit('mb') > 10) {
            return $this->failValidationErrors(['file' => 'File must be under 10 MB.']);
        }

        $allowedMime = [
            'image/jpeg', 'image/png', 'image/webp', 'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
        ];

        if (!in_array($file->getMimeType(), $allowedMime, true)) {
            return $this->failValidationErrors(['file' => 'File type not allowed.']);
        }

        try {
            $newName  = $file->getRandomName();
            $destPath = WRITEPATH . 'uploads/chat/';

            if (!is_dir($destPath)) {
                mkdir($destPath, 0755, true);
            }

            $file->move($destPath, $newName);

            $baseUrl     = rtrim(base_url(), '/');
            $publicUrl   = $baseUrl . '/uploads/chat/' . $newName;
            $origName    = $file->getClientName();
            $messageType = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'file';

            $insertData = [
                'conversation_id' => 0,
                'customer_id'     => $customerId,
                'sender_role'     => $senderRole,
                'sender_id'       => $senderId,
                'body'            => null,
                'message_type'    => $messageType,
                'attachment_url'  => $publicUrl,
                'attachment_name' => $origName,
                'is_read'         => 0,
            ];

            $insertId = $this->chatModel->insert($insertData, true);

            if ($insertId === false || $insertId === null) {
                $errors = $this->chatModel->errors();
                log_message('error', '[ChatController::uploadAttachment] insert() failed. Model errors: ' . json_encode($errors));
                return $this->failServerError('Failed to save attachment message.');
            }

            $insertId = (int) $insertId;

            return $this->respondCreated([
                'message' => 'Attachment uploaded.',
                'data'    => $this->normalizeFromInsertId($insertId, $insertData),
            ]);
        } catch (Exception $e) {
            log_message('error', '[ChatController::uploadAttachment] ' . $e->getMessage());
            return $this->failServerError('Could not upload attachment.');
        }
    }

    /**
     * GET /api/v1/customer/chat/unread-count
     */
    public function unreadCount(): mixed
    {
        if ($resp = $this->requireChatAccess()) {
            return $resp;
        }

        $user       = service('request')->auth_payload;
        $customerId = (int) $user->id;

        try {
            $count = $this->chatModel->countUnreadForCustomer($customerId);
            return $this->respond(['data' => ['unread_count' => $count]]);
        } catch (Exception $e) {
            log_message('error', '[ChatController::unreadCount] ' . $e->getMessage());
            return $this->failServerError('Could not retrieve unread count.');
        }
    }

    
}
