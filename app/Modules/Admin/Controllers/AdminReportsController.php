<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class AdminReportsController extends BaseController
{
    use ResponseTrait;

    protected function range()
    {
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to') ?? date('Y-m-d');

        return [$from . ' 00:00:00', $to . ' 23:59:59'];
    }

    /**
     * EXECUTIVE DASHBOARD
     */
    public function executive()
    {
        [$from, $to] = $this->range();
        $db = db_connect();

        $jobsTotal = $db->table('jobs')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->countAllResults();

        $completed = $db->table('jobs')
            ->where('status', 'completed')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->countAllResults();

        $cancelled = $db->table('jobs')
            ->where('status', 'cancelled')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->countAllResults();

        $escalated = $db->table('jobs')
            ->where('status', 'escalated')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->countAllResults();

        $earnings = $db->table('earnings')
            ->selectSum('amount', 'total')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->get()->getRow()->total ?? 0;

        $refunds = $db->table('refunds')
            ->selectSum('amount', 'total')
            ->where('submitted_at >=', $from)
            ->where('submitted_at <=', $to)
            ->get()->getRow()->total ?? 0;

        $completionRate = $jobsTotal > 0 ? ($completed / $jobsTotal) * 100 : 0;
        $cancelRate = $jobsTotal > 0 ? ($cancelled / $jobsTotal) * 100 : 0;

        return $this->respond([
            'data' => [
                'kpis' => [
                    'jobs_total' => $jobsTotal,
                    'completed' => $completed,
                    'cancelled' => $cancelled,
                    'escalated' => $escalated,
                    'completion_rate' => round($completionRate, 2),
                    'cancel_rate' => round($cancelRate, 2),
                    'earnings' => $earnings,
                    'refunds' => $refunds,
                ]
            ]
        ]);
    }

    /**
     * OPERATIONS SUMMARY
     */
    public function operationsSummary()
    {
        [$from, $to] = $this->range();
        $db = db_connect();

        $statuses = ['pending','active','scheduled','completed','cancelled','escalated'];
        $result = [];

        foreach ($statuses as $status) {
            $result[$status] = $db->table('jobs')
                ->where('status', $status)
                ->where('created_at >=', $from)
                ->where('created_at <=', $to)
                ->countAllResults();
        }

        return $this->respond(['data' => $result]);
    }

    /**
     * OPERATIONS FUNNEL
     */
    public function operationsFunnel()
    {
        [$from, $to] = $this->range();
        $db = db_connect();

        $data = $db->table('jobs')
            ->select("DATE(created_at) as day, COUNT(*) as total")
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->groupBy('DATE(created_at)')
            ->orderBy('day','ASC')
            ->get()->getResult();

        return $this->respond(['data' => $data]);
    }

    /**
     * OPERATIONS EXCEPTIONS
     */
    public function operationsExceptions()
    {
        [$from, $to] = $this->range();
        $db = db_connect();

        $rows = $db->table('jobs')
            ->where('status', 'pending')
            ->where('created_at >=', $from)
            ->where('created_at <=', $to)
            ->orderBy('created_at','ASC')
            ->limit(50)
            ->get()->getResult();

        return $this->respond(['data' => $rows]);
    }
}