<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class FinanceLedgerController extends BaseController
{
    use ResponseTrait;

    private function normalizeRows(array $rows): array
    {
        return array_map(function ($row) {
            if (is_object($row)) {
                $row = (array) $row;
            }
            foreach (['id', 'user_id'] as $k) {
                if (isset($row[$k]) && $row[$k] !== null && $row[$k] !== '') {
                    $row[$k] = (int) $row[$k];
                }
            }
            if (isset($row['amount'])) {
                $row['amount'] = (float) $row['amount'];
            }
            return $row;
        }, $rows);
    }

    /**
     * GET /api/v1/admin/finance/ledger
     * Required: from_date, to_date (YYYY-MM-DD)
     * Optional: user_id, transaction_type, reference, page, page_size
     */
    public function index()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinanceLedgerController: payload for index: ' . json_encode($user));

        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $from = $this->request->getGet('from_date');
        $to   = $this->request->getGet('to_date');
        if (!$from || !$to) {
            return $this->failValidationErrors('from_date and to_date are required.');
        }

        $userId = $this->request->getGet('user_id');
        $type   = $this->request->getGet('transaction_type');
        $ref    = $this->request->getGet('reference');

        $page = max(1, (int)($this->request->getGet('page') ?? 1));
        $pageSize = (int)($this->request->getGet('page_size') ?? 20);
        if (!in_array($pageSize, [20, 50, 100], true)) {
            $pageSize = 20;
        }
        $offset = ($page - 1) * $pageSize;

        $db = db_connect();

        $base = $db->table('ledger l')
            ->select('l.id, l.user_id, u.name as user_name, u.role as user_role, l.transaction_type, l.amount, l.description, l.reference, l.created_at')
            ->join('users u', 'u.id = l.user_id', 'left')
            ->where('DATE(l.created_at) >=', $from)
            ->where('DATE(l.created_at) <=', $to);

        if ($userId !== null && $userId !== '') {
            $base->where('l.user_id', (int) $userId);
        }

        if ($type !== null && $type !== '') {
            $base->where('l.transaction_type', $type);
        }

        if ($ref !== null && $ref !== '') {
            // partial match (safe)
            $base->like('l.reference', $ref);
        }

        $totalRows = (clone $base)->countAllResults();

        $rows = (clone $base)
            ->orderBy('l.created_at', 'DESC')
            ->limit($pageSize, $offset)
            ->get()
            ->getResultArray();

        $totalPages = (int) ceil($totalRows / $pageSize);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        return $this->respond([
            'data' => [
                'rows' => $this->normalizeRows($rows),
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
     * GET /api/v1/admin/finance/ledger/summary
     * Required: from_date, to_date
     * Optional: user_id, transaction_type, reference
     */
    public function summary()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinanceLedgerController: payload for summary: ' . json_encode($user));

        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $from = $this->request->getGet('from_date');
        $to   = $this->request->getGet('to_date');
        if (!$from || !$to) {
            return $this->failValidationErrors('from_date and to_date are required.');
        }

        $userId = $this->request->getGet('user_id');
        $type   = $this->request->getGet('transaction_type');
        $ref    = $this->request->getGet('reference');

        $db = db_connect();

        $q = $db->table('ledger l')
            ->select('l.transaction_type, COALESCE(SUM(l.amount), 0) as total_amount, COUNT(*) as count_rows')
            ->where('DATE(l.created_at) >=', $from)
            ->where('DATE(l.created_at) <=', $to)
            ->groupBy('l.transaction_type');

        if ($userId !== null && $userId !== '') {
            $q->where('l.user_id', (int) $userId);
        }

        if ($type !== null && $type !== '') {
            $q->where('l.transaction_type', $type);
        }

        if ($ref !== null && $ref !== '') {
            $q->like('l.reference', $ref);
        }

        $rows = $q->get()->getResultArray();

        $byType = [];
        $grandTotal = 0.0;
        $grandCount = 0;

        foreach ($rows as $r) {
            $t = $r['transaction_type'] ?? 'unknown';
            $total = (float) $r['total_amount'];
            $count = (int) $r['count_rows'];

            $byType[$t] = [
                'total' => $total,
                'count' => $count,
            ];

            $grandTotal += $total;
            $grandCount += $count;
        }

        return $this->respond([
            'data' => [
                'total' => $grandTotal,
                'count' => $grandCount,
                'by_type' => $byType,
            ],
        ]);
    }
}
