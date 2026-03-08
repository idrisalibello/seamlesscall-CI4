<?php

namespace App\Modules\Customer\Controllers;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\ServiceModel;
use App\Models\ServicePricingProfileModel;
use App\Models\ServicePricingAdjustmentModel;
use CodeIgniter\API\ResponseTrait;
use Exception;

class CustomerController extends BaseController
{
    use ResponseTrait;

    protected CategoryModel $categoryModel;
    protected ServiceModel $serviceModel;
    protected ServicePricingProfileModel $profileModel;
    protected ServicePricingAdjustmentModel $adjustmentModel;

    public function __construct()
    {
        $this->categoryModel   = new CategoryModel();
        $this->serviceModel    = new ServiceModel();
        $this->profileModel    = new ServicePricingProfileModel();
        $this->adjustmentModel = new ServicePricingAdjustmentModel();
    }

    private function requireCustomerAccess()
    {
        $user = service('request')->auth_payload ?? null;

        if (!$user || !isset($user->id) || !isset($user->role)) {
            return $this->failUnauthorized('Access denied. Authentication required.');
        }

        if (!in_array($user->role, ['Customer', 'Admin'], true)) {
            return $this->failUnauthorized('Access denied. Customers only.');
        }

        return null;
    }

    private function normalizeCategory(array $row): array
    {
        return [
            'id'            => (int) ($row['id'] ?? 0),
            'name'          => (string) ($row['name'] ?? ''),
            'description'   => $row['description'] ?? null,
            'status'        => $row['status'] ?? null,
            'service_count' => isset($row['service_count']) ? (int) $row['service_count'] : 0,
            'created_at'    => $row['created_at'] ?? null,
            'updated_at'    => $row['updated_at'] ?? null,
        ];
    }

    private function normalizeService(array $row): array
    {
        return [
            'id'          => (int) ($row['id'] ?? 0),
            'category_id' => (int) ($row['category_id'] ?? 0),
            'name'        => (string) ($row['name'] ?? ''),
            'description' => $row['description'] ?? null,
            'status'      => $row['status'] ?? null,
            'created_at'  => $row['created_at'] ?? null,
            'updated_at'  => $row['updated_at'] ?? null,
        ];
    }

    private function normalizeProfile(array $row): array
    {
        return [
            'id'                           => (int) ($row['id'] ?? 0),
            'service_id'                   => (int) ($row['service_id'] ?? 0),
            'pricing_basis'                => $row['pricing_basis'] ?? null,
            'inspection_fee'               => isset($row['inspection_fee']) ? (float) $row['inspection_fee'] : 0.0,
            'minimum_job_fee'              => isset($row['minimum_job_fee']) ? (float) $row['minimum_job_fee'] : 0.0,
            'price_band_min'               => isset($row['price_band_min']) ? (float) $row['price_band_min'] : 0.0,
            'price_band_max'               => isset($row['price_band_max']) ? (float) $row['price_band_max'] : 0.0,
            'band_is_per_unit'             => isset($row['band_is_per_unit']) ? (int) $row['band_is_per_unit'] : 0,
            'unit_label'                   => $row['unit_label'] ?? null,
            'currency'                     => $row['currency'] ?? 'NGN',
            'status'                       => $row['status'] ?? null,
            'notes_for_client'             => $row['notes_for_client'] ?? null,
            'notes_for_provider'           => $row['notes_for_provider'] ?? null,
            'allow_band_override'          => isset($row['allow_band_override']) ? (int) $row['allow_band_override'] : 0,
            'max_override_percent'         => isset($row['max_override_percent']) ? (int) $row['max_override_percent'] : 0,
            'require_admin_review'         => isset($row['require_admin_review']) ? (int) $row['require_admin_review'] : 0,
            'auto_flag_dispute_threshold'  => isset($row['auto_flag_dispute_threshold']) ? (int) $row['auto_flag_dispute_threshold'] : 0,
            'warn_variance_percent'        => isset($row['warn_variance_percent']) ? (int) $row['warn_variance_percent'] : 0,
            'critical_variance_percent'    => isset($row['critical_variance_percent']) ? (int) $row['critical_variance_percent'] : 0,
            'require_reason_over_critical' => isset($row['require_reason_over_critical']) ? (int) $row['require_reason_over_critical'] : 0,
            'created_at'                   => $row['created_at'] ?? null,
            'updated_at'                   => $row['updated_at'] ?? null,
        ];
    }

