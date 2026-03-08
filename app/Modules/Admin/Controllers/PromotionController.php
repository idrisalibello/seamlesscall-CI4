<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\ServiceModel;
use App\Models\UserModel;
use App\Modules\Admin\Models\PromotionModel;
use CodeIgniter\API\ResponseTrait;

class PromotionController extends BaseController
{
    use ResponseTrait;

    private PromotionModel $model;
    private CategoryModel $categoryModel;
    private ServiceModel $serviceModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->model = new PromotionModel();
        $this->categoryModel = new CategoryModel();
        $this->serviceModel = new ServiceModel();
        $this->userModel = new UserModel();
    }

    private function assertAdmin()
    {
        $user = service('request')->auth_payload ?? null;

        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        return null;
    }

    private function currentAdminId(): ?int
    {
        $user = service('request')->auth_payload ?? null;

        if ($user && isset($user->id) && is_numeric($user->id)) {
            return (int) $user->id;
        }

        return null;
    }

    private function normalizeDateTime($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace('T', ' ', $value);
        $ts = strtotime($value);

        if ($ts === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $ts);
    }

    private function normalizePayload(array $data): array
    {
        foreach (['title', 'description', 'promotion_type', 'discount_type', 'code', 'status'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                $data[$key] = trim((string) $data[$key]);
            }
        }

        if (isset($data['promotion_type'])) {
            $data['promotion_type'] = strtolower($data['promotion_type']);
        }

        if (isset($data['discount_type'])) {
            $data['discount_type'] = strtolower($data['discount_type']);
        }

        if (isset($data['status'])) {
            $data['status'] = strtolower($data['status']);
        }

        if (array_key_exists('discount_value', $data) && $data['discount_value'] !== null && $data['discount_value'] !== '') {
            $data['discount_value'] = (float) $data['discount_value'];
        }

        foreach (['category_id', 'service_id', 'provider_id', 'usage_limit', 'created_by', 'updated_by'] as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = ($data[$key] === '' || $data[$key] === null)
                    ? null
                    : (int) $data[$key];
            }
        }

        foreach (['description', 'start_date', 'end_date'] as $key) {
            if (array_key_exists($key, $data) && trim((string) $data[$key]) === '') {
                $data[$key] = null;
            }
        }

        if (array_key_exists('start_date', $data)) {
            $data['start_date'] = $this->normalizeDateTime($data['start_date']);
        }

        if (array_key_exists('end_date', $data)) {
            $data['end_date'] = $this->normalizeDateTime($data['end_date']);
        }

        if (($data['promotion_type'] ?? '') === 'coupon') {
            $data['code'] = !empty($data['code'])
                ? strtoupper(trim((string) $data['code']))
                : null;
        } else {
            $data['code'] = null;
        }

        return $data;
    }

    private function validateBusinessRules(array &$data, ?int $ignoreId = null): ?array
    {
        $errors = [];

        if (($data['promotion_type'] ?? '') === 'service') {
            $hasCategory = !empty($data['category_id']);
            $hasService  = !empty($data['service_id']);

            if (!$hasCategory && !$hasService) {
                $errors['target'] = 'For service promotions, select a category or a service.';
            }

            if ($hasCategory) {
                $category = $this->categoryModel->find((int) $data['category_id']);
                if (!$category) {
                    $errors['category_id'] = 'Selected category was not found.';
                }
            }

            if ($hasService) {
                $service = $this->serviceModel->find((int) $data['service_id']);
                if (!$service) {
                    $errors['service_id'] = 'Selected service was not found.';
                } elseif (!empty($data['category_id']) && (int) $service['category_id'] !== (int) $data['category_id']) {
                    $errors['service_id'] = 'Selected service does not belong to the chosen category.';
                }
            }

            $data['provider_id'] = null;
            $data['code'] = null;
        }

        if (($data['promotion_type'] ?? '') === 'provider') {
            if (empty($data['provider_id'])) {
                $errors['provider_id'] = 'provider_id is required for provider promotions.';
            } else {
                $provider = $this->userModel->find((int) $data['provider_id']);
                if (!$provider) {
                    $errors['provider_id'] = 'Selected provider was not found.';
                }
            }

            $data['category_id'] = null;
            $data['service_id'] = null;
            $data['code'] = null;
        }

        if (($data['promotion_type'] ?? '') === 'coupon') {
            if (empty($data['code'])) {
                $errors['code'] = 'Coupon code is required for coupon promotions.';
            } else {
                $builder = $this->model->where('code', $data['code']);

                if ($ignoreId !== null) {
                    $builder->where('id !=', $ignoreId);
                }

                $existing = $builder->first();
                if ($existing) {
                    $errors['code'] = 'Coupon code already exists.';
                }
            }

            $data['category_id'] = null;
            $data['service_id'] = null;
            $data['provider_id'] = null;
        }

        if (($data['promotion_type'] ?? '') === 'global') {
            $data['category_id'] = null;
            $data['service_id'] = null;
            $data['provider_id'] = null;
            $data['code'] = null;
        }

        if (isset($data['discount_type'], $data['discount_value'])) {
            if ($data['discount_type'] === 'percent' && ((float) $data['discount_value'] <= 0 || (float) $data['discount_value'] > 100)) {
                $errors['discount_value'] = 'Percent discount must be greater than 0 and not exceed 100.';
            }

            if ($data['discount_type'] === 'fixed' && (float) $data['discount_value'] <= 0) {
                $errors['discount_value'] = 'Fixed discount must be greater than 0.';
            }
        }

        if (!empty($data['usage_limit']) && (int) $data['usage_limit'] < 1) {
            $errors['usage_limit'] = 'usage_limit must be at least 1.';
        }

        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                $errors['end_date'] = 'end_date must be later than or equal to start_date.';
            }
        }

        return empty($errors) ? null : $errors;
    }

    private function decodeRow(array $row): array
    {
        foreach (['id', 'category_id', 'service_id', 'provider_id', 'usage_limit', 'created_by', 'updated_by'] as $key) {
            $row[$key] = isset($row[$key]) && $row[$key] !== null ? (int) $row[$key] : null;
        }

        $row['discount_value'] = isset($row['discount_value']) ? (float) $row['discount_value'] : 0.0;

        return $row;
    }

    private function baseBuilder()
    {
        return $this->model
            ->select(
                'promotions.*,
                 categories.name as category_name,
                 services.name as service_name,
                 users.name as provider_name'
            )
            ->join('categories', 'categories.id = promotions.category_id', 'left')
            ->join('services', 'services.id = promotions.service_id', 'left')
            ->join('users', 'users.id = promotions.provider_id', 'left');
    }

    public function index()
    {
        if ($auth = $this->assertAdmin()) {
            return $auth;
        }

        $builder = $this->baseBuilder()->orderBy('promotions.id', 'DESC');

        $promotionType = trim((string) $this->request->getGet('promotion_type'));
        if ($promotionType !== '') {
            $builder->where('promotions.promotion_type', strtolower($promotionType));
        }

        $status = trim((string) $this->request->getGet('status'));
        if ($status !== '') {
            $builder->where('promotions.status', strtolower($status));
        }

        $q = trim((string) $this->request->getGet('q'));
        if ($q !== '') {
            $builder->groupStart()
                ->like('promotions.title', $q)
                ->orLike('promotions.description', $q)
                ->orLike('promotions.code', strtoupper($q))
                ->orLike('categories.name', $q)
                ->orLike('services.name', $q)
                ->orLike('users.name', $q)
                ->groupEnd();
        }

        $rows = array_map(fn(array $row) => $this->decodeRow($row), $builder->findAll());

        return $this->respond(['data' => $rows]);
    }

    public function show($id = null)
    {
        if ($auth = $this->assertAdmin()) {
            return $auth;
        }

        $row = $this->baseBuilder()
            ->where('promotions.id', (int) $id)
            ->first();

        if (!$row) {
            return $this->failNotFound('Promotion not found.');
        }

        return $this->respond(['data' => $this->decodeRow($row)]);
    }

    public function create()
    {
        if ($auth = $this->assertAdmin()) {
            return $auth;
        }

        $adminId = $this->currentAdminId();
        $data = $this->normalizePayload($this->request->getJSON(true) ?? []);
        $data['created_by'] = $adminId;
        $data['updated_by'] = $adminId;
        $data['status'] = $data['status'] ?? 'active';

        $rules = [
            'title' => 'required|min_length[3]|max_length[150]',
            'promotion_type' => 'required|in_list[global,service,provider,coupon]',
            'discount_type' => 'required|in_list[percent,fixed]',
            'discount_value' => 'required|decimal',
            'status' => 'required|in_list[active,inactive]',
        ];

        if (!$this->validateData($data, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if ($errors = $this->validateBusinessRules($data)) {
            return $this->failValidationErrors($errors);
        }

        $newId = $this->model->insert($data, true);
        if (!$newId) {
            return $this->failServerError('Failed to create promotion.');
        }

        $row = $this->baseBuilder()
            ->where('promotions.id', (int) $newId)
            ->first();

        return $this->respondCreated([
            'data' => $this->decodeRow($row),
            'message' => 'Promotion created successfully',
        ]);
    }

    public function update($id = null)
    {
        if ($auth = $this->assertAdmin()) {
            return $auth;
        }

        $existing = $this->model->find((int) $id);
        if (!$existing) {
            return $this->failNotFound('Promotion not found.');
        }

        $payload = $this->normalizePayload($this->request->getJSON(true) ?? []);
        $data = array_merge($existing, $payload);
        $data['updated_by'] = $this->currentAdminId();

        $rules = [
            'title' => 'required|min_length[3]|max_length[150]',
            'promotion_type' => 'required|in_list[global,service,provider,coupon]',
            'discount_type' => 'required|in_list[percent,fixed]',
            'discount_value' => 'required|decimal',
            'status' => 'required|in_list[active,inactive]',
        ];

        if (!$this->validateData($data, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if ($errors = $this->validateBusinessRules($data, (int) $id)) {
            return $this->failValidationErrors($errors);
        }

        if (!$this->model->update((int) $id, $data)) {
            return $this->failServerError('Failed to update promotion.');
        }

        $row = $this->baseBuilder()
            ->where('promotions.id', (int) $id)
            ->first();

        return $this->respond([
            'data' => $this->decodeRow($row),
            'message' => 'Promotion updated successfully',
        ]);
    }

    public function updateStatus($id = null)
    {
        if ($auth = $this->assertAdmin()) {
            return $auth;
        }

        $existing = $this->model->find((int) $id);
        if (!$existing) {
            return $this->failNotFound('Promotion not found.');
        }

        $status = strtolower((string) ($this->request->getJSON(true)['status'] ?? ''));
        if (!in_array($status, ['active', 'inactive'], true)) {
            return $this->failValidationErrors([
                'status' => 'status must be active or inactive.',
            ]);
        }

        $this->model->update((int) $id, [
            'status' => $status,
            'updated_by' => $this->currentAdminId(),
        ]);

        $row = $this->baseBuilder()
            ->where('promotions.id', (int) $id)
            ->first();

        return $this->respond([
            'data' => $this->decodeRow($row),
            'message' => 'Promotion status updated successfully',
        ]);
    }

    public function delete($id = null)
    {
        if ($auth = $this->assertAdmin()) {
            return $auth;
        }

        $existing = $this->model->find((int) $id);
        if (!$existing) {
            return $this->failNotFound('Promotion not found.');
        }

        if (!$this->model->delete((int) $id)) {
            return $this->failServerError('Failed to delete promotion.');
        }

        return $this->respondDeleted([
            'message' => 'Promotion deleted successfully',
        ]);
    }
}