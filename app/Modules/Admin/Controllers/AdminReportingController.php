<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class AdminReportsController extends BaseController
{
    use ResponseTrait;

    private array $allowedSections = [
        'overview'   => 'view-reports-dashboard',
        'operations' => 'view-operations-reports',
        'providers'  => 'view-provider-reports',
        'customers'  => 'view-customer-reports',
        'finance'    => 'view-finance-reports',
        'promotions' => 'view-promotion-reports',
    ];

    private function authorizeSection(string $section)
    {
        $payload = $this->authPayload();
        if (!$payload || ($payload->role ?? null) !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $permission = $this->allowedSections[$section] ?? null;
        if ($permission === null) {
            return $this->failValidationErrors('Unknown report section.');
        }

        if ($denied = $this->requirePermission($permission)) {
            return $denied;
        }

        return null;
    }

    private function dateRange(): array
    {
        $from = trim((string) ($this->request->getGet('from_date') ?? ''));
        $to   = trim((string) ($this->request->getGet('to_date') ?? ''));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = date('Y-m-d', strtotime('-30 days'));
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $to = date('Y-m-d');
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    private function page(): int
    {
        return max(1, (int) ($this->request->getGet('page') ?? 1));
    }

    private function pageSize(): int
    {
        $size = (int) ($this->request->getGet('page_size') ?? 20);
        return in_array($size, [10, 20, 50, 100], true) ? $size : 20;
    }

    private function pagination(int $page, int $pageSize, int $totalRows): array
    {
        $totalPages = (int) ceil(($totalRows ?: 1) / $pageSize);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        return [
            'page' => $page,
            'page_size' => $pageSize,
            'total_rows' => $totalRows,
            'total_pages' => $totalPages,
        ];
    }

    private function scalar(string $sql, array $binds = [], float $default = 0): float
    {
        $row = db_connect()->query($sql, $binds)->getRowArray();
        if (!$row) {
            return $default;
        }
        $value = reset($row);
        return is_numeric($value) ? (float) $value : $default;
    }

    private function listCategoryOptions(): array
    {
        return db_connect()->table('categories')
            ->select('id, name')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function listProviderOptions(): array
    {
        return db_connect()->table('users')
            ->select('id, name')
            ->where('is_provider', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function summary()
    {
        if ($auth = $this->authorizeSection('overview')) {
            return $auth;
        }

        [$from, $to] = $this->dateRange();
        $db = db_connect();

        $jobsTotal = $this->scalar('SELECT COUNT(*) c FROM jobs WHERE DATE(created_at) BETWEEN ? AND ?', [$from, $to]);
        $jobsCompleted = $this->scalar("SELECT COUNT(*) c FROM jobs WHERE status = 'completed' AND DATE(created_at) BETWEEN ? AND ?", [$from, $to]);
        $jobsCancelled = $this->scalar("SELECT COUNT(*) c FROM jobs WHERE status = 'cancelled' AND DATE(created_at) BETWEEN ? AND ?", [$from, $to]);
        $activeProviders = $this->scalar("SELECT COUNT(*) c FROM users WHERE is_provider = 1 AND status = 'active' AND provider_status = 'approved'");
        $customersTotal = $this->scalar("SELECT COUNT(*) c FROM users WHERE role = 'Customer'");
        $earningsTotal = $this->scalar('SELECT COALESCE(SUM(amount),0) t FROM earnings WHERE DATE(created_at) BETWEEN ? AND ?', [$from, $to]);
        $commissionTotal = $this->scalar('SELECT COALESCE(SUM(commission_amount),0) t FROM earnings WHERE DATE(created_at) BETWEEN ? AND ?', [$from, $to]);
        $payoutProcessed = $this->scalar("SELECT COALESCE(SUM(amount),0) t FROM payouts WHERE status = 'processed' AND DATE(requested_at) BETWEEN ? AND ?", [$from, $to]);
        $refundApproved = $this->scalar("SELECT COALESCE(SUM(amount),0) t FROM refunds WHERE status = 'approved' AND DATE(submitted_at) BETWEEN ? AND ?", [$from, $to]);

        $jobTrend = $db->query(
            "SELECT DATE(created_at) label,
                    COUNT(*) total_jobs,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) completed_jobs
             FROM jobs
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY DATE(created_at) ASC",
            [$from, $to]
        )->getResultArray();

        $revenueTrend = $db->query(
            "SELECT DATE(created_at) label,
                    COALESCE(SUM(amount),0) gross_earnings,
                    COALESCE(SUM(commission_amount),0) platform_commission
             FROM earnings
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY DATE(created_at) ASC",
            [$from, $to]
        )->getResultArray();

        return $this->respond([
            'data' => [
                'meta' => ['from_date' => $from, 'to_date' => $to],
                'cards' => [
                    ['label' => 'Jobs Created', 'value' => (int) $jobsTotal],
                    ['label' => 'Jobs Completed', 'value' => (int) $jobsCompleted],
                    ['label' => 'Jobs Cancelled', 'value' => (int) $jobsCancelled],
                    ['label' => 'Active Providers', 'value' => (int) $activeProviders],
                    ['label' => 'Customers', 'value' => (int) $customersTotal],
                    ['label' => 'Gross Earnings', 'value' => $earningsTotal],
                    ['label' => 'Platform Commission', 'value' => $commissionTotal],
                    ['label' => 'Processed Payouts', 'value' => $payoutProcessed],
                    ['label' => 'Approved Refunds', 'value' => $refundApproved],
                ],
                'job_trend' => $jobTrend,
                'revenue_trend' => $revenueTrend,
                'notes' => [
                    'Executive summary reflects the selected date range.',
                    'Promotion usage and conversion are not yet measurable because the current schema has no promotion redemption linkage to jobs or payments.',
                ],
            ],
        ]);
    }

    public function operations()
    {
        if ($auth = $this->authorizeSection('operations')) {
            return $auth;
        }

        [$from, $to] = $this->dateRange();
        $page = $this->page();
        $pageSize = $this->pageSize();
        $offset = ($page - 1) * $pageSize;
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $categoryId = (int) ($this->request->getGet('category_id') ?? 0);
        $providerId = (int) ($this->request->getGet('provider_id') ?? 0);
        $search = trim((string) ($this->request->getGet('search') ?? ''));

        $db = db_connect();
        $base = $db->table('jobs j')
            ->join('users cu', 'cu.id = j.customer_id', 'left')
            ->join('users pu', 'pu.id = j.provider_id', 'left')
            ->join('services s', 's.id = j.service_id', 'left')
            ->join('categories c', 'c.id = s.category_id', 'left')
            ->select('j.id, j.title, j.status, j.scheduled_time, j.created_at, j.assigned_at, j.completed_at, j.cancelled_at, j.escalated_at, cu.name AS customer_name, pu.name AS provider_name, s.name AS service_name, c.id AS category_id, c.name AS category_name')
            ->where('DATE(j.created_at) >=', $from)
            ->where('DATE(j.created_at) <=', $to);

        if ($status !== '') {
            $base->where('j.status', $status);
        }
        if ($categoryId > 0) {
            $base->where('c.id', $categoryId);
        }
        if ($providerId > 0) {
            $base->where('j.provider_id', $providerId);
        }
        if ($search !== '') {
            $base->groupStart()
                ->like('j.title', $search)
                ->orLike('cu.name', $search)
                ->orLike('pu.name', $search)
                ->orLike('c.name', $search)
                ->groupEnd();
        }

        $totalRows = (clone $base)->countAllResults();
        $rows = (clone $base)
            ->orderBy('j.created_at', 'DESC')
            ->limit($pageSize, $offset)
            ->get()
            ->getResultArray();

        $statusSummary = (clone $base)
            ->select('j.status, COUNT(*) AS total_rows', false)
            ->groupBy('j.status')
            ->get()
            ->getResultArray();

        $categorySummary = (clone $base)
            ->select('COALESCE(c.name, "Uncategorized") AS label, COUNT(*) AS total_rows', false)
            ->groupBy('c.id')
            ->orderBy('total_rows', 'DESC')
            ->get()
            ->getResultArray();

        return $this->respond([
            'data' => [
                'meta' => [
                    'from_date' => $from,
                    'to_date' => $to,
                    'available_statuses' => ['pending', 'active', 'scheduled', 'completed', 'cancelled', 'escalated'],
                    'categories' => $this->listCategoryOptions(),
                    'providers' => $this->listProviderOptions(),
                ],
                'cards' => [
                    ['label' => 'Total Jobs', 'value' => (int) $this->scalar('SELECT COUNT(*) c FROM jobs WHERE DATE(created_at) BETWEEN ? AND ?', [$from, $to])],
                    ['label' => 'Completed', 'value' => (int) $this->scalar("SELECT COUNT(*) c FROM jobs WHERE status = 'completed' AND DATE(created_at) BETWEEN ? AND ?", [$from, $to])],
                    ['label' => 'Pending', 'value' => (int) $this->scalar("SELECT COUNT(*) c FROM jobs WHERE status = 'pending' AND DATE(created_at) BETWEEN ? AND ?", [$from, $to])],
                    ['label' => 'Scheduled', 'value' => (int) $this->scalar("SELECT COUNT(*) c FROM jobs WHERE status = 'scheduled' AND DATE(created_at) BETWEEN ? AND ?", [$from, $to])],
                    ['label' => 'Active', 'value' => (int) $this->scalar("SELECT COUNT(*) c FROM jobs WHERE status = 'active' AND DATE(created_at) BETWEEN ? AND ?", [$from, $to])],
                    ['label' => 'Cancelled', 'value' => (int) $this->scalar("SELECT COUNT(*) c FROM jobs WHERE status = 'cancelled' AND DATE(created_at) BETWEEN ? AND ?", [$from, $to])],
                    ['label' => 'Escalated', 'value' => (int) $this->scalar("SELECT COUNT(*) c FROM jobs WHERE status = 'escalated' AND DATE(created_at) BETWEEN ? AND ?", [$from, $to])],
                ],
                'breakdowns' => [
                    'by_status' => $statusSummary,
                    'by_category' => $categorySummary,
                ],
                'rows' => $rows,
                'pagination' => $this->pagination($page, $pageSize, $totalRows),
            ],
        ]);
    }

    public function providers()
    {
        if ($auth = $this->authorizeSection('providers')) {
            return $auth;
        }

        [$from, $to] = $this->dateRange();
        $page = $this->page();
        $pageSize = $this->pageSize();
        $offset = ($page - 1) * $pageSize;
        $providerStatus = trim((string) ($this->request->getGet('provider_status') ?? ''));
        $search = trim((string) ($this->request->getGet('search') ?? ''));

        $db = db_connect();
        $jobsSql = "
            SELECT provider_id,
                   COUNT(*) total_jobs,
                   SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) completed_jobs,
                   SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) cancelled_jobs,
                   SUM(CASE WHEN status = 'escalated' THEN 1 ELSE 0 END) escalated_jobs
            FROM jobs
            WHERE provider_id IS NOT NULL
              AND DATE(created_at) BETWEEN '{$from}' AND '{$to}'
            GROUP BY provider_id
        ";
        $ratingsSql = "
            SELECT provider_id,
                   COUNT(*) rating_count,
                   AVG(rating) avg_rating
            FROM provider_ratings
            WHERE DATE(created_at) BETWEEN '{$from}' AND '{$to}'
            GROUP BY provider_id
        ";
        $disputesSql = "
            SELECT provider_id,
                   COUNT(*) disputes_count,
                   SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) resolved_count
            FROM provider_disputes
            WHERE DATE(created_at) BETWEEN '{$from}' AND '{$to}'
            GROUP BY provider_id
        ";
        $earningsSql = "
            SELECT provider_id,
                   COALESCE(SUM(amount),0) gross_earnings,
                   COALESCE(SUM(provider_net),0) provider_net,
                   COALESCE(SUM(commission_amount),0) commission_total
            FROM earnings
            WHERE DATE(created_at) BETWEEN '{$from}' AND '{$to}'
            GROUP BY provider_id
        ";

        $base = $db->table('users u')
            ->select('u.id, u.name, u.email, u.phone, u.status, u.provider_status, u.location,
                COALESCE(j.total_jobs,0) AS total_jobs,
                COALESCE(j.completed_jobs,0) AS completed_jobs,
                COALESCE(j.cancelled_jobs,0) AS cancelled_jobs,
                COALESCE(j.escalated_jobs,0) AS escalated_jobs,
                COALESCE(r.rating_count,0) AS rating_count,
                COALESCE(r.avg_rating,0) AS avg_rating,
                COALESCE(d.disputes_count,0) AS disputes_count,
                COALESCE(d.resolved_count,0) AS resolved_disputes,
                COALESCE(e.gross_earnings,0) AS gross_earnings,
                COALESCE(e.provider_net,0) AS provider_net,
                COALESCE(e.commission_total,0) AS commission_total', false)
            ->join("({$jobsSql}) j", 'j.provider_id = u.id', 'left', false)
            ->join("({$ratingsSql}) r", 'r.provider_id = u.id', 'left', false)
            ->join("({$disputesSql}) d", 'd.provider_id = u.id', 'left', false)
            ->join("({$earningsSql}) e", 'e.provider_id = u.id', 'left', false)
            ->where('u.is_provider', 1);

        if ($providerStatus !== '') {
            $base->where('u.provider_status', $providerStatus);
        }
        if ($search !== '') {
            $base->groupStart()
                ->like('u.name', $search)
                ->orLike('u.email', $search)
                ->orLike('u.phone', $search)
                ->orLike('u.location', $search)
                ->groupEnd();
        }

        $totalRows = (clone $base)->countAllResults();
        $rows = (clone $base)
            ->orderBy('gross_earnings', 'DESC')
            ->orderBy('u.name', 'ASC')
            ->limit($pageSize, $offset)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $totalJobs = (int) ($row['total_jobs'] ?? 0);
            $row['completion_rate'] = $totalJobs > 0 ? round(((int) $row['completed_jobs'] / $totalJobs) * 100, 2) : 0;
            $row['dispute_rate'] = $totalJobs > 0 ? round(((int) $row['disputes_count'] / $totalJobs) * 100, 2) : 0;
            $row['avg_rating'] = round((float) $row['avg_rating'], 2);
        }
        unset($row);

        return $this->respond([
            'data' => [
                'meta' => [
                    'from_date' => $from,
                    'to_date' => $to,
                    'available_provider_statuses' => ['pending', 'approved', 'rejected'],
                ],
                'cards' => [
                    ['label' => 'Providers', 'value' => (int) $this->scalar('SELECT COUNT(*) c FROM users WHERE is_provider = 1')],
                    ['label' => 'Approved Providers', 'value' => (int) $this->scalar("SELECT COUNT(*) c FROM users WHERE is_provider = 1 AND provider_status = 'approved'")],
                    ['label' => 'Active Providers', 'value' => (int) $this->scalar("SELECT COUNT(*) c FROM users WHERE is_provider = 1 AND provider_status = 'approved' AND status = 'active'")],
                    ['label' => 'Gross Provider Earnings', 'value' => $this->scalar('SELECT COALESCE(SUM(amount),0) t FROM earnings WHERE DATE(created_at) BETWEEN ? AND ?', [$from, $to])],
                ],
                'rows' => $rows,
                'pagination' => $this->pagination($page, $pageSize, $totalRows),
            ],
        ]);
    }

    public function customers()
    {
        if ($auth = $this->authorizeSection('customers')) {
            return $auth;
        }

        [$from, $to] = $this->dateRange();
        $page = $this->page();
        $pageSize = $this->pageSize();
        $offset = ($page - 1) * $pageSize;
        $search = trim((string) ($this->request->getGet('search') ?? ''));

        $db = db_connect();
        $jobsSql = "
            SELECT customer_id,
                   COUNT(*) total_jobs,
                   SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) completed_jobs,
                   SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) cancelled_jobs,
                   MAX(created_at) last_job_at
            FROM jobs
            WHERE DATE(created_at) BETWEEN '{$from}' AND '{$to}'
            GROUP BY customer_id
        ";

        $base = $db->table('users u')
            ->select('u.id, u.name, u.email, u.phone, u.status, u.created_at, COALESCE(j.total_jobs,0) AS total_jobs, COALESCE(j.completed_jobs,0) AS completed_jobs, COALESCE(j.cancelled_jobs,0) AS cancelled_jobs, j.last_job_at', false)
            ->join("({$jobsSql}) j", 'j.customer_id = u.id', 'left', false)
            ->where('u.role', 'Customer');

        if ($search !== '') {
            $base->groupStart()
                ->like('u.name', $search)
                ->orLike('u.email', $search)
                ->orLike('u.phone', $search)
                ->groupEnd();
        }

        $totalRows = (clone $base)->countAllResults();
        $rows = (clone $base)
            ->orderBy('total_jobs', 'DESC')
            ->orderBy('u.name', 'ASC')
            ->limit($pageSize, $offset)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $row['customer_type'] = ((int) ($row['total_jobs'] ?? 0)) > 1 ? 'repeat' : (((int) ($row['total_jobs'] ?? 0)) === 1 ? 'new' : 'inactive');
        }
        unset($row);

        return $this->respond([
            'data' => [
                'meta' => ['from_date' => $from, 'to_date' => $to],
                'cards' => [
                    ['label' => 'Total Customers', 'value' => (int) $this->scalar("SELECT COUNT(*) c FROM users WHERE role = 'Customer'")],
                    ['label' => 'Customers With Jobs', 'value' => (int) $this->scalar('SELECT COUNT(DISTINCT customer_id) c FROM jobs WHERE DATE(created_at) BETWEEN ? AND ?', [$from, $to])],
                    ['label' => 'New Customers In Range', 'value' => (int) $this->scalar("SELECT COUNT(*) c FROM users WHERE role = 'Customer' AND DATE(created_at) BETWEEN ? AND ?", [$from, $to])],
                    ['label' => 'Repeat Customers In Range', 'value' => (int) $this->scalar('SELECT COUNT(*) c FROM (SELECT customer_id FROM jobs WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY customer_id HAVING COUNT(*) > 1) q', [$from, $to])],
                ],
                'rows' => $rows,
                'pagination' => $this->pagination($page, $pageSize, $totalRows),
            ],
        ]);
    }

    public function finance()
    {
        if ($auth = $this->authorizeSection('finance')) {
            return $auth;
        }

        [$from, $to] = $this->dateRange();
        $page = $this->page();
        $pageSize = $this->pageSize();
        $offset = ($page - 1) * $pageSize;
        $providerId = (int) ($this->request->getGet('provider_id') ?? 0);
        $commissionStatus = trim((string) ($this->request->getGet('commission_status') ?? ''));

        $db = db_connect();
        $base = $db->table('earnings e')
            ->join('users u', 'u.id = e.provider_id', 'left')
            ->join('jobs j', 'j.id = e.job_id', 'left')
            ->join('services s', 's.id = j.service_id', 'left')
            ->join('categories c', 'c.id = s.category_id', 'left')
            ->select('e.id, e.created_at, e.provider_id, u.name AS provider_name, e.amount, e.commission_status, e.commission_rate, e.commission_amount, e.provider_net, j.title AS job_title, c.name AS category_name')
            ->where('DATE(e.created_at) >=', $from)
            ->where('DATE(e.created_at) <=', $to);

        if ($providerId > 0) {
            $base->where('e.provider_id', $providerId);
        }
        if ($commissionStatus !== '') {
            $base->where('e.commission_status', $commissionStatus);
        }

        $totalRows = (clone $base)->countAllResults();
        $rows = (clone $base)
            ->orderBy('e.created_at', 'DESC')
            ->limit($pageSize, $offset)
            ->get()
            ->getResultArray();

        return $this->respond([
            'data' => [
                'meta' => [
                    'from_date' => $from,
                    'to_date' => $to,
                    'providers' => $this->listProviderOptions(),
                    'commission_statuses' => ['confirmed', 'unconfirmed'],
                ],
                'cards' => [
                    ['label' => 'Gross Earnings', 'value' => $this->scalar('SELECT COALESCE(SUM(amount),0) t FROM earnings WHERE DATE(created_at) BETWEEN ? AND ?', [$from, $to])],
                    ['label' => 'Commission Total', 'value' => $this->scalar('SELECT COALESCE(SUM(commission_amount),0) t FROM earnings WHERE DATE(created_at) BETWEEN ? AND ?', [$from, $to])],
                    ['label' => 'Provider Net', 'value' => $this->scalar('SELECT COALESCE(SUM(provider_net),0) t FROM earnings WHERE DATE(created_at) BETWEEN ? AND ?', [$from, $to])],
                    ['label' => 'Processed Payouts', 'value' => $this->scalar("SELECT COALESCE(SUM(amount),0) t FROM payouts WHERE status = 'processed' AND DATE(requested_at) BETWEEN ? AND ?", [$from, $to])],
                    ['label' => 'Pending Payouts', 'value' => $this->scalar("SELECT COALESCE(SUM(amount),0) t FROM payouts WHERE status = 'pending' AND DATE(requested_at) BETWEEN ? AND ?", [$from, $to])],
                    ['label' => 'Approved Refunds', 'value' => $this->scalar("SELECT COALESCE(SUM(amount),0) t FROM refunds WHERE status = 'approved' AND DATE(submitted_at) BETWEEN ? AND ?", [$from, $to])],
                    ['label' => 'Pending Refunds', 'value' => $this->scalar("SELECT COALESCE(SUM(amount),0) t FROM refunds WHERE status = 'pending' AND DATE(submitted_at) BETWEEN ? AND ?", [$from, $to])],
                    ['label' => 'Ledger Movement', 'value' => $this->scalar('SELECT COALESCE(SUM(amount),0) t FROM ledger WHERE DATE(created_at) BETWEEN ? AND ?', [$from, $to])],
                ],
                'rows' => $rows,
                'pagination' => $this->pagination($page, $pageSize, $totalRows),
            ],
        ]);
    }

    public function promotions()
    {
        if ($auth = $this->authorizeSection('promotions')) {
            return $auth;
        }

        [$from, $to] = $this->dateRange();
        $page = $this->page();
        $pageSize = $this->pageSize();
        $offset = ($page - 1) * $pageSize;
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $promotionType = trim((string) ($this->request->getGet('promotion_type') ?? ''));

        $db = db_connect();
        $base = $db->table('promotions p')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('services s', 's.id = p.service_id', 'left')
            ->join('users u', 'u.id = p.provider_id', 'left')
            ->select('p.id, p.title, p.promotion_type, p.discount_type, p.discount_value, p.code, p.status, p.start_date, p.end_date, p.usage_limit, c.name AS category_name, s.name AS service_name, u.name AS provider_name, p.created_at')
            ->where('DATE(p.created_at) >=', $from)
            ->where('DATE(p.created_at) <=', $to);

        if ($status !== '') {
            $base->where('p.status', $status);
        }
        if ($promotionType !== '') {
            $base->where('p.promotion_type', $promotionType);
        }

        $totalRows = (clone $base)->countAllResults();
        $rows = (clone $base)
            ->orderBy('p.created_at', 'DESC')
            ->limit($pageSize, $offset)
            ->get()
            ->getResultArray();

        return $this->respond([
            'data' => [
                'meta' => [
                    'from_date' => $from,
                    'to_date' => $to,
                    'promotion_types' => ['global', 'service', 'provider', 'coupon'],
                    'statuses' => ['active', 'inactive'],
                ],
                'cards' => [
                    ['label' => 'Promotions Created', 'value' => (int) $this->scalar('SELECT COUNT(*) c FROM promotions WHERE DATE(created_at) BETWEEN ? AND ?', [$from, $to])],
                    ['label' => 'Active Promotions', 'value' => (int) $this->scalar("SELECT COUNT(*) c FROM promotions WHERE status = 'active' AND DATE(created_at) BETWEEN ? AND ?", [$from, $to])],
                    ['label' => 'Coupon Promotions', 'value' => (int) $this->scalar("SELECT COUNT(*) c FROM promotions WHERE promotion_type = 'coupon' AND DATE(created_at) BETWEEN ? AND ?", [$from, $to])],
                    ['label' => 'Provider Promotions', 'value' => (int) $this->scalar("SELECT COUNT(*) c FROM promotions WHERE promotion_type = 'provider' AND DATE(created_at) BETWEEN ? AND ?", [$from, $to])],
                ],
                'notes' => [
                    'This report is configuration-rich and operationally useful, but true redemption, conversion, and revenue attribution require a promotion usage table linked to jobs or payments.',
                ],
                'rows' => $rows,
                'pagination' => $this->pagination($page, $pageSize, $totalRows),
            ],
        ]);
    }
}
