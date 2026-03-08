<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Modules\Admin\Models\CoverageRuleModel;
use CodeIgniter\API\ResponseTrait;

class CoverageRuleController extends BaseController
{
    use ResponseTrait;

    private CoverageRuleModel $model;

    public function __construct()
    {
        $this->model = new CoverageRuleModel();
    }

    private function assertAdmin()
    {
        $user = service('request')->auth_payload ?? null;
        log_message('debug', 'CoverageRuleController payload: ' . json_encode($user));

        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        return null;
    }

    private function normalizeDaysFromRequest($days): ?array
    {
        if ($days === null) {
            return null;
        }

        if (is_string($days)) {
            $decoded = json_decode($days, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $days = $decoded;
            } else {
                $days = array_map('trim', explode(',', $days));
            }
        }

        if (!is_array($days)) {
            return null;
        }

        $allowed = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $normalized = [];

        foreach ($days as $day) {
            $value = strtolower(trim((string) $day));
            if ($value === '') {
                continue;
            }
            if (in_array($value, $allowed, true)) {
                $normalized[] = $value;
            }
        }

        $normalized = array_values(array_unique($normalized));
        sort($normalized);

        return $normalized;
    }

    private function normalizeTime(?string $time): ?string
    {
        if ($time === null) {
            return null;
        }

        $time = trim($time);
        if ($time === '') {
            return null;
        }

        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time . ':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }

        return null;
    }

    private function decodeRow(array $row): array
    {
        $row['id'] = isset($row['id']) ? (int) $row['id'] : null;
        $row['category_id'] = isset($row['category_id']) ? (int) $row['category_id'] : null;
        $row['availability_days'] = !empty($row['availability_days'])
            ? (json_decode((string) $row['availability_days'], true) ?: [])
            : [];

        return $row;
    }

