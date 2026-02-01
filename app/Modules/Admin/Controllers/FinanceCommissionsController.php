<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\FinanceSettingsModel;
use CodeIgniter\API\ResponseTrait;

class FinanceCommissionsController extends BaseController
{
    use ResponseTrait;

    private const KEY_RATE = 'platform_commission_rate';
    private const DEFAULT_RATE = '0.15';

    /**
     * Finance -> Platform Commissions (from earnings)
     *
     * GET /api/v1/admin/finance/commissions
     * Required: from_date, to_date (YYYY-MM-DD)
     * Optional: provider_id, status (confirmed|unconfirmed), page, page_size
     *
     * Behavior:
     * - confirmed: uses locked commission_rate/commission_amount/provider_net
     * - unconfirmed: uses current global rate to project values
     */
    public function index()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinanceCommissionsController: payload for index: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $from = $this->request->getGet('from_date');
        $to   = $this->request->getGet('to_date');
        if (!$from || !$to) {
            return $this->failValidationErrors('from_date and to_date are required.');
        }

        $providerId = $this->request->getGet('provider_id');
        $statusFilter = $this->request->getGet('status'); // confirmed|unconfirmed|null

        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $pageSize = (int) ($this->request->getGet('page_size') ?? 20);
        if (!in_array($pageSize, [20, 50, 100], true)) {
            $pageSize = 20;
        }
        $offset = ($page - 1) * $pageSize;

        $settings = new FinanceSettingsModel();
        $currentRate = (float) $settings->getValue(self::KEY_RATE, self::DEFAULT_RATE);

        $db = db_connect();

        $base = $db->table('earnings e')
            ->select('
                e.id,
                e.provider_id,
                u.name as provider,
                e.amount as gross_amount,
                e.description,
                e.job_id,
                e.created_at,
                e.commission_status,
                e.commission_rate,
                e.commission_amount,
                e.provider_net,
                e.commission_confirmed_at,
                e.commission_confirmed_by
            ')
            ->join('users u', 'u.id = e.provider_id', 'left')
            ->where('DATE(e.created_at) >=', $from)
            ->where('DATE(e.created_at) <=', $to);

        if ($providerId !== null && $providerId !== '') {
            $base->where('e.provider_id', (int) $providerId);
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            if (!in_array($statusFilter, ['confirmed', 'unconfirmed'], true)) {
                return $this->failValidationErrors('status must be confirmed or unconfirmed.');
            }
            $base->where('e.commission_status', $statusFilter);
        }

        $totalRows = (clone $base)->countAllResults();
        $rows = (clone $base)
            ->orderBy('e.created_at', 'DESC')
            ->limit($pageSize, $offset)
            ->get()
            ->getResultArray();

        $normalized = array_map(function ($r) use ($currentRate) {
            $gross = (float) ($r['gross_amount'] ?? 0);

            $isConfirmed = (($r['commission_status'] ?? 'unconfirmed') === 'confirmed')
                && $r['commission_amount'] !== null
                && $r['provider_net'] !== null
                && $r['commission_rate'] !== null;

            if ($isConfirmed) {
                $rate = (float) $r['commission_rate'];
                $commission = (float) $r['commission_amount'];
                $net = (float) $r['provider_net'];
                $statusLabel = 'Confirmed';
            } else {
                $rate = (float) $currentRate;
                $commission = round($gross * $rate, 2);
                $net = round($gross - $commission, 2);
                $statusLabel = 'Unconfirmed';
            }

            return [
                'id' => (int) ($r['id'] ?? 0),
                'reference' => 'COM-' . ($r['id'] ?? ''),
                'earning_id' => (int) ($r['id'] ?? 0),

                'provider_id' => (int) ($r['provider_id'] ?? 0),
                'provider' => $r['provider'] ?? 'Unknown',
                'job_id' => $r['job_id'] !== null ? (int) $r['job_id'] : null,

                'gross_amount' => $gross,
                'commission_rate' => $rate,
                'commission_amount' => $commission,
                'provider_net' => $net,

                'description' => $r['description'] ?? null,
                'date' => $r['created_at'] ?? null,
                'created_at' => $r['created_at'] ?? null,

                // status + audit
                'commission_status' => $r['commission_status'] ?? 'unconfirmed',
                'status' => $statusLabel,
                'commission_confirmed_at' => $r['commission_confirmed_at'] ?? null,
                'commission_confirmed_by' => $r['commission_confirmed_by'] !== null ? (int) $r['commission_confirmed_by'] : null,
            ];
        }, $rows);

        $totalPages = (int) ceil($totalRows / $pageSize);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        return $this->respond([
            'data' => [
                'rate' => $currentRate, // current global rate (used for projection)
                'rows' => $normalized,
                'pagination' => [
                    'page' => $page,
                    'page_size' => $pageSize,
                    'total_rows' => (int) $totalRows,
                    'total_pages' => $totalPages,
                ],
            ],
        ]);
    }

    /**
     * Summary with clarity:
     * - confirmed totals use locked values
     * - unconfirmed totals are projected using current global rate
     *
     * GET /api/v1/admin/finance/commissions/summary
     * Required: from_date, to_date
     * Optional: provider_id, status (confirmed|unconfirmed)
     */
    public function summary()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinanceCommissionsController: payload for summary: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $from = $this->request->getGet('from_date');
        $to   = $this->request->getGet('to_date');
        if (!$from || !$to) {
            return $this->failValidationErrors('from_date and to_date are required.');
        }

