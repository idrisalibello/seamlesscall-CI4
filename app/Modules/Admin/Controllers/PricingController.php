<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\ServicePricingAdjustmentModel;
use App\Models\ServicePricingProfileModel;
use CodeIgniter\API\ResponseTrait;

class PricingController extends BaseController
{
    use ResponseTrait;

    private function assertAdmin()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'PricingController: payload: ' . json_encode($user));

        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }
        return null;
    }

    private function normalizeProfile(array $r): array
    {
        foreach (['id', 'service_id', 'max_override_percent', 'auto_flag_dispute_threshold'] as $k) {
            if (isset($r[$k]) && $r[$k] !== null && $r[$k] !== '') {
                $r[$k] = (int) $r[$k];
            }
        }
        foreach (['inspection_fee', 'minimum_job_fee', 'price_band_min', 'price_band_max'] as $k) {
            if (isset($r[$k])) {
                $r[$k] = (float) $r[$k];
            }
        }
        foreach (['allow_band_override', 'require_admin_review'] as $k) {
            if (isset($r[$k])) {
                $r[$k] = (int) $r[$k];
            }
        }
        return $r;
    }

    private function normalizeAdjustment(array $r): array
    {
        foreach (['id', 'profile_id', 'requires_client_approval'] as $k) {
            if (isset($r[$k]) && $r[$k] !== null && $r[$k] !== '') {
                $r[$k] = (int) $r[$k];
            }
        }
        foreach (['value', 'max_allowed'] as $k) {
            if (isset($r[$k])) {
                $r[$k] = (float) $r[$k];
            }
        }
        return $r;
    }

    /**
     * GET /api/v1/admin/pricing/summary
     */
    public function summary()
    {
        if ($resp = $this->assertAdmin()) {
            return $resp;
        }

        $db = db_connect();

        $status = $db->table('service_pricing_profiles')
            ->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()->getResultArray();

        $basis = $db->table('service_pricing_profiles')
            ->select('pricing_basis, COUNT(*) as count')
            ->groupBy('pricing_basis')
            ->get()->getResultArray();

        return $this->respond([
            'data' => [
                'status' => [
                    'values' => ['active', 'inactive'],
                    'counts' => $status,
                ],
                'pricing_basis' => [
                    'values' => ['fixed', 'hourly', 'unit', 'quote_after_inspection'],
                    'counts' => $basis,
                ],
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/pricing/profiles
     * Optional: category_id, service_id, status, pricing_basis, q
     * Optional: page, page_size
     */
    public function profiles()
    {
        if ($resp = $this->assertAdmin()) {
            return $resp;
        }

        $categoryId = $this->request->getGet('category_id');
        $serviceId  = $this->request->getGet('service_id');
        $status     = $this->request->getGet('status');
        $basis      = $this->request->getGet('pricing_basis');
        $q          = $this->request->getGet('q');

        $page = max(1, (int)($this->request->getGet('page') ?? 1));
        $pageSize = (int)($this->request->getGet('page_size') ?? 20);
        if (!in_array($pageSize, [20, 50, 100], true)) {
            $pageSize = 20;
        }
        $offset = ($page - 1) * $pageSize;

        $db = db_connect();
        $base = $db->table('service_pricing_profiles p')
            ->select('p.*, s.name as service_name, s.category_id, c.name as category_name')
            ->join('services s', 's.id = p.service_id', 'left')
            ->join('categories c', 'c.id = s.category_id', 'left');

        if ($categoryId !== null && $categoryId !== '') {
            $base->where('s.category_id', (int) $categoryId);
        }
        if ($serviceId !== null && $serviceId !== '') {
            $base->where('p.service_id', (int) $serviceId);
        }
        if ($status !== null && $status !== '') {
            $base->where('p.status', $status);
        }
        if ($basis !== null && $basis !== '') {
            $base->where('p.pricing_basis', $basis);
        }
        if ($q !== null && $q !== '') {
            $base->groupStart()
                ->like('s.name', $q)
                ->orLike('c.name', $q)
                ->groupEnd();
        }

        $totalRows = (clone $base)->countAllResults();

        $rows = (clone $base)
            ->orderBy('p.updated_at', 'DESC')
            ->limit($pageSize, $offset)
            ->get()->getResultArray();

        $rows = array_map(fn ($r) => $this->normalizeProfile(is_object($r) ? (array) $r : $r), $rows);

        $totalPages = (int) ceil($totalRows / $pageSize);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        return $this->respond([
            'data' => [
                'rows' => $rows,
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
     * GET /api/v1/admin/pricing/profiles/{id}
     */
    public function profile($id = null)
    {
        if ($resp = $this->assertAdmin()) {
            return $resp;
        }

        $id = (int) $id;
        $db = db_connect();
        $row = $db->table('service_pricing_profiles p')
            ->select('p.*, s.name as service_name, s.category_id, c.name as category_name')
            ->join('services s', 's.id = p.service_id', 'left')
            ->join('categories c', 'c.id = s.category_id', 'left')
            ->where('p.id', $id)
            ->get()->getRowArray();

        if (!$row) {
            return $this->failNotFound('No pricing profile found');
        }

        return $this->respond(['data' => $this->normalizeProfile($row)]);
    }

    /**
     * POST /api/v1/admin/pricing/profiles
     */
    public function createProfile()
    {
        if ($resp = $this->assertAdmin()) {
            return $resp;
        }

        $payload = $this->request->getJSON(true) ?? [];

        $rules = [
            'service_id' => 'required|is_natural_no_zero',
            'pricing_basis' => 'required|in_list[fixed,hourly,unit,quote_after_inspection]',
            'inspection_fee' => 'required|numeric',
            'minimum_job_fee' => 'required|numeric',
            'price_band_min' => 'required|numeric',
            'price_band_max' => 'required|numeric',
            'currency' => 'permit_empty|string|max_length[10]',
            'status' => 'permit_empty|in_list[active,inactive]',
            'notes_for_client' => 'permit_empty|string',
            'notes_for_provider' => 'permit_empty|string',
            'allow_band_override' => 'permit_empty|in_list[0,1]',
            'max_override_percent' => 'permit_empty|is_natural',
            'require_admin_review' => 'permit_empty|in_list[0,1]',
            'auto_flag_dispute_threshold' => 'permit_empty|is_natural',
        ];

        if (!$this->validate($rules, $payload)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $min = (float) $payload['price_band_min'];
        $max = (float) $payload['price_band_max'];
        if ($min < 0 || $max < 0 || $max < $min) {
            return $this->failValidationErrors('price_band_max must be >= price_band_min and both must be >= 0');
        }

        $model = new ServicePricingProfileModel();
        $data = [
            'service_id' => (int) $payload['service_id'],
            'pricing_basis' => $payload['pricing_basis'],
            'inspection_fee' => number_format((float) $payload['inspection_fee'], 2, '.', ''),
            'minimum_job_fee' => number_format((float) $payload['minimum_job_fee'], 2, '.', ''),
            'price_band_min' => number_format($min, 2, '.', ''),
            'price_band_max' => number_format($max, 2, '.', ''),
            'currency' => $payload['currency'] ?? 'NGN',
            'status' => $payload['status'] ?? 'active',
            'notes_for_client' => $payload['notes_for_client'] ?? null,
            'notes_for_provider' => $payload['notes_for_provider'] ?? null,
            'allow_band_override' => isset($payload['allow_band_override']) ? (int) $payload['allow_band_override'] : 0,
            'max_override_percent' => isset($payload['max_override_percent']) ? (int) $payload['max_override_percent'] : 0,
            'require_admin_review' => isset($payload['require_admin_review']) ? (int) $payload['require_admin_review'] : 0,
            'auto_flag_dispute_threshold' => isset($payload['auto_flag_dispute_threshold']) ? (int) $payload['auto_flag_dispute_threshold'] : 0,
        ];

        try {
            $id = $model->insert($data, true);
            if (!$id) {
                return $this->failServerError('Failed to create pricing profile');
            }
            return $this->respondCreated(['data' => $this->normalizeProfile(array_merge(['id' => $id], $data))]);
        } catch (\Throwable $e) {
            // service_id is unique => duplicate profile for same service
            return $this->fail($e->getMessage(), 400);
        }
    }

    /**
     * PUT /api/v1/admin/pricing/profiles/{id}
     */
    public function updateProfile($id = null)
    {
        if ($resp = $this->assertAdmin()) {
            return $resp;
        }

        $id = (int) $id;
        $payload = $this->request->getJSON(true) ?? [];

        $rules = [
            'service_id' => 'permit_empty|is_natural_no_zero',
            'pricing_basis' => 'permit_empty|in_list[fixed,hourly,unit,quote_after_inspection]',
            'inspection_fee' => 'permit_empty|numeric',
            'minimum_job_fee' => 'permit_empty|numeric',
            'price_band_min' => 'permit_empty|numeric',
            'price_band_max' => 'permit_empty|numeric',
            'currency' => 'permit_empty|string|max_length[10]',
            'status' => 'permit_empty|in_list[active,inactive]',
            'notes_for_client' => 'permit_empty|string',
            'notes_for_provider' => 'permit_empty|string',
            'allow_band_override' => 'permit_empty|in_list[0,1]',
            'max_override_percent' => 'permit_empty|is_natural',
            'require_admin_review' => 'permit_empty|in_list[0,1]',
            'auto_flag_dispute_threshold' => 'permit_empty|is_natural',
        ];

        if (!$this->validate($rules, $payload)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $model = new ServicePricingProfileModel();
        $existing = $model->find($id);
        if (!$existing) {
            return $this->failNotFound('No pricing profile found');
        }

        $data = [];
        foreach ([
            'service_id', 'pricing_basis', 'currency', 'status',
            'notes_for_client', 'notes_for_provider',
            'allow_band_override', 'max_override_percent',
            'require_admin_review', 'auto_flag_dispute_threshold',
        ] as $k) {
            if (array_key_exists($k, $payload)) {
                $data[$k] = $payload[$k];
            }
        }

        foreach (['inspection_fee', 'minimum_job_fee', 'price_band_min', 'price_band_max'] as $k) {
            if (array_key_exists($k, $payload)) {
                $data[$k] = number_format((float) $payload[$k], 2, '.', '');
            }
        }

        $min = array_key_exists('price_band_min', $payload) ? (float) $payload['price_band_min'] : (float) $existing['price_band_min'];
        $max = array_key_exists('price_band_max', $payload) ? (float) $payload['price_band_max'] : (float) $existing['price_band_max'];
        if ($min < 0 || $max < 0 || $max < $min) {
            return $this->failValidationErrors('price_band_max must be >= price_band_min and both must be >= 0');
        }

        try {
            $model->update($id, $data);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage(), 400);
        }

        $row = $model->find($id);
        return $this->respond(['data' => $this->normalizeProfile($row)]);
    }

    /**
     * PATCH /api/v1/admin/pricing/profiles/{id}/status
     */
    public function updateProfileStatus($id = null)
    {
        if ($resp = $this->assertAdmin()) {
            return $resp;
        }

        $id = (int) $id;
        $payload = $this->request->getJSON(true) ?? [];
        $status = $payload['status'] ?? null;
        if (!in_array($status, ['active', 'inactive'], true)) {
            return $this->failValidationErrors('status must be active or inactive');
        }

        $model = new ServicePricingProfileModel();
        $row = $model->find($id);
        if (!$row) {
            return $this->failNotFound('No pricing profile found');
        }

        $model->update($id, ['status' => $status]);
        $row = $model->find($id);

        return $this->respond(['data' => $this->normalizeProfile($row)]);
    }

    /**
     * DELETE /api/v1/admin/pricing/profiles/{id}
     */
    public function deleteProfile($id = null)
    {
        if ($resp = $this->assertAdmin()) {
            return $resp;
        }

        $id = (int) $id;

        $profileModel = new ServicePricingProfileModel();
        $adjModel = new ServicePricingAdjustmentModel();

        $row = $profileModel->find($id);
        if (!$row) {
            return $this->failNotFound('No pricing profile found');
        }

        // delete children first (non-FK environment safe)
        $adjModel->where('profile_id', $id)->delete();
        $profileModel->delete($id);

        return $this->respond(['message' => 'Deleted']);
    }

    /**
     * GET /api/v1/admin/pricing/profiles/{id}/adjustments
     */
    public function listAdjustments($profileId = null)
    {
        if ($resp = $this->assertAdmin()) {
            return $resp;
        }

        $profileId = (int) $profileId;
        $profileModel = new ServicePricingProfileModel();
        if (!$profileModel->find($profileId)) {
            return $this->failNotFound('No pricing profile found');
        }

        $db = db_connect();
        $rows = $db->table('service_pricing_adjustments')
            ->where('profile_id', $profileId)
            ->orderBy('id', 'DESC')
            ->get()->getResultArray();

        $rows = array_map(fn ($r) => $this->normalizeAdjustment(is_object($r) ? (array) $r : $r), $rows);

        return $this->respond(['data' => $rows]);
    }

    /**
     * POST /api/v1/admin/pricing/profiles/{id}/adjustments
     */
    public function createAdjustment($profileId = null)
    {
        if ($resp = $this->assertAdmin()) {
            return $resp;
        }

        $profileId = (int) $profileId;

        $profileModel = new ServicePricingProfileModel();
        if (!$profileModel->find($profileId)) {
            return $this->failNotFound('No pricing profile found');
        }

        $payload = $this->request->getJSON(true) ?? [];
        $rules = [
            'label' => 'required|string|max_length[255]',
            'adjustment_type' => 'required|in_list[flat,percent]',
            'value' => 'required|numeric',
            'max_allowed' => 'required|numeric',
            'applies_phase' => 'required|in_list[inspection,execution]',
            'requires_client_approval' => 'permit_empty|in_list[0,1]',
            'status' => 'permit_empty|in_list[active,inactive]',
        ];

        if (!$this->validate($rules, $payload)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = [
            'profile_id' => $profileId,
            'label' => $payload['label'],
            'adjustment_type' => $payload['adjustment_type'],
            'value' => number_format((float) $payload['value'], 2, '.', ''),
            'max_allowed' => number_format((float) $payload['max_allowed'], 2, '.', ''),
            'applies_phase' => $payload['applies_phase'],
            'requires_client_approval' => isset($payload['requires_client_approval']) ? (int) $payload['requires_client_approval'] : 1,
            'status' => $payload['status'] ?? 'active',
        ];

        $model = new ServicePricingAdjustmentModel();
        $id = $model->insert($data, true);
        if (!$id) {
            return $this->failServerError('Failed to create adjustment');
        }

        return $this->respondCreated(['data' => $this->normalizeAdjustment(array_merge(['id' => $id], $data))]);
    }

    /**
     * PUT /api/v1/admin/pricing/adjustments/{id}
     */
    public function updateAdjustment($id = null)
    {
        if ($resp = $this->assertAdmin()) {
            return $resp;
        }

        $id = (int) $id;
        $payload = $this->request->getJSON(true) ?? [];

        $rules = [
            'label' => 'permit_empty|string|max_length[255]',
            'adjustment_type' => 'permit_empty|in_list[flat,percent]',
            'value' => 'permit_empty|numeric',
            'max_allowed' => 'permit_empty|numeric',
            'applies_phase' => 'permit_empty|in_list[inspection,execution]',
            'requires_client_approval' => 'permit_empty|in_list[0,1]',
            'status' => 'permit_empty|in_list[active,inactive]',
        ];

        if (!$this->validate($rules, $payload)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $model = new ServicePricingAdjustmentModel();
        $existing = $model->find($id);
        if (!$existing) {
            return $this->failNotFound('No adjustment found');
        }

        $data = [];
        foreach (['label', 'adjustment_type', 'applies_phase', 'requires_client_approval', 'status'] as $k) {
            if (array_key_exists($k, $payload)) {
                $data[$k] = $payload[$k];
            }
        }
        foreach (['value', 'max_allowed'] as $k) {
            if (array_key_exists($k, $payload)) {
                $data[$k] = number_format((float) $payload[$k], 2, '.', '');
            }
        }

        $model->update($id, $data);

        $row = $model->find($id);
        return $this->respond(['data' => $this->normalizeAdjustment($row)]);
    }

    /**
     * PATCH /api/v1/admin/pricing/adjustments/{id}/status
     */
    public function updateAdjustmentStatus($id = null)
    {
        if ($resp = $this->assertAdmin()) {
            return $resp;
        }

        $id = (int) $id;
        $payload = $this->request->getJSON(true) ?? [];
        $status = $payload['status'] ?? null;
        if (!in_array($status, ['active', 'inactive'], true)) {
            return $this->failValidationErrors('status must be active or inactive');
        }

        $model = new ServicePricingAdjustmentModel();
        $row = $model->find($id);
        if (!$row) {
            return $this->failNotFound('No adjustment found');
        }

        $model->update($id, ['status' => $status]);
        $row = $model->find($id);

        return $this->respond(['data' => $this->normalizeAdjustment($row)]);
    }

    /**
     * DELETE /api/v1/admin/pricing/adjustments/{id}
     */
    public function deleteAdjustment($id = null)
    {
        if ($resp = $this->assertAdmin()) {
            return $resp;
        }

        $id = (int) $id;
        $model = new ServicePricingAdjustmentModel();
        $row = $model->find($id);
        if (!$row) {
            return $this->failNotFound('No adjustment found');
        }

        $model->delete($id);
        return $this->respond(['message' => 'Deleted']);
    }
}
