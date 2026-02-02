<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\RefundModel;
use CodeIgniter\API\ResponseTrait;

class FinanceRefundsController extends BaseController
{
    use ResponseTrait;

    /**
     * Ensure IDs are integers and normalize object/array outputs.
     */
    private function formatOutput(array $data): array
    {
        return array_map(function ($row) {
            if (is_object($row)) {
                $row = (array) $row;
            }
            foreach (['id', 'user_id', 'transaction_id', 'processed_by'] as $k) {
                if (isset($row[$k]) && $row[$k] !== null && $row[$k] !== '') {
                    $row[$k] = (int) $row[$k];
                }
            }
            return $row;
        }, $data);
    }

    /**
     * Finance -> Refunds (global, filter-driven)
     *
     * GET /api/v1/admin/finance/refunds
     * Required: from_date, to_date (YYYY-MM-DD)
     * Optional: user_id, status, page, page_size
     */
    public function index()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinanceRefundsController: payload for index: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $from = $this->request->getGet('from_date');
        $to   = $this->request->getGet('to_date');
        if (!$from || !$to) {
            return $this->failValidationErrors('from_date and to_date are required.');
        }

        $userId = $this->request->getGet('user_id');
        $status = $this->request->getGet('status');

        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $pageSize = (int) ($this->request->getGet('page_size') ?? 20);
        if (!in_array($pageSize, [20, 50, 100], true)) {
            $pageSize = 20;
        }
        $offset = ($page - 1) * $pageSize;

        $db = db_connect();

        $base = $db->table('refunds r')
            ->select('r.id, r.user_id, u.name as customer, u.email, u.phone, r.transaction_id, r.amount, r.reason, r.status, r.submitted_at, r.processed_by, r.processed_at')
            ->join('users u', 'u.id = r.user_id', 'left')
            ->where('DATE(r.submitted_at) >=', $from)
            ->where('DATE(r.submitted_at) <=', $to);

        if ($userId !== null && $userId !== '') {
            $base->where('r.user_id', (int) $userId);
        }
        if ($status !== null && $status !== '') {
            $base->where('r.status', $status);
        }

        $totalRows = (clone $base)->countAllResults();
        $rows = (clone $base)
            ->orderBy('r.submitted_at', 'DESC')
            ->limit($pageSize, $offset)
            ->get()
            ->getResultArray();

        $totalPages = (int) ceil($totalRows / $pageSize);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        return $this->respond([
            'data' => [
                'rows' => $this->formatOutput($rows),
                'pagination' => [
                    'page' => $page,
                    'page_size' => $pageSize,
                    'total_rows' => $totalRows,
                    'total_pages' => $totalPages,
                ],
            ],
        ]);
    }

    /**
     * Finance -> Refunds summary (range-aware)
     *
     * GET /api/v1/admin/finance/refunds/summary
     * Required: from_date, to_date
     * Optional: user_id, status
     */
    public function summary()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinanceRefundsController: payload for summary: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $from = $this->request->getGet('from_date');
        $to   = $this->request->getGet('to_date');
        if (!$from || !$to) {
            return $this->failValidationErrors('from_date and to_date are required.');
        }

        $userId = $this->request->getGet('user_id');
        $status = $this->request->getGet('status');

        $db = db_connect();
        $q = $db->table('refunds r')
            ->select('r.status, COALESCE(SUM(r.amount), 0) as total_amount, COUNT(*) as count_rows')
            ->where('DATE(r.submitted_at) >=', $from)
            ->where('DATE(r.submitted_at) <=', $to)
            ->groupBy('r.status');

        if ($userId !== null && $userId !== '') {
            $q->where('r.user_id', (int) $userId);
        }
        if ($status !== null && $status !== '') {
            $q->where('r.status', $status);
        }

        $rows = $q->get()->getResultArray();

        $byStatus = [
            'pending' => ['total' => 0.0, 'count' => 0],
            'approved' => ['total' => 0.0, 'count' => 0],
            'rejected' => ['total' => 0.0, 'count' => 0],
        ];

        $grandTotal = 0.0;
        $grandCount = 0;
        foreach ($rows as $r) {
            $s = $r['status'];
            $total = (float) $r['total_amount'];
            $count = (int) $r['count_rows'];
            if (!isset($byStatus[$s])) {
                $byStatus[$s] = ['total' => 0.0, 'count' => 0];
            }
            $byStatus[$s] = ['total' => $total, 'count' => $count];
            $grandTotal += $total;
            $grandCount += $count;
        }

        return $this->respond([
            'data' => [
                'total' => $grandTotal,
                'count' => $grandCount,
                'by_status' => $byStatus,
            ],
        ]);
    }

    /**
     * PATCH /api/v1/admin/finance/refunds/{id}/status
     * POST  /api/v1/admin/refunds/{id}/status (backward-compat)
     * Body: { status: approved|rejected }
     */
    public function updateStatus($refundId)
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinanceRefundsController: payload for updateStatus: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $body = $this->request->getJSON(true) ?? $this->request->getPost() ?? [];
        $status = $body['status'] ?? null;
        if (!in_array($status, ['approved', 'rejected'], true)) {
            return $this->failValidationErrors('status must be approved or rejected.');
        }

        $refundModel = new RefundModel();
        $row = $refundModel->find($refundId);
        if (!$row) {
            return $this->failNotFound('Refund not found.');
        }

        $currentStatus = $row['status'] ?? null;
        if ($currentStatus !== 'pending') {
            return $this->failValidationErrors('Only pending refunds can be updated.');
        }

        $db = db_connect();
        $db->transStart();

        $refundModel->update((int) $refundId, [
            'status' => $status,
            'processed_by' => (int) ($user->id ?? 0),
            'processed_at' => date('Y-m-d H:i:s'),
        ]);

        $activityLogModel = new ActivityLogModel();
        $activityLogModel->insert([
            'user_id' => (int) ($user->id ?? 0),
            'action' => 'refund_status_update',
            'description' => 'Updated refund #' . $refundId . ' to ' . $status,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->failServerError('Failed to update refund status.');
        }

        $updated = $refundModel->find($refundId);
        return $this->respond([
            'data' => $this->formatOutput([(array) $updated])[0],
        ]);
    }
}