        $providerId = $this->request->getGet('provider_id');
        $statusFilter = $this->request->getGet('status');

        $settings = new FinanceSettingsModel();
        $currentRate = (float) $settings->getValue(self::KEY_RATE, self::DEFAULT_RATE);

        $db = db_connect();

        $q = $db->table('earnings e')
            ->select('
                COALESCE(SUM(e.amount), 0) as gross_total,
                COALESCE(SUM(CASE WHEN e.commission_status = "confirmed" THEN e.amount ELSE 0 END), 0) as confirmed_gross_total,
                COALESCE(SUM(CASE WHEN e.commission_status = "unconfirmed" OR e.commission_status IS NULL THEN e.amount ELSE 0 END), 0) as unconfirmed_gross_total,
                COALESCE(SUM(CASE WHEN e.commission_status = "confirmed" THEN e.commission_amount ELSE 0 END), 0) as confirmed_commission_total,
                COALESCE(SUM(CASE WHEN e.commission_status = "confirmed" THEN e.provider_net ELSE 0 END), 0) as confirmed_provider_net_total
            ')
            ->where('DATE(e.created_at) >=', $from)
            ->where('DATE(e.created_at) <=', $to);

        if ($providerId !== null && $providerId !== '') {
            $q->where('e.provider_id', (int) $providerId);
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            if (!in_array($statusFilter, ['confirmed', 'unconfirmed'], true)) {
                return $this->failValidationErrors('status must be confirmed or unconfirmed.');
            }
            $q->where('e.commission_status', $statusFilter);
        }

        $row = $q->get()->getRowArray();

        $grossTotal = (float) ($row['gross_total'] ?? 0);
        $confirmedGross = (float) ($row['confirmed_gross_total'] ?? 0);
        $unconfirmedGross = (float) ($row['unconfirmed_gross_total'] ?? 0);

        $confirmedCommission = (float) ($row['confirmed_commission_total'] ?? 0);
        $confirmedNet = (float) ($row['confirmed_provider_net_total'] ?? 0);

        // projected for unconfirmed using current rate
        $projectedUnconfirmedCommission = round($unconfirmedGross * $currentRate, 2);
        $projectedUnconfirmedNet = round($unconfirmedGross - $projectedUnconfirmedCommission, 2);

        return $this->respond([
            'data' => [
                'rate' => $currentRate,

                'gross_total' => $grossTotal,

                'confirmed' => [
                    'gross_total' => $confirmedGross,
                    'commission_total' => round($confirmedCommission, 2),
                    'provider_net_total' => round($confirmedNet, 2),
                ],

                'unconfirmed' => [
                    'gross_total' => $unconfirmedGross,
                    'commission_total' => $projectedUnconfirmedCommission,
                    'provider_net_total' => $projectedUnconfirmedNet,
                ],
            ],
        ]);
    }

    /**
     * Lock commission on an earning row using the CURRENT global rate.
     *
     * PATCH /api/v1/admin/finance/commissions/{earningId}/confirm
     */
    public function confirm($earningId)
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'FinanceCommissionsController: payload for confirm: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $earningId = (int) $earningId;
        if ($earningId <= 0) {
            return $this->failValidationErrors('Invalid earning id.');
        }

        $settings = new FinanceSettingsModel();
        $rate = (float) $settings->getValue(self::KEY_RATE, self::DEFAULT_RATE);

        $db = db_connect();
        $row = $db->table('earnings')->where('id', $earningId)->get()->getRowArray();
        if (!$row) {
            return $this->failNotFound('Earning not found.');
        }

        $status = $row['commission_status'] ?? 'unconfirmed';
        if ($status === 'confirmed') {
            return $this->failValidationErrors('Commission already confirmed for this earning.');
        }

        $gross = (float) ($row['amount'] ?? 0);
        $commission = round($gross * $rate, 2);
        $net = round($gross - $commission, 2);

        $now = date('Y-m-d H:i:s');

        $db->transStart();

        $db->table('earnings')
            ->where('id', $earningId)
            ->update([
                'commission_status' => 'confirmed',
                'commission_rate' => $rate,
                'commission_amount' => $commission,
                'provider_net' => $net,
                'commission_confirmed_at' => $now,
                'commission_confirmed_by' => (int) ($user->id ?? 0),
            ]);

        $activityLogModel = new ActivityLogModel();
        $activityLogModel->insert([
            'user_id' => (int) ($user->id ?? 0),
            'action' => 'commission_confirm',
            'description' => 'Confirmed commission for earning #' . $earningId . ' rate=' . $rate . ' commission=' . $commission . ' net=' . $net,
            'created_at' => $now,
        ]);

        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->failServerError('Failed to confirm commission.');
        }

        $updated = $db->table('earnings')->where('id', $earningId)->get()->getRowArray();

        return $this->respond([
            'data' => [
                'earning_id' => $earningId,
                'commission_status' => $updated['commission_status'] ?? 'confirmed',
                'commission_rate' => (float) ($updated['commission_rate'] ?? $rate),
                'commission_amount' => (float) ($updated['commission_amount'] ?? $commission),
                'provider_net' => (float) ($updated['provider_net'] ?? $net),
                'commission_confirmed_at' => $updated['commission_confirmed_at'] ?? $now,
                'commission_confirmed_by' => $updated['commission_confirmed_by'] !== null ? (int) $updated['commission_confirmed_by'] : null,
            ],
        ]);
    }
}
