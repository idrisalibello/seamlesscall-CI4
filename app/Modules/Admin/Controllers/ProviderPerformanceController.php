<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\JobModel;
use App\Models\ProviderRatingsModel;
use App\Models\ProviderDisputesModel;
use CodeIgniter\I18n\Time; // For date handling
use CodeIgniter\Pager\Pager; // For pagination

class ProviderPerformanceController extends BaseController
{
    use ResponseTrait;

    protected $userModel;
    protected $jobsModel;
    protected $providerRatingsModel;
    protected $providerDisputesModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->jobsModel = new JobModel();
        $this->providerRatingsModel = new ProviderRatingsModel();
        $this->providerDisputesModel = new ProviderDisputesModel();
    }

    // Authorization check for Admin role
    protected function checkAdminAuth()
    {
        $user = service('request')->auth_payload;
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }
        return true;
    }

    /**
     * Helper to parse and default date ranges.
     * @return array ['from' => YYYY-MM-DD, 'to' => YYYY-MM-DD]
     */
    protected function getDateRange(): array
    {
        $to = $this->request->getGet('to');
        if ($to) {
            $to = Time::parse($to);
            if ($to->getYear() < 2000 || $to->getYear() > 2100) $to = Time::now(); // Basic year validation
        } else {
            $to = Time::now();
        }

        $from = $this->request->getGet('from');
        if ($from) {
            $from = Time::parse($from);
            if ($from->getYear() < 2000 || $from->getYear() > 2100) $from = $to->subDays(28); // Basic year validation
        } else {
            $from = $to->subDays(28);
        }

        // Ensure 'from' is not after 'to'
        if ($from->isAfter($to)) {
            $from = $to->subDays(28); // Reset to default if invalid
        }

        return [
            'from' => $from->toDateString(),
            'to'   => $to->toDateString(),
        ];
    }


    /**
     * Helper to determine time bucket.
     * @return string 'week' or 'day'
     */
    protected function getTimeBucket(): string
    {
        $bucket = $this->request->getGet('bucket');
        return in_array($bucket, ['week', 'day']) ? $bucket : 'week'; // Default to 'week'
    }

    /**
     * Helper to get pagination offset and limit.
     * @return array ['offset', 'limit']
     */
    protected function getPaginationParams(): array
    {
        $page  = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 20);
        // Default limit 20

        $limit = max(1, min(100, $limit)); // Limit between 1 and 100
        $offset = ($page - 1) * $limit;
        $page = max(1, $page);

        return ['offset' => $offset, 'limit' => $limit];
    }


    /**
     * Generate job trend series buckets for a given provider within a date range.
     * @return array [{start, end, assigned, completed, cancelled, escalated}, ...]
     */
    protected function _getJobTrendSeries(int $providerId, array $dateRange, string $bucket): array
    {
        $trend = [];
        $currentDate = Time::parse($dateRange['from']);
        $endDate = Time::parse($dateRange['to']);

        while ($currentDate->isBefore($endDate) || $currentDate->toDateString() === $endDate->toDateString()) {
            $bucketStart = $currentDate->toDateString();

            if ($bucket === 'week') {
                $bucketEnd = $currentDate->addDays(6)->toDateString();
                if (Time::parse($bucketEnd)->isAfter($endDate)) {
                    $bucketEnd = $endDate->toDateString();
                }
                $currentDate = $currentDate->addDays(7); // Move to next day after adding 6 days
            } else { // day
                $bucketEnd = $currentDate->toDateString();
            }

            $jobsInBucket = $this->jobsModel
                ->where('provider_id', $providerId)
                ->groupStart()
                ->groupStart()
                ->where('assigned_at >=', $bucketStart)
                ->where('assigned_at <=', $bucketEnd)
                ->groupEnd()
                ->orGroupStart()
                ->where('completed_at >=', $bucketStart)
                ->where('completed_at <=', $bucketEnd)
                ->groupEnd()
                ->orGroupStart()
                ->where('cancelled_at >=', $bucketStart)
                ->where('cancelled_at <=', $bucketEnd)
                ->groupEnd()
                ->orGroupStart()
                ->where('escalated_at >=', $bucketStart)
                ->where('escalated_at <=', $bucketEnd)
                ->groupEnd()
                ->groupEnd()
                ->findAll();

            $assigned = 0;
            $completed = 0;
            $cancelled = 0;
            $escalated = 0;

            foreach ($jobsInBucket as $job) {
                if (isset($job['assigned_at']) && Time::parse($job['assigned_at'])->toDateString() >= $bucketStart && Time::parse($job['assigned_at'])->toDateString() <= $bucketEnd) {
                    $assigned++;
                }
                if (isset($job['completed_at']) && Time::parse($job['completed_at'])->toDateString() >= $bucketStart && Time::parse($job['completed_at'])->toDateString() <= $bucketEnd) {
                    $completed++;
                }
                if (isset($job['cancelled_at']) && Time::parse($job['cancelled_at'])->toDateString() >= $bucketStart && Time::parse($job['cancelled_at'])->toDateString() <= $bucketEnd) {
                    $cancelled++;
                }
                if (isset($job['escalated_at']) && Time::parse($job['escalated_at'])->toDateString() >= $bucketStart && Time::parse($job['escalated_at'])->toDateString() <= $bucketEnd) {
                    $escalated++;
                }
            }

            $trend[] = [
                'start'     => $bucketStart,
                'end'       => $bucketEnd,
                'assigned'  => $assigned,
                'completed' => $completed,
                'cancelled' => $cancelled,
                'escalated' => $escalated,
            ];

            if ($bucket === 'day') {
                $currentDate = $currentDate->addDays(1);
            }
        }

        return $trend;
    }

    // Backward-compatible route targets (Routes.php points here)
    public function getOverallProviderPerformance()
    {
        return $this->index();
    }

    public function getProviderPerformance($providerId)
    {
        return $this->show($providerId);
    }



    /**
     * List providers performance (paged, default limit 20).
     * GET admin/providers/performance
     */
    public function index()
    {
        if (($authResult = $this->checkAdminAuth()) !== true) {
            return $authResult;
        }

        $dateRange = $this->getDateRange();
        $pagination = $this->getPaginationParams();

        $providers = $this->userModel->where('is_provider', 1)
            ->orderBy('id', 'ASC')
            ->findAll($pagination['limit'], $pagination['offset']);

        $totalProviders = $this->userModel->where('is_provider', 1)->countAllResults();

        $performanceData = [];
        foreach ($providers as $provider) {
            $providerId = $provider['id'];
            $providerName = $provider['name'];
            $providerStatus = $provider['status']; // Assuming 'status' is relevant here

            // Calculate metrics for each provider within the date range
            $totalJobs = $this->jobsModel->where('provider_id', $providerId)
                // ->where('created_at >=', $dateRange['from'])
                // ->where('created_at <=', $dateRange['to'])
                ->countAllResults();

            $completedJobs = $this->jobsModel->where('provider_id', $providerId)
                ->where('status', 'completed')
                // ->where('completed_at >=', $dateRange['from'])
                // ->where('completed_at <=', $dateRange['to'])
                ->countAllResults();

            $cancelledJobs = $this->jobsModel->where('provider_id', $providerId)
                ->where('status', 'cancelled')
                // ->where('cancelled_at >=', $dateRange['from'])
                // ->where('cancelled_at <=', $dateRange['to'])
                ->countAllResults();

            $escalationsCount = $this->jobsModel->where('provider_id', $providerId)
                ->where('status', 'escalated')
                // ->where('escalated_at >=', $dateRange['from'])
                // ->where('escalated_at <=', $dateRange['to'])
                ->countAllResults();

            $avgRatingData = $this->providerRatingsModel->where('provider_id', $providerId)
                // ->where('created_at >=', $dateRange['from'])
                // ->where('created_at <=', $dateRange['to'])
                ->selectAvg('rating', 'avg_rating')
                ->first();
            $avgRating = $avgRatingData ? (float)$avgRatingData['avg_rating'] : 0.0;

            $disputesCount = $this->providerDisputesModel->where('provider_id', $providerId)
                // ->where('created_at >=', $dateRange['from'])
                // ->where('created_at <=', $dateRange['to'])
                ->countAllResults();

            $performanceData[] = [
                'provider_id'       => (int)$providerId,
                'provider_name'     => $providerName,
                'status'            => $providerStatus,
                'total_jobs'        => $totalJobs,
                'completed_jobs'    => $completedJobs,
                'cancelled_jobs'    => $cancelledJobs,
                'escalations_count' => $escalationsCount,
                'avg_rating'        => round($avgRating, 2),
                'disputes_count'    => $disputesCount,
            ];
        }

        return $this->respond([
            'data' => $performanceData,
            'pager' => [
                'total' => $totalProviders,
                'per_page' => $pagination['limit'],
                'current_page' => floor($pagination['offset'] / $pagination['limit']) + 1,
                'last_page' => ceil($totalProviders / $pagination['limit']),
            ],
        ]);
    }

    /**
     * Get summary + job trend for a specific provider.
     * GET admin/providers/(:num)/performance
     */
    public function show($providerId)
    {
        if (($authResult = $this->checkAdminAuth()) !== true) {
            return $authResult;
        }

        $provider = $this->userModel->find($providerId);
        if (!$provider || $provider['is_provider'] != 1) {
            return $this->failNotFound('Provider not found or not a registered provider.');
        }

        $dateRange = $this->getDateRange();
        $bucket = $this->getTimeBucket();

        // Summary Metrics
        $totalJobs = $this->jobsModel->where('provider_id', $providerId)
            // ->where('created_at >=', $dateRange['from'])
            //->where('created_at <=', $dateRange['to'])
            ->countAllResults();

        $completedJobs = $this->jobsModel->where('provider_id', $providerId)
            ->where('status', 'completed')
            //->where('completed_at >=', $dateRange['from'])
            // ->where('completed_at <=', $dateRange['to'])
            ->countAllResults();

        $cancelledJobs = $this->jobsModel->where('provider_id', $providerId)
            ->where('status', 'cancelled')
            //->where('cancelled_at >=', $dateRange['from'])
            //->where('cancelled_at <=', $dateRange['to'])
            ->countAllResults();

        $escalationsCount = $this->jobsModel->where('provider_id', $providerId)
            ->where('status', 'escalated')
            //->where('escalated_at >=', $dateRange['from'])
            //->where('escalated_at <=', $dateRange['to'])
            ->countAllResults();

        $avgRatingData = $this->providerRatingsModel->where('provider_id', $providerId)
            //->where('created_at >=', $dateRange['from'])
            //->where('created_at <=', $dateRange['to'])
            ->selectAvg('rating', 'avg_rating')
            ->first();
        $avgRating = $avgRatingData ? (float)$avgRatingData['avg_rating'] : 0.0;

        $disputesCount = $this->providerDisputesModel->where('provider_id', $providerId)
            //->where('created_at >=', $dateRange['from'])
            //->where('created_at <=', $dateRange['to'])
            ->countAllResults();

        $summaryMetrics = [
            'total_jobs'        => $totalJobs,
            'completed_jobs'    => $completedJobs,
            'cancelled_jobs'    => $cancelledJobs,
            'escalations_count' => $escalationsCount,
            'avg_rating'        => round($avgRating, 2),
            'disputes_count'    => $disputesCount,
        ];

        // Job Trend Series Buckets
        $jobTrend = $this->_getJobTrendSeries($providerId, $dateRange, $bucket);

        return $this->respond([
            'data' => [
                'provider_identity' => [
                    'id'              => (int) $providerId,
                    'name'            => $provider['name'],
                    'email'           => $provider['email'],
                    'phone'           => $provider['phone'],
                    'kyc_status'      => $provider['kyc_status'],
                    'provider_status' => $provider['provider_status'],
                ],
                'summary_metrics'  => $summaryMetrics,
                'job_trend_series' => $jobTrend,
            ],
        ]);
    }

    public function getProviderRatings($providerId)
    {
        return $this->ratings($providerId);
    }

    public function getProviderDisputes($providerId)
    {
        return $this->disputes($providerId);
    }


    /**
     * Get ratings distribution + avg rating for range.
     * GET admin/providers/(:num)/ratings
     */
    public function ratings($providerId)
    {
        if (($authResult = $this->checkAdminAuth()) !== true) {
            return $authResult;
        }

        $provider = $this->userModel->find($providerId);
        if (!$provider || $provider['is_provider'] != 1) {
            return $this->failNotFound('Provider not found or not a registered provider.');
        }

        // OPTIONAL range: only apply if user passes from/to
        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');

        $builder = $this->providerRatingsModel->where('provider_id', $providerId);

        if (!empty($from) && !empty($to)) {
            $dateRange = $this->getDateRange();
            $builder->where('created_at >=', $dateRange['from'])
                ->where('created_at <=', $dateRange['to']);
        }

        $ratings = $builder->findAll();

        $totalRatings  = count($ratings);
        $averageRating = 0.0;
        $distribution  = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

        if ($totalRatings > 0) {
            $sum = 0;
            foreach ($ratings as $r) {
                $ratingValue = (int)($r['rating'] ?? 0);
                $sum += $ratingValue;
                if (isset($distribution[$ratingValue])) {
                    $distribution[$ratingValue]++;
                }
            }
            $averageRating = $sum / $totalRatings;
        }

        return $this->respond([
            'data' => [
                'avg_rating'     => round($averageRating, 2),
                'total_ratings'  => $totalRatings,   // add this for UI clarity
                'distribution'   => $distribution,
            ],
        ]);
    }


    /**
     * List disputes for range (paged).
     * GET admin/providers/(:num)/disputes
     */
    public function disputes($providerId)
    {
        if (($authResult = $this->checkAdminAuth()) !== true) {
            return $authResult;
        }

        $provider = $this->userModel->find($providerId);
        if (!$provider || $provider['is_provider'] != 1) {
            return $this->failNotFound('Provider not found or not a registered provider.');
        }

        $pagination = $this->getPaginationParams();

        // OPTIONAL range: only apply if user passes from/to
        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');

        $builder = $this->providerDisputesModel->where('provider_id', $providerId);

        if (!empty($from) && !empty($to)) {
            $dateRange = $this->getDateRange();
            $builder->where('created_at >=', $dateRange['from'])
                ->where('created_at <=', $dateRange['to']);
        }

        $disputes = $builder->findAll($pagination['limit'], $pagination['offset']);

        // count query must re-build (CI builders get “consumed”)
        $countBuilder = $this->providerDisputesModel->where('provider_id', $providerId);
        if (!empty($from) && !empty($to)) {
            $dateRange = $this->getDateRange();
            $countBuilder->where('created_at >=', $dateRange['from'])
                ->where('created_at <=', $dateRange['to']);
        }
        $totalDisputes = $countBuilder->countAllResults();

        return $this->respond([
            'data' => $disputes,
            'pager' => [
                'total' => $totalDisputes,
                'per_page' => $pagination['limit'],
                'current_page' => floor($pagination['offset'] / $pagination['limit']) + 1,
                'last_page' => (int) ceil($totalDisputes / $pagination['limit']),
            ],
        ]);
    }
}
