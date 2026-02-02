<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\ProviderDisputesModel;
use CodeIgniter\API\ResponseTrait;

class FinanceDisputesController extends BaseController
{
    use ResponseTrait;

    private function formatOutput(array $data): array
    {
        return array_map(function ($row) {
            if (is_object($row)) {
                $row = (array) $row;
            }
            foreach (['id', 'job_id', 'provider_id', 'customer_id', 'raised_by'] as $k) {
                if (isset($row[$k]) && $row[$k] !== null && $row[$k] !== '') {
                    $row[$k] = (int) $row[$k];
                }
            }
            return $row;
        }, $data);
    }

    /**
     * Finance -> Disputes (global, filter-driven)
     *
     * GET /api/v1/admin/finance/disputes
     * Required: from_date, to_date (YYYY-MM-DD)
     * Optional: provider_id, status, page, page_size
     */
    public function index()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinanceDisputesController: payload for index: ' . json_encode($user));
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

        $base = $db->table('provider_disputes d')
            ->select(
                'd.id, d.job_id, j.title as job_title, j.customer_id, c.name as customer, '
                . 'd.provider_id, p.name as provider, d.raised_by, rb.name as raised_by_name, '
                . 'd.status, d.reason, d.created_at, d.resolved_at'
            )
            ->join('jobs j', 'j.id = d.job_id', 'left')
            ->join('users p', 'p.id = d.provider_id', 'left')
            ->join('users c', 'c.id = j.customer_id', 'left')
            ->join('users rb', 'rb.id = d.raised_by', 'left')
            ->where('DATE(d.created_at) >=', $from)
            ->where('DATE(d.created_at) <=', $to);

        if ($providerId !== null && $providerId !== '') {
            $base->where('d.provider_id', (int) $providerId);
        }
        if ($status !== null && $status !== '') {
            $base->where('d.status', $status);
        }

        $totalRows = (clone $base)->countAllResults();
        $rows = (clone $base)
            ->orderBy('d.created_at', 'DESC')
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
     * Finance -> Disputes summary (range-aware)
     *
     * GET /api/v1/admin/finance/disputes/summary
     * Required: from_date, to_date
     * Optional: provider_id, status
     */
    public function summary()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinanceDisputesController: payload for summary: ' . json_encode($user));
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
        $q = $db->table('provider_disputes d')
            ->select('d.status, COUNT(*) as count_rows')
            ->where('DATE(d.created_at) >=', $from)
            ->where('DATE(d.created_at) <=', $to)
            ->groupBy('d.status');

        if ($providerId !== null && $providerId !== '') {
            $q->where('d.provider_id', (int) $providerId);
        }
        if ($status !== null && $status !== '') {
            $q->where('d.status', $status);
        }

        $rows = $q->get()->getResultArray();

        $byStatus = [
            'pending' => ['count' => 0],
            'resolved' => ['count' => 0],
            'dismissed' => ['count' => 0],
        ];

        $grandCount = 0;
        foreach ($rows as $r) {
            $s = $r['status'];
            $count = (int) $r['count_rows'];
            if (!isset($byStatus[$s])) {
                $byStatus[$s] = ['count' => 0];
            }
            $byStatus[$s] = ['count' => $count];
            $grandCount += $count;
        }

        return $this->respond([
            'data' => [
                'count' => $grandCount,
                'by_status' => $byStatus,
            ],
        ]);
    }

    /**
     * PATCH /api/v1/admin/finance/disputes/{id}/status
     * Body: { status: resolved|dismissed }
     */
    public function updateStatus($disputeId)
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinanceDisputesController: payload for updateStatus: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $body = $this->request->getJSON(true) ?? $this->request->getPost() ?? [];
        $status = $body['status'] ?? null;
        if (!in_array($status, ['resolved', 'dismissed'], true)) {
            return $this->failValidationErrors('status must be resolved or dismissed.');
        }

        $disputesModel = new ProviderDisputesModel();
        $row = $disputesModel->find($disputeId);
        if (!$row) {
            return $this->failNotFound('Dispute not found.');
        }

        $currentStatus = $row['status'] ?? null;
        if ($currentStatus !== 'pending') {
            return $this->failValidationErrors('Only pending disputes can be updated.');
        }

        $db = db_connect();
        $db->transStart();

        $disputesModel->update((int) $disputeId, [
            'status' => $status,
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);

        $activityLogModel = new ActivityLogModel();
        $activityLogModel->insert([
            'user_id' => (int) ($user->id ?? 0),
            'action' => 'dispute_status_update',
            'description' => 'Updated dispute #' . $disputeId . ' to ' . $status,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->failServerError('Failed to update dispute status.');
        }

        $updated = $disputesModel->find($disputeId);
        return $this->respond([
            'data' => $this->formatOutput([(array) $updated])[0],
        ]);
    }
}
