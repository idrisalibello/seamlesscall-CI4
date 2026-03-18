<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class AdminReportsController extends BaseController
{
    use ResponseTrait;

    protected function ensureAdmin()
    {
        $user = service('request')->auth_payload ?? null;

        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        return true;
    }

    protected function range(): array
    {
        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');

        $from = $from ?: date('Y-m-01');
        $to   = $to ?: date('Y-m-d');

        return [$from . ' 00:00:00', $to . ' 23:59:59'];
    }

    public function overview()
    {
        if (($auth = $this->ensureAdmin()) !== true) {
            return $auth;
        }

        [$from, $to] = $this->range();
        $db = db_connect();

        $jobsTotal = (int) $db->table('jobs')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->countAllResults();

        $jobsCompleted = (int) $db->table('jobs')
            ->where('status', 'completed')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->countAllResults();

        $jobsCancelled = (int) $db->table('jobs')
            ->where('status', 'cancelled')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->countAllResults();

        $jobsEscalated = (int) $db->table('jobs')
            ->where('status', 'escalated')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->countAllResults();

        $customers = (int) $db->table('users')
            ->where('role', 'Customer')
            ->countAllResults();

        $providers = (int) $db->table('users')
            ->where('role', 'Provider')
            ->countAllResults();

        $earnings = (float) (($db->table('earnings')
            ->selectSum('amount', 'total_amount')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->get()
            ->getRowArray()['total_amount'] ?? 0));

        $payouts = (float) (($db->table('payouts')
            ->selectSum('amount', 'total_amount')
            ->where('requested_at >=', $from)
            ->where('requested_at <=', $to)
            ->get()
            ->getRowArray()['total_amount'] ?? 0));

        $refunds = (float) (($db->table('refunds')
            ->selectSum('amount', 'total_amount')
            ->where('submitted_at >=', $from)
            ->where('submitted_at <=', $to)
            ->get()
            ->getRowArray()['total_amount'] ?? 0));

        return $this->respond([
            'data' => [
                'summary' => [
                    'jobs_total'      => $jobsTotal,
                    'jobs_completed'  => $jobsCompleted,
                    'jobs_cancelled'  => $jobsCancelled,
                    'jobs_escalated'  => $jobsEscalated,
                    'customers_total' => $customers,
                    'providers_total' => $providers,
                    'earnings_total'  => $earnings,
                    'payouts_total'   => $payouts,
                    'refunds_total'   => $refunds,
                ],
            ],
        ]);
    }

    public function operations()
    {
        if (($auth = $this->ensureAdmin()) !== true) {
            return $auth;
        }

        [$from, $to] = $this->range();
        $status = $this->request->getGet('status');
        $db = db_connect();

        $builder = $db->table('jobs j')
            ->select('j.id, j.title, j.status, j.scheduled_time, j.created_at, s.name AS service_name, c.name AS customer_name, p.name AS provider_name')
            ->join('services s', 's.id = j.service_id', 'left')
            ->join('users c', 'c.id = j.customer_id', 'left')
            ->join('users p', 'p.id = j.provider_id', 'left')
            ->where('j.created_at >=', $from)
            ->where('j.created_at <=', $to);

        if (!empty($status)) {
            $builder->where('j.status', $status);
        }

        $rows = $builder
            ->orderBy('j.id', 'DESC')
            ->get()
            ->getResultArray();

        $statusCounts = [];
        foreach (['pending', 'active', 'scheduled', 'completed', 'cancelled', 'escalated'] as $s) {
            $statusCounts[$s] = (int) $db->table('jobs')
                ->where('status', $s)
                ->where('created_at >=', $from)
                ->where('created_at <=', $to)
                ->countAllResults();
        }

        return $this->respond([
            'data' => [
                'summary' => $statusCounts,
                'rows' => $rows,
            ],
        ]);
    }

    public function providers()
    {
        if (($auth = $this->ensureAdmin()) !== true) {
            return $auth;
        }

        [$from, $to] = $this->range();
        $db = db_connect();

        $providers = $db->table('users u')
            ->select('u.id, u.name')
            ->where('u.role', 'Provider')
            ->orderBy('u.name', 'ASC')
            ->get()
            ->getResultArray();

        $rows = [];

        foreach ($providers as $provider) {
            $providerId = (int) $provider['id'];

            $jobsCompleted = (int) $db->table('jobs')
                ->where('provider_id', $providerId)
                ->where('status', 'completed')
                ->where('created_at >=', $from)
                ->where('created_at <=', $to)
                ->countAllResults();

            $jobsTotal = (int) $db->table('jobs')
                ->where('provider_id', $providerId)
                ->where('created_at >=', $from)
                ->where('created_at <=', $to)
                ->countAllResults();

            $avgRating = (float) (($db->table('provider_ratings')
                ->selectAvg('rating', 'avg_rating')
                ->where('provider_id', $providerId)
                ->where('created_at >=', $from)
                ->where('created_at <=', $to)
                ->get()
                ->getRowArray()['avg_rating'] ?? 0));

            $disputes = (int) $db->table('provider_disputes')
                ->where('provider_id', $providerId)
                ->where('created_at >=', $from)
                ->where('created_at <=', $to)
                ->countAllResults();

            $earnings = (float) (($db->table('earnings')
                ->selectSum('amount', 'total_amount')
                ->where('provider_id', $providerId)
                ->where('created_at >=', $from)
                ->where('created_at <=', $to)
                ->get()
                ->getRowArray()['total_amount'] ?? 0));

            $rows[] = [
                'provider_id' => $providerId,
                'provider_name' => $provider['name'],
                'jobs_total' => $jobsTotal,
                'jobs_completed' => $jobsCompleted,
                'average_rating' => $avgRating,
                'disputes' => $disputes,
                'earnings_total' => $earnings,
            ];
        }

        usort($rows, static function ($a, $b) {
            return $b['jobs_completed'] <=> $a['jobs_completed'];
        });

        return $this->respond([
            'data' => [
                'rows' => $rows,
            ],
        ]);
    }

    public function customers()
    {
        if (($auth = $this->ensureAdmin()) !== true) {
            return $auth;
        }

        [$from, $to] = $this->range();
        $db = db_connect();

        $customers = $db->table('users')
            ->select('id, name')
            ->where('role', 'Customer')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $rows = [];

        foreach ($customers as $customer) {
            $customerId = (int) $customer['id'];

            $jobsCount = (int) $db->table('jobs')
                ->where('customer_id', $customerId)
                ->where('created_at >=', $from)
                ->where('created_at <=', $to)
                ->countAllResults();

            $completed = (int) $db->table('jobs')
                ->where('customer_id', $customerId)
                ->where('status', 'completed')
                ->where('created_at >=', $from)
                ->where('created_at <=', $to)
                ->countAllResults();

            $cancelled = (int) $db->table('jobs')
                ->where('customer_id', $customerId)
                ->where('status', 'cancelled')
                ->where('created_at >=', $from)
                ->where('created_at <=', $to)
                ->countAllResults();

            $rows[] = [
                'customer_id' => $customerId,
                'customer_name' => $customer['name'],
                'jobs_total' => $jobsCount,
                'jobs_completed' => $completed,
                'jobs_cancelled' => $cancelled,
            ];
        }

        usort($rows, static function ($a, $b) {
            return $b['jobs_total'] <=> $a['jobs_total'];
        });

        return $this->respond([
            'data' => [
                'rows' => $rows,
            ],
        ]);
    }

    public function finance()
    {
        if (($auth = $this->ensureAdmin()) !== true) {
            return $auth;
        }

        [$from, $to] = $this->range();
        $db = db_connect();

        $earnings = (float) (($db->table('earnings')
            ->selectSum('amount', 'total_amount')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->get()
            ->getRowArray()['total_amount'] ?? 0));

        $commissions = (float) (($db->table('earnings')
            ->selectSum('commission_amount', 'total_amount')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->get()
            ->getRowArray()['total_amount'] ?? 0));

        $providerNet = (float) (($db->table('earnings')
            ->selectSum('provider_net', 'total_amount')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->get()
            ->getRowArray()['total_amount'] ?? 0));

        $payouts = (float) (($db->table('payouts')
            ->selectSum('amount', 'total_amount')
            ->where('requested_at >=', $from)
            ->where('requested_at <=', $to)
            ->get()
            ->getRowArray()['total_amount'] ?? 0));

        $refunds = (float) (($db->table('refunds')
            ->selectSum('amount', 'total_amount')
            ->where('submitted_at >=', $from)
            ->where('submitted_at <=', $to)
            ->get()
            ->getRowArray()['total_amount'] ?? 0));

        $ledgerRows = $db->table('ledger l')
            ->select('l.id, l.transaction_type, l.amount, l.description, l.reference, l.created_at, u.name AS user_name')
            ->join('users u', 'u.id = l.user_id', 'left')
            ->where('l.created_at >=', $from)
            ->where('l.created_at <=', $to)
            ->orderBy('l.id', 'DESC')
            ->get()
            ->getResultArray();

        return $this->respond([
            'data' => [
                'summary' => [
                    'earnings_total' => $earnings,
                    'commission_total' => $commissions,
                    'provider_net_total' => $providerNet,
                    'payouts_total' => $payouts,
                    'refunds_total' => $refunds,
                ],
                'rows' => $ledgerRows,
            ],
        ]);
    }

    public function promotions()
    {
        if (($auth = $this->ensureAdmin()) !== true) {
            return $auth;
        }

        [$from, $to] = $this->range();
        $db = db_connect();

        $rows = $db->table('promotions p')
            ->select('p.id, p.title, p.promotion_type, p.discount_type, p.discount_value, p.code, p.start_date, p.end_date, p.usage_limit, p.status, c.name AS category_name, s.name AS service_name, u.name AS provider_name')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('services s', 's.id = p.service_id', 'left')
            ->join('users u', 'u.id = p.provider_id', 'left')
            ->where('p.created_at >=', $from)
            ->where('p.created_at <=', $to)
            ->orderBy('p.id', 'DESC')
            ->get()
            ->getResultArray();

        $active = (int) $db->table('promotions')
            ->where('status', 'active')
            ->countAllResults();

        $inactive = (int) $db->table('promotions')
            ->where('status', 'inactive')
            ->countAllResults();

        return $this->respond([
            'data' => [
                'summary' => [
                    'active' => $active,
                    'inactive' => $inactive,
                    'total' => $active + $inactive,
                ],
                'rows' => $rows,
            ],
        ]);
    }
}