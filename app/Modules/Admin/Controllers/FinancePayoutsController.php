<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\PayoutsModel;
use CodeIgniter\API\ResponseTrait;

class FinancePayoutsController extends BaseController
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
            if (isset($row['id'])) {
                $row['id'] = (int) $row['id'];
            }
            if (isset($row['provider_id'])) {
                $row['provider_id'] = (int) $row['provider_id'];
            }
            return $row;
        }, $data);
    }

    /**
     * Finance -> Provider Payouts (global, filter-driven)
     *
     * GET /api/v1/admin/finance/payouts
     * Required: from_date, to_date (YYYY-MM-DD)
     * Optional: provider_id, status, page, page_size
     */
    public function index()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinancePayoutsController: payload for index: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $from = $this->request->getGet('from_date');
        $to   = $this->request->getGet('to_date');
        if (!$from || !$to) {
            return $this->failValidationErrors('from_date and to_date are required.');
        }

        $providerId = $this->request->getGet('provider_id');
        $status     = $this->request->getGet('status');

        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $pageSize = (int) ($this->request->getGet('page_size') ?? 20);
        if (!in_array($pageSize, [20, 50, 100], true)) {
            $pageSize = 20;
        }
        $offset = ($page - 1) * $pageSize;

        $db = db_connect();

        $base = $db->table('payouts p')
            ->select('p.id, p.provider_id, u.name as provider, p.amount, p.status, p.payment_method, p.transaction_id, p.requested_at, p.processed_at')
            ->join('users u', 'u.id = p.provider_id', 'left')
            ->where('DATE(p.requested_at) >=', $from)
            ->where('DATE(p.requested_at) <=', $to);

        if ($providerId !== null && $providerId !== '') {
            $base->where('p.provider_id', (int) $providerId);
        }
        if ($status !== null && $status !== '') {
            $base->where('p.status', $status);
        }

        $totalRows = (clone $base)->countAllResults();
        $rows = (clone $base)
            ->orderBy('p.requested_at', 'DESC')
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
     * Finance -> Provider Payouts summary (range-aware)
     *
     * GET /api/v1/admin/finance/payouts/summary
     * Required: from_date, to_date
     * Optional: provider_id, status
     */
    public function summary()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinancePayoutsController: payload for summary: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $from = $this->request->getGet('from_date');
        $to   = $this->request->getGet('to_date');
        if (!$from || !$to) {
            return $this->failValidationErrors('from_date and to_date are required.');
        }

        $providerId = $this->request->getGet('provider_id');
        $status     = $this->request->getGet('status');

        $db = db_connect();
        $q = $db->table('payouts p')
            ->select('p.status, COALESCE(SUM(p.amount), 0) as total_amount, COUNT(*) as count_rows')
            ->where('DATE(p.requested_at) >=', $from)
            ->where('DATE(p.requested_at) <=', $to)
            ->groupBy('p.status');

        if ($providerId !== null && $providerId !== '') {
            $q->where('p.provider_id', (int) $providerId);
        }
        if ($status !== null && $status !== '') {
            $q->where('p.status', $status);
        }

        $rows = $q->get()->getResultArray();

        $byStatus = [
            'pending' => ['total' => 0.0, 'count' => 0],
            'processed' => ['total' => 0.0, 'count' => 0],
            'failed' => ['total' => 0.0, 'count' => 0],
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
     * PATCH /api/v1/admin/finance/payouts/{id}/mark-paid
     * Body: { payment_method, transaction_id }
     */
    public function markPaid($payoutId)
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinancePayoutsController: payload for markPaid: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $body = $this->request->getJSON(true) ?? [];
        $paymentMethod = $body['payment_method'] ?? null;
        $transactionId = $body['transaction_id'] ?? null;
        if (!$paymentMethod || !$transactionId) {
            return $this->failValidationErrors('payment_method and transaction_id are required.');
        }

        $payoutsModel = new PayoutsModel();
        $row = $payoutsModel->find($payoutId);
        if (!$row) {
            return $this->failNotFound('Payout not found.');
        }
        $status = $row['status'] ?? null;
        if (!in_array($status, ['pending', 'failed'], true)) {
            return $this->failValidationErrors('Only pending or failed payouts can be marked as paid.');
        }


        $db = db_connect();
        $db->transStart();

        $payoutsModel->update($payoutId, [
            'status' => 'processed',
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'processed_at' => date('Y-m-d H:i:s'),
        ]);

        $activityLogModel = new ActivityLogModel();
        $activityLogModel->insert([
            'user_id' => (int) ($user->id ?? 0),
            'action' => 'payout_mark_paid',
            'description' => 'Marked payout #' . $payoutId . ' as processed. txn=' . $transactionId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->failServerError('Failed to mark payout as paid.');
        }

        $updated = $payoutsModel->find($payoutId);
        return $this->respond(['data' => $updated]);
    }

    /**
     * PATCH /api/v1/admin/finance/payouts/{id}/mark-failed
     * Body: { reason }
     */
    public function markFailed($payoutId)
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinancePayoutsController: payload for markFailed: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $body = $this->request->getJSON(true) ?? [];
        $reason = trim((string) ($body['reason'] ?? ''));
        if ($reason === '') {
            return $this->failValidationErrors('reason is required.');
        }

        $payoutsModel = new PayoutsModel();
        $row = $payoutsModel->find($payoutId);
        if (!$row) {
            return $this->failNotFound('Payout not found.');
        }
        if (!in_array($row['status'], ['pending', 'failed'], true)) {
            return $this->failValidationErrors(
                'Only pending or failed payouts can be marked as paid.'
            );
        }


        $db = db_connect();
        $db->transStart();

        $payoutsModel->update($payoutId, [
            'status' => 'failed',
            'processed_at' => date('Y-m-d H:i:s'),
        ]);

        $activityLogModel = new ActivityLogModel();
        $activityLogModel->insert([
            'user_id' => (int) ($user->id ?? 0),
            'action' => 'payout_mark_failed',
            'description' => 'Marked payout #' . $payoutId . ' as failed. reason=' . $reason,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->failServerError('Failed to mark payout as failed.');
        }

        $updated = $payoutsModel->find($payoutId);
        return $this->respond(['data' => $updated]);
    }
}
