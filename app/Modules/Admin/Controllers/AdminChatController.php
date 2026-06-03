<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Modules\Customers\Models\ChatMessageModel;
use CodeIgniter\API\ResponseTrait;
use Exception;

class AdminChatController extends BaseController
{
    use ResponseTrait;

    protected ChatMessageModel $chatModel;

    public function __construct()
    {
        $this->chatModel = new ChatMessageModel();
    }

    private function requireAdmin(): mixed
    {
        $user = service('request')->auth_payload ?? null;

        if (!$user || !isset($user->id) || !isset($user->role)) {
            return $this->failUnauthorized('Authentication required.');
        }

        if ($user->role !== 'Admin') {
            return $this->failForbidden('Admin chat access is restricted to admins.');
        }

        return null;
    }

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

    private function normalizeFromInsertId(int $insertId, array $insertData): array
    {
        $row = $this->chatModel->find($insertId);

        if ($row !== null) {
            return $this->normalizeMessage($row);
        }

        return [
            'id'              => $insertId,
            'customer_id'     => (int) ($insertData['customer_id'] ?? 0),
            'sender_role'     => (string) ($insertData['sender_role'] ?? 'agent'),
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

    public function conversations(): mixed
    {
        if ($resp = $this->requireAdmin()) {
            return $resp;
        }

        try {
            $db = \Config\Database::connect();
            $rows = $db->table('chat_messages cm')
                ->select('cm.*, customers.name AS customer_name, customers.email AS customer_email, customers.phone AS customer_phone')
                ->join('users AS customers', 'customers.id = cm.customer_id', 'left')
                ->orderBy('cm.created_at', 'DESC')
                ->orderBy('cm.id', 'DESC')
                ->get()
                ->getResultArray();

            $conversations = [];

            foreach ($rows as $row) {
                $customerId = (int) ($row['customer_id'] ?? 0);
                if ($customerId <= 0) {
                    continue;
                }

                if (!isset($conversations[$customerId])) {
                    $conversations[$customerId] = [
                        'customer_id'    => $customerId,
                        'customer_name'  => $row['customer_name'] ?? 'Customer',
                        'customer_email' => $row['customer_email'] ?? null,
                        'customer_phone' => $row['customer_phone'] ?? null,
                        'last_message'   => $row['body'] ?? $row['attachment_name'] ?? 'Attachment',
                        'last_sender'    => $row['sender_role'] ?? 'customer',
                        'last_message_at'=> $row['created_at'] ?? null,
                        'unread_count'   => 0,
                    ];
                }

                if (($row['sender_role'] ?? '') === 'customer' && (int) ($row['is_read'] ?? 0) === 0) {
                    $conversations[$customerId]['unread_count']++;
                }
            }

            return $this->respond([
                'data' => array_values($conversations),
            ]);
        } catch (Exception $e) {
            log_message('error', '[AdminChatController::conversations] ' . $e->getMessage());
            return $this->failServerError('Could not load chat conversations.');
        }
    }

    public function messages(int $customerId): mixed
    {
        if ($resp = $this->requireAdmin()) {
            return $resp;
        }

        if ($customerId <= 0) {
            return $this->failValidationErrors(['customer_id' => 'A valid customer id is required.']);
        }

        $limit = max(1, min(100, (int) ($this->request->getGet('limit') ?? 50)));
        $offset = max(0, (int) ($this->request->getGet('offset') ?? 0));

        try {
            $db = \Config\Database::connect();
            $db->table('chat_messages')
                ->where('customer_id', $customerId)
                ->where('sender_role', 'customer')
                ->where('is_read', 0)
                ->update(['is_read' => 1]);

            $rows = $this->chatModel->getForCustomer($customerId, $limit, $offset);
            $messages = [];

            foreach ($rows as $row) {
                $message = $this->normalizeMessage($row);
                if ($message !== null) {
                    $messages[] = $message;
                }
            }

            return $this->respond([
                'data' => $messages,
                'meta' => [
                    'customer_id' => $customerId,
                    'limit'       => $limit,
                    'offset'      => $offset,
                    'count'       => count($messages),
                ],
            ]);
        } catch (Exception $e) {
            log_message('error', '[AdminChatController::messages] ' . $e->getMessage());
            return $this->failServerError('Could not load conversation messages.');
        }
    }

    public function sendMessage(int $customerId): mixed
    {
        if ($resp = $this->requireAdmin()) {
            return $resp;
        }

        if ($customerId <= 0) {
            return $this->failValidationErrors(['customer_id' => 'A valid customer id is required.']);
        }

        $admin = service('request')->auth_payload;
        $input = (array) ($this->request->getJSON(true) ?? []);

        $rules = [
            'body' => 'required|min_length[1]|max_length[4000]',
        ];

        if (!$this->validateData($input, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $insertData = [
            'conversation_id' => 0,
            'customer_id'     => $customerId,
            'sender_role'     => 'agent',
            'sender_id'       => (int) $admin->id,
            'body'            => trim((string) $input['body']),
            'message_type'    => 'text',
            'is_read'         => 0,
        ];

        try {
            $insertId = $this->chatModel->insert($insertData, true);

            if ($insertId === false || $insertId === null) {
                log_message('error', '[AdminChatController::sendMessage] insert failed: ' . json_encode($this->chatModel->errors()));
                return $this->failServerError('Failed to send message.');
            }

            return $this->respondCreated([
                'message' => 'Message sent.',
                'data'    => $this->normalizeFromInsertId((int) $insertId, $insertData),
            ]);
        } catch (Exception $e) {
            log_message('error', '[AdminChatController::sendMessage] ' . $e->getMessage());
            return $this->failServerError('Could not send message.');
        }
    }
}
