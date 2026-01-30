<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\ProviderRatingsModel; // NEW
use App\Models\ProviderDisputesModel; // NEW
use App\Models\JobsModel; // NEW

class AdminReportingController extends BaseController
{
    use ResponseTrait;

    protected $userModel;
    protected $providerRatingsModel; // NEW
    protected $providerDisputesModel; // NEW
    protected $jobsModel; // NEW

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->providerRatingsModel = new ProviderRatingsModel(); // NEW
        $this->providerDisputesModel = new ProviderDisputesModel(); // NEW
        $this->jobsModel = new JobsModel(); // NEW
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

    public function getOverallProviderPerformance()
    {
        if (($authResult = $this->checkAdminAuth()) !== true) {
            return $authResult;
        }

        $totalProviders = $this->userModel->where('is_provider', 1)->countAllResults();

        // Calculate average rating
        $averageRatingData = $this->providerRatingsModel
                                ->selectAvg('rating', 'average_rating')
                                ->first();
        $averageRating = $averageRatingData ? (float)$averageRatingData['average_rating'] : 0.0;

        $totalDisputes = $this->providerDisputesModel->countAllResults();

        $data = [
            'totalProviders' => $totalProviders,
            'averageRating'  => round($averageRating, 2),
            'totalDisputes'  => $totalDisputes,
        ];

        return $this->respond(['data' => $data]);
    }

    public function getProviderPerformance($providerId)
    {
        if (($authResult = $this->checkAdminAuth()) !== true) {
            return $authResult;
        }

        $provider = $this->userModel->find($providerId);
        if (!$provider || $provider->is_provider != 1) {
            return $this->failNotFound('Provider not found or not a registered provider.');
        }

        $completedJobs = $this->jobsModel->where('provider_id', $providerId)
                                         ->where('status', 'completed')
                                         ->countAllResults();

        $providerRatings = $this->providerRatingsModel->where('provider_id', $providerId)->findAll();
        $averageRating = 0.0;
        if (!empty($providerRatings)) {
            $sumRatings = array_sum(array_column($providerRatings, 'rating'));
            $averageRating = $sumRatings / count($providerRatings);
        }

        $disputesCount = $this->providerDisputesModel->where('provider_id', $providerId)->countAllResults();

        $data = [
            'providerId' => (int)$providerId,
            'providerName' => $provider->name,
            'completedJobs' => $completedJobs,
            'averageRating' => round($averageRating, 2),
            'disputesCount' => $disputesCount,
        ];

        return $this->respond(['data' => $data]);
    }

    public function getProviderRatings($providerId)
    {
        if (($authResult = $this->checkAdminAuth()) !== true) {
            return $authResult;
        }

        $provider = $this->userModel->find($providerId);
        if (!$provider || $provider->is_provider != 1) {
            return $this->failNotFound('Provider not found or not a registered provider.');
        }

        $ratings = $this->providerRatingsModel->where('provider_id', $providerId)->findAll();

        return $this->respond(['data' => $ratings]);
    }

    public function getProviderDisputes($providerId)
    {
        if (($authResult = $this->checkAdminAuth()) !== true) {
            return $authResult;
        }

        $provider = $this->userModel->find($providerId);
        if (!$provider || $provider->is_provider != 1) {
            return $this->failNotFound('Provider not found or not a registered provider.');
        }

        $disputes = $this->providerDisputesModel->where('provider_id', $providerId)->findAll();

        return $this->respond(['data' => $disputes]);
    }
}