    public function index()
    {
        if ($auth = $this->assertAdmin()) {
            return $auth;
        }

        $builder = $this->model
            ->select('coverage_rules.*, categories.name as category_name')
            ->join('categories', 'categories.id = coverage_rules.category_id', 'left')
            ->orderBy('coverage_rules.id', 'DESC');

        $status = trim((string) $this->request->getGet('status'));
        if ($status !== '') {
            $builder->where('coverage_rules.status', $status);
        }

        $categoryId = $this->request->getGet('category_id');
        if ($categoryId !== null && $categoryId !== '') {
            $builder->where('coverage_rules.category_id', (int) $categoryId);
        }

        $q = trim((string) $this->request->getGet('q'));
        if ($q !== '') {
            $builder->groupStart()
                ->like('categories.name', $q)
                ->orLike('coverage_rules.state', $q)
                ->orLike('coverage_rules.lga', $q)
                ->orLike('coverage_rules.city', $q)
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

        $row = $this->model
            ->select('coverage_rules.*, categories.name as category_name')
            ->join('categories', 'categories.id = coverage_rules.category_id', 'left')
            ->where('coverage_rules.id', (int) $id)
            ->first();

        if (!$row) {
            return $this->failNotFound('No availability rule found');
        }

        return $this->respond(['data' => $this->decodeRow($row)]);
    }

    public function create()
    {
        if ($auth = $this->assertAdmin()) {
            return $auth;
        }

        $data = $this->request->getJSON(true) ?? [];
        $data['availability_days'] = $this->normalizeDaysFromRequest($data['availability_days'] ?? null);
        $data['availability_time_start'] = $this->normalizeTime($data['availability_time_start'] ?? null);
        $data['availability_time_end'] = $this->normalizeTime($data['availability_time_end'] ?? null);
        $data['status'] = strtolower((string) ($data['status'] ?? 'active'));

        $rules = [
            'category_id' => 'required|integer|greater_than[0]',
            'state' => 'required|min_length[2]|max_length[100]',
            'lga' => 'permit_empty|max_length[100]',
            'city' => 'permit_empty|max_length[100]',
            'status' => 'required|in_list[active,inactive]',
        ];

        if (!$this->validateData($data, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if (!empty($data['availability_days']) && !is_array($data['availability_days'])) {
            return $this->failValidationErrors([
                'availability_days' => 'availability_days must be an array of day codes.'
            ]);
        }

        if (($data['availability_time_start'] ?? null) === null && ($data['availability_time_end'] ?? null) !== null) {
            return $this->failValidationErrors([
                'availability_time_start' => 'Start time is required when end time is provided.'
            ]);
        }

        if (($data['availability_time_start'] ?? null) !== null && ($data['availability_time_end'] ?? null) === null) {
            return $this->failValidationErrors([
                'availability_time_end' => 'End time is required when start time is provided.'
            ]);
        }

        if (($data['availability_time_start'] ?? null) !== null && ($data['availability_time_end'] ?? null) !== null) {
            if (strcmp($data['availability_time_end'], $data['availability_time_start']) <= 0) {
                return $this->failValidationErrors([
                    'availability_time_end' => 'End time must be later than start time.'
                ]);
            }
        }

        $data['availability_days'] = !empty($data['availability_days'])
            ? json_encode($data['availability_days'])
            : null;

        $newId = $this->model->insert($data, true);
        if (!$newId) {
            return $this->failServerError('Failed to create availability rule.');
        }

        $row = $this->model
            ->select('coverage_rules.*, categories.name as category_name')
            ->join('categories', 'categories.id = coverage_rules.category_id', 'left')
            ->where('coverage_rules.id', (int) $newId)
            ->first();

        return $this->respondCreated([
            'data' => $this->decodeRow($row),
            'message' => 'Availability rule created successfully',
        ]);
    }

    public function update($id = null)
    {
        if ($auth = $this->assertAdmin()) {
            return $auth;
        }

        $existing = $this->model->find((int) $id);
        if (!$existing) {
            return $this->failNotFound('No availability rule found');
        }

        $data = $this->request->getJSON(true) ?? [];
        $raw = $this->request->getJSON(true) ?? [];

        if (array_key_exists('availability_days', $data)) {
            $data['availability_days'] = $this->normalizeDaysFromRequest($data['availability_days']);
            if ($data['availability_days'] !== null && !is_array($data['availability_days'])) {
                return $this->failValidationErrors([
                    'availability_days' => 'availability_days must be an array of day codes.'
                ]);
            }
            $data['availability_days'] = !empty($data['availability_days'])
                ? json_encode($data['availability_days'])
                : null;
        }

        if (array_key_exists('availability_time_start', $data)) {
            $data['availability_time_start'] = $this->normalizeTime($data['availability_time_start']);
            if (($raw['availability_time_start'] ?? null) !== null && $data['availability_time_start'] === null) {
                return $this->failValidationErrors([
                    'availability_time_start' => 'Start time must be HH:MM or HH:MM:SS.'
                ]);
            }
        }

        if (array_key_exists('availability_time_end', $data)) {
            $data['availability_time_end'] = $this->normalizeTime($data['availability_time_end']);
            if (($raw['availability_time_end'] ?? null) !== null && $data['availability_time_end'] === null) {
                return $this->failValidationErrors([
                    'availability_time_end' => 'End time must be HH:MM or HH:MM:SS.'
                ]);
            }
        }

        if (isset($data['status'])) {
            $data['status'] = strtolower((string) $data['status']);
        }

        $rules = [
            'category_id' => 'permit_empty|integer|greater_than[0]',
            'state' => 'permit_empty|min_length[2]|max_length[100]',
            'lga' => 'permit_empty|max_length[100]',
            'city' => 'permit_empty|max_length[100]',
            'status' => 'permit_empty|in_list[active,inactive]',
        ];

        if (!$this->validateData($data, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $start = array_key_exists('availability_time_start', $data)
            ? $data['availability_time_start']
            : $existing['availability_time_start'];

        $end = array_key_exists('availability_time_end', $data)
            ? $data['availability_time_end']
            : $existing['availability_time_end'];

        if (($start === null) xor ($end === null)) {
            return $this->failValidationErrors([
                'availability_time_start' => 'Start and end time must both be provided together.',
                'availability_time_end' => 'Start and end time must both be provided together.',
            ]);
        }

        if ($start !== null && $end !== null && strcmp($end, $start) <= 0) {
            return $this->failValidationErrors([
                'availability_time_end' => 'End time must be later than start time.'
            ]);
        }

        if (empty($data)) {
            return $this->failValidationErrors('No data to update.');
        }

        if (!$this->model->update((int) $id, $data)) {
            return $this->failServerError('Failed to update availability rule.');
        }

        $row = $this->model
            ->select('coverage_rules.*, categories.name as category_name')
            ->join('categories', 'categories.id = coverage_rules.category_id', 'left')
            ->where('coverage_rules.id', (int) $id)
            ->first();

        return $this->respondUpdated([
            'data' => $this->decodeRow($row),
            'message' => 'Availability rule updated successfully',
        ]);
    }

    public function updateStatus($id = null)
    {
        if ($auth = $this->assertAdmin()) {
            return $auth;
        }

        $existing = $this->model->find((int) $id);
        if (!$existing) {
            return $this->failNotFound('No availability rule found');
        }

        $data = $this->request->getJSON(true) ?? [];
        $status = strtolower(trim((string) ($data['status'] ?? '')));

        if (!in_array($status, ['active', 'inactive'], true)) {
            return $this->failValidationErrors([
                'status' => 'status must be active or inactive'
            ]);
        }

        if (!$this->model->update((int) $id, ['status' => $status])) {
            return $this->failServerError('Failed to update availability rule status.');
        }

        $row = $this->model
            ->select('coverage_rules.*, categories.name as category_name')
            ->join('categories', 'categories.id = coverage_rules.category_id', 'left')
            ->where('coverage_rules.id', (int) $id)
            ->first();

        return $this->respond([
            'data' => $this->decodeRow($row),
            'message' => 'Availability rule status updated successfully',
        ]);
    }

    public function delete($id = null)
    {
        if ($auth = $this->assertAdmin()) {
            return $auth;
        }

        $existing = $this->model->find((int) $id);
        if (!$existing) {
            return $this->failNotFound('No availability rule found');
        }

        if (!$this->model->delete((int) $id)) {
            return $this->failServerError('Failed to delete availability rule.');
        }

        return $this->respondDeleted([
            'id' => (int) $id,
            'message' => 'Availability rule deleted successfully',
        ]);
    }
}