    private function normalizeAdjustment(array $row): array
    {
        return [
            'id'                       => (int) ($row['id'] ?? 0),
            'profile_id'               => (int) ($row['profile_id'] ?? 0),
            'label'                    => (string) ($row['label'] ?? ''),
            'adjustment_type'          => $row['adjustment_type'] ?? null,
            'value'                    => isset($row['value']) ? (float) $row['value'] : 0.0,
            'max_allowed'              => isset($row['max_allowed']) ? (float) $row['max_allowed'] : null,
            'applies_phase'            => $row['applies_phase'] ?? null,
            'requires_client_approval' => isset($row['requires_client_approval']) ? (int) $row['requires_client_approval'] : 0,
            'status'                   => $row['status'] ?? null,
            'created_at'               => $row['created_at'] ?? null,
            'updated_at'               => $row['updated_at'] ?? null,
        ];
    }

    /**
     * GET /api/v1/customer/categories
     */
    public function getCategories()
    {
        if ($resp = $this->requireCustomerAccess()) {
            return $resp;
        }

        try {
            $rows = $this->categoryModel
                ->select('categories.*, COUNT(services.id) as service_count')
                ->join('services', 'services.category_id = categories.id AND services.status = "active"', 'left')
                ->where('categories.status', 'active')
                ->groupBy('categories.id')
                ->orderBy('categories.name', 'ASC')
                ->findAll();

            $data = array_map(fn($row) => $this->normalizeCategory((array) $row), $rows);

            return $this->respond(['data' => $data]);
        } catch (Exception $e) {
            log_message('error', '[CustomerController] getCategories: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    /**
     * GET /api/v1/customer/categories/{id}/services
     */
    public function getServicesByCategory(int $categoryId)
    {
        if ($resp = $this->requireCustomerAccess()) {
            return $resp;
        }

        try {
            $category = $this->categoryModel
                ->where('id', $categoryId)
                ->where('status', 'active')
                ->first();

            if (!$category) {
                return $this->failNotFound('No active category found.');
            }

            $rows = $this->serviceModel
                ->where('category_id', $categoryId)
                ->where('status', 'active')
                ->orderBy('name', 'ASC')
                ->findAll();

            $data = array_map(fn($row) => $this->normalizeService((array) $row), $rows);

            return $this->respond(['data' => $data]);
        } catch (Exception $e) {
            log_message('error', '[CustomerController] getServicesByCategory: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    /**
     * GET /api/v1/customer/services/{id}
     */
    public function getServiceDetails(int $serviceId)
    {
        if ($resp = $this->requireCustomerAccess()) {
            return $resp;
        }

        try {
            $service = $this->serviceModel
                ->select('services.*, categories.name as category_name, categories.status as category_status')
                ->join('categories', 'categories.id = services.category_id', 'left')
                ->where('services.id', $serviceId)
                ->where('services.status', 'active')
                ->first();

            if (!$service) {
                return $this->failNotFound('No active service found.');
            }

            if (($service['category_status'] ?? null) !== 'active') {
                return $this->failNotFound('Service category is inactive.');
            }

            $profile = $this->profileModel
                ->where('service_id', $serviceId)
                ->where('status', 'active')
                ->first();

            $adjustments = [];
            if ($profile) {
                $adjustments = $this->adjustmentModel
                    ->where('profile_id', (int) $profile['id'])
                    ->where('status', 'active')
                    ->orderBy('id', 'ASC')
                    ->findAll();
            }

            return $this->respond([
                'data' => [
                    'service' => [
                        'id'            => (int) $service['id'],
                        'category_id'   => (int) $service['category_id'],
                        'category_name' => $service['category_name'] ?? null,
                        'name'          => (string) ($service['name'] ?? ''),
                        'description'   => $service['description'] ?? null,
                        'status'        => $service['status'] ?? null,
                        'created_at'    => $service['created_at'] ?? null,
                        'updated_at'    => $service['updated_at'] ?? null,
                    ],
                    'pricing_profile' => $profile ? $this->normalizeProfile((array) $profile) : null,
                    'pricing_adjustments' => array_map(
                        fn($row) => $this->normalizeAdjustment((array) $row),
                        $adjustments
                    ),
                ],
            ]);
        } catch (Exception $e) {
            log_message('error', '[CustomerController] getServiceDetails: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }
}