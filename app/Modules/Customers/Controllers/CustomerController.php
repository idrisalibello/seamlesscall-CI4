<?php

namespace App\Modules\Customers\Controllers;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\JobModel;
use App\Models\JobPaymentModel;
use App\Models\LedgerModel;
use App\Models\ServiceModel;
use App\Models\ServicePricingAdjustmentModel;
use App\Models\ServicePricingProfileModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use Exception;

class CustomerController extends BaseController
{
    use ResponseTrait;

    protected CategoryModel $categoryModel;
    protected ServiceModel $serviceModel;
    protected ServicePricingProfileModel $profileModel;
    protected ServicePricingAdjustmentModel $adjustmentModel;
    protected JobModel $jobModel;
    protected UserModel $userModel;
    protected JobPaymentModel $jobPaymentModel;
    protected LedgerModel $ledgerModel;

    public function __construct()
    {
        $this->categoryModel   = new CategoryModel();
        $this->serviceModel    = new ServiceModel();
        $this->profileModel    = new ServicePricingProfileModel();
        $this->adjustmentModel = new ServicePricingAdjustmentModel();
        $this->jobModel        = new JobModel();
        $this->userModel       = new UserModel();
        $this->jobPaymentModel = new JobPaymentModel();
        $this->ledgerModel     = new LedgerModel();
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

    private function paystackSecretKey(): string
    {
        return trim((string) env('paystack.secretKey'));
    }

    private function paystackBaseUrl(): string
    {
        $value = trim((string) env('paystack.baseUrl'));
        return $value !== '' ? rtrim($value, '/') : 'https://api.paystack.co';
    }

    private function paystackCallbackUrl(): ?string
    {
        $value = trim((string) env('paystack.callbackUrl'));
        return $value !== '' ? $value : null;
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

    private function normalizeJob(array $row): array
    {
        return [
            'id'                => (int) ($row['id'] ?? 0),
            'customer_id'       => (int) ($row['customer_id'] ?? 0),
            'provider_id'       => isset($row['provider_id']) && $row['provider_id'] !== null ? (int) $row['provider_id'] : null,
            'service_id'        => (int) ($row['service_id'] ?? 0),
            'title'             => (string) ($row['title'] ?? ''),
            'description'       => $row['description'] ?? null,
            'status'            => $row['status'] ?? null,
            'scheduled_time'    => $row['scheduled_time'] ?? null,
            'completed_at'      => $row['completed_at'] ?? null,
            'cancelled_at'      => $row['cancelled_at'] ?? null,
            'assigned_at'       => $row['assigned_at'] ?? null,
            'created_at'        => $row['created_at'] ?? null,
            'updated_at'        => $row['updated_at'] ?? null,
            'escalation_reason' => $row['escalation_reason'] ?? null,
            'escalated_at'      => $row['escalated_at'] ?? null,
            'escalated_by'      => isset($row['escalated_by']) && $row['escalated_by'] !== null ? (int) $row['escalated_by'] : null,
        ];
    }

    private function decodeJobDescription(?string $raw): array
    {
        if (!$raw) {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function amountToSubunit(float $amount, string $currency): int
    {
        $currency = strtoupper(trim($currency));
        if (in_array($currency, ['NGN', 'GHS', 'ZAR'], true)) {
            return (int) round($amount * 100);
        }

        return (int) round($amount * 100);
    }

    private function findInspectionProfileForService(int $serviceId): ?array
    {
        return $this->profileModel
            ->where('service_id', $serviceId)
            ->where('status', 'active')
            ->first();
    }

    private function latestInspectionPaymentSummary(int $jobId, int $serviceId): array
    {
        $profile = $this->findInspectionProfileForService($serviceId);
        $fee = isset($profile['inspection_fee']) ? (float) $profile['inspection_fee'] : 0.0;
        $currency = $profile['currency'] ?? 'NGN';

        $payment = $this->jobPaymentModel->latestForJobPurpose($jobId, 'inspection_fee');

        if ($fee <= 0) {
            return [
                'purpose' => 'inspection_fee',
                'status' => 'success',
                'amount' => 0.0,
                'currency' => $currency,
                'reference' => null,
                'message' => 'Inspection fee is not required for this service.',
            ];
        }

        if (!$payment) {
            return [
                'purpose' => 'inspection_fee',
                'status' => 'inspection_due',
                'amount' => $fee,
                'currency' => $currency,
                'reference' => null,
                'message' => 'Inspection fee is pending.',
            ];
        }

        return [
            'purpose' => 'inspection_fee',
            'status' => (string) ($payment['status'] ?? 'inspection_due'),
            'amount' => isset($payment['amount']) ? (float) $payment['amount'] : $fee,
            'currency' => $payment['currency'] ?? $currency,
            'reference' => $payment['paystack_reference'] ?? null,
            'message' => $payment['gateway_message'] ?? null,
        ];
    }

    private function buildBookingListRow(array $job): array
    {
        $data = $this->normalizeJob($job);
        $data['payment_summary'] = $this->latestInspectionPaymentSummary(
            (int) $data['id'],
            (int) $data['service_id']
        );
        return $data;
    }

    private function buildTrackingPayload(array $job): array
    {
        $meta = $this->decodeJobDescription($job['description'] ?? null);
        $service = $this->serviceModel->find((int) $job['service_id']);

        $provider = null;
        if (!empty($job['provider_id'])) {
            $provider = $this->userModel->find((int) $job['provider_id']);
        }

        $status = strtolower((string) ($job['status'] ?? 'pending'));
        $paymentSummary = $this->latestInspectionPaymentSummary((int) $job['id'], (int) $job['service_id']);

        $timeline = [
            [
                'key'       => 'request_created',
                'title'     => 'Request created',
                'subtitle'  => 'Your booking request was received.',
                'completed' => true,
                'active'    => $status === 'pending',
                'time'      => $job['created_at'] ?? null,
            ],
            [
                'key'       => 'inspection_payment',
                'title'     => 'Inspection payment',
                'subtitle'  => $paymentSummary['status'] === 'success'
                    ? 'Inspection fee has been paid.'
                    : 'Inspection fee is awaiting payment.',
                'completed' => $paymentSummary['status'] === 'success',
                'active'    => $paymentSummary['status'] !== 'success',
                'time'      => $paymentSummary['status'] === 'success' ? ($job['updated_at'] ?? null) : null,
            ],
            [
                'key'       => 'provider_assigned',
                'title'     => 'Technician assigned',
                'subtitle'  => !empty($job['provider_id'])
                    ? 'A provider has been assigned to your job.'
                    : 'No provider has been assigned yet.',
                'completed' => !empty($job['provider_id']),
                'active'    => $status === 'active',
                'time'      => $job['assigned_at'] ?? null,
            ],
            [
                'key'       => 'job_completed',
                'title'     => 'Job completed',
                'subtitle'  => $status === 'completed'
                    ? 'The job has been marked completed.'
                    : 'Completion has not been confirmed yet.',
                'completed' => $status === 'completed',
                'active'    => false,
                'time'      => $job['completed_at'] ?? null,
            ],
        ];

        $statusTitle = match ($status) {
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'active'    => !empty($job['provider_id']) ? 'Technician assigned' : 'In progress',
            'scheduled' => 'Scheduled',
            'escalated' => 'Escalated',
            default     => 'Request received',
        };

        $statusHint = match ($status) {
            'completed' => 'Your service request has been completed.',
            'cancelled' => 'This booking was cancelled.',
            'active'    => !empty($job['provider_id'])
                ? 'A provider has been assigned and work is underway.'
                : 'Your booking is active.',
            'scheduled' => 'Your service is scheduled and awaiting execution.',
            'escalated' => 'This job is under review by the admin team.',
            default     => 'We have received your request and it is awaiting the next action.',
        };

        $primaryAction = $paymentSummary['status'] === 'success'
            ? 'View details'
            : 'Pay inspection';

        return [
            'job' => $this->buildBookingListRow($job),
            'service' => $service ? $this->normalizeService($service) : null,
            'provider' => $provider ? [
                'id'    => (int) $provider['id'],
                'name'  => $provider['name'] ?? 'Assigned technician',
                'phone' => $provider['phone'] ?? null,
            ] : null,
            'meta' => [
                'address'      => $meta['address'] ?? null,
                'note'         => $meta['note'] ?? null,
                'booking_type' => $meta['booking_type'] ?? null,
            ],
            'payment_summary' => $paymentSummary,
            'status_summary' => [
                'title' => $statusTitle,
                'hint'  => $statusHint,
            ],
            'timeline' => $timeline,
            'primary_action_text' => $primaryAction,
        ];
    }

    private function createLedgerEntryForSuccessfulInspection(array $paymentRow): void
    {
        $existing = $this->ledgerModel
            ->where('reference', $paymentRow['paystack_reference'])
            ->first();

        if ($existing) {
            return;
        }

        $this->ledgerModel->insert([
            'user_id' => (int) $paymentRow['customer_id'],
            'transaction_type' => 'inspection_fee_payment',
            'amount' => (float) $paymentRow['amount'],
            'description' => 'Inspection fee payment for job #' . $paymentRow['job_id'],
            'reference' => $paymentRow['paystack_reference'],
        ]);
    }

    private function updatePaymentFromVerifyPayload(array $paymentRow, array $verifyData): array
    {
        $paystackStatus = strtolower((string) ($verifyData['status'] ?? ''));
        $expectedAmountSubunit = $this->amountToSubunit(
            (float) $paymentRow['amount'],
            (string) $paymentRow['currency']
        );
        $returnedAmount = (int) ($verifyData['amount'] ?? 0);

        $newStatus = 'failed';
        if ($paystackStatus === 'success' && $returnedAmount === $expectedAmountSubunit) {
            $newStatus = 'success';
        } elseif (in_array($paystackStatus, ['abandoned', 'failed', 'reversed'], true)) {
            $newStatus = $paystackStatus;
        } else {
            $newStatus = 'pending';
        }

        $update = [
            'status' => $newStatus,
            'gateway_message' => $verifyData['message'] ?? null,
            'paystack_transaction_id' => isset($verifyData['id']) ? (string) $verifyData['id'] : null,
            'verified_at' => date('Y-m-d H:i:s'),
            'metadata_json' => json_encode($verifyData, JSON_UNESCAPED_UNICODE),
        ];

        if ($newStatus === 'success') {
            $update['paid_at'] = date('Y-m-d H:i:s');
        }

        $this->jobPaymentModel->update((int) $paymentRow['id'], $update);
        $updated = $this->jobPaymentModel->find((int) $paymentRow['id']);

        if ($updated && $newStatus === 'success') {
            $this->createLedgerEntryForSuccessfulInspection($updated);
        }

        return $updated ?? array_merge($paymentRow, $update);
    }

    private function callPaystackInitialize(array $payload): array
    {
        $client = service('curlrequest', [
            'baseURI' => $this->paystackBaseUrl(),
            'timeout' => 30,
        ]);

        $response = $client->request('POST', '/transaction/initialize', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->paystackSecretKey(),
                'Content-Type'  => 'application/json',
            ],
            'json' => $payload,
        ]);

        $json = json_decode((string) $response->getBody(), true);
        if (!is_array($json)) {
            throw new Exception('Invalid response from Paystack initialize API.');
        }

        return $json;
    }

    private function callPaystackVerify(string $reference): array
    {
        $client = service('curlrequest', [
            'baseURI' => $this->paystackBaseUrl(),
            'timeout' => 30,
        ]);

        $response = $client->request('GET', '/transaction/verify/' . rawurlencode($reference), [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->paystackSecretKey(),
                'Content-Type'  => 'application/json',
            ],
        ]);

        $json = json_decode((string) $response->getBody(), true);
        if (!is_array($json)) {
            throw new Exception('Invalid response from Paystack verify API.');
        }

        return $json;
    }

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

    public function createBooking()
    {
        if ($resp = $this->requireCustomerAccess()) {
            return $resp;
        }

        $user = service('request')->auth_payload;
        $payload = (array) $this->request->getJSON(true);

        $rules = [
            'service_id'   => 'required|integer',
            'booking_type' => 'required|in_list[asap,scheduled]',
            'scheduled_at' => 'permit_empty|valid_date',
            'address'      => 'required|min_length[3]|max_length[255]',
            'note'         => 'permit_empty|max_length[2000]',
            'service_name' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validateData($payload, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        try {
            $service = $this->serviceModel
                ->where('id', (int) $payload['service_id'])
                ->where('status', 'active')
                ->first();

            if (!$service) {
                return $this->failNotFound('Selected service was not found.');
            }

            $bookingType = (string) $payload['booking_type'];
            $scheduledAt = $bookingType === 'scheduled'
                ? date('Y-m-d H:i:s', strtotime((string) $payload['scheduled_at']))
                : date('Y-m-d H:i:s');

            if ($bookingType === 'scheduled' && empty($payload['scheduled_at'])) {
                return $this->failValidationErrors([
                    'scheduled_at' => 'Scheduled date and time is required for scheduled bookings.',
                ]);
            }

            $title = trim((string) ($payload['service_name'] ?? $service['name'] ?? 'Service Request'));
            if ($title === '') {
                $title = (string) ($service['name'] ?? 'Service Request');
            }

            $description = json_encode([
                'address'      => trim((string) ($payload['address'] ?? '')),
                'note'         => trim((string) ($payload['note'] ?? '')),
                'booking_type' => $bookingType,
            ], JSON_UNESCAPED_UNICODE);

            $insertData = [
                'customer_id'    => (int) $user->id,
                'service_id'     => (int) $service['id'],
                'title'          => $title,
                'description'    => $description,
                'status'         => 'pending',
                'scheduled_time' => $scheduledAt,
            ];

            $bookingId = $this->jobModel->insert($insertData, true);

            if (!$bookingId) {
                $errors = $this->jobModel->errors();
                if (!empty($errors)) {
                    return $this->failValidationErrors($errors);
                }

                return $this->failServerError('Failed to create booking.');
            }

            $booking = $this->jobModel->find((int) $bookingId);

            return $this->respondCreated([
                'message' => 'Booking created successfully.',
                'data'    => $booking ? $this->buildBookingListRow($booking) : ['id' => (int) $bookingId],
            ]);
        } catch (Exception $e) {
            log_message('error', '[CustomerController] createBooking: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    public function getMyBookings()
    {
        if ($resp = $this->requireCustomerAccess()) {
            return $resp;
        }

        $user = service('request')->auth_payload;

        try {
            $rows = $this->jobModel
                ->where('customer_id', (int) $user->id)
                ->orderBy('id', 'DESC')
                ->findAll();

            $data = array_map(fn($row) => $this->buildBookingListRow((array) $row), $rows);

            return $this->respond(['data' => $data]);
        } catch (Exception $e) {
            log_message('error', '[CustomerController] getMyBookings: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    public function getBookingDetails(int $bookingId)
    {
        if ($resp = $this->requireCustomerAccess()) {
            return $resp;
        }

        $user = service('request')->auth_payload;

        try {
            $job = $this->jobModel
                ->where('id', $bookingId)
                ->where('customer_id', (int) $user->id)
                ->first();

            if (!$job) {
                return $this->failNotFound('Booking not found.');
            }

            return $this->respond([
                'data' => $this->buildTrackingPayload((array) $job),
            ]);
        } catch (Exception $e) {
            log_message('error', '[CustomerController] getBookingDetails: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    public function initializeInspectionPayment()
    {
        if ($resp = $this->requireCustomerAccess()) {
            return $resp;
        }

        if ($this->paystackSecretKey() === '') {
            return $this->failServerError('Paystack secret key is not configured.');
        }

        $user    = service('request')->auth_payload;
        $payload = (array) $this->request->getJSON(true);
        $jobId   = (int) ($payload['job_id'] ?? 0);

        if ($jobId <= 0) {
            return $this->failValidationErrors(['job_id' => 'Valid job_id is required.']);
        }

        try {
            $job = $this->jobModel
                ->where('id', $jobId)
                ->where('customer_id', (int) $user->id)
                ->first();

            if (!$job) {
                return $this->failNotFound('Booking not found.');
            }

            $profile = $this->findInspectionProfileForService((int) $job['service_id']);
            if (!$profile) {
                return $this->failNotFound('No active pricing profile found for this service.');
            }

            $originalAmount = (float) ($profile['inspection_fee'] ?? 0);
            $currency       = (string) ($profile['currency'] ?? 'NGN');

            if ($originalAmount <= 0) {
                return $this->failValidationErrors([
                    'inspection_fee' => 'Inspection fee is not configured for this service.',
                ]);
            }

            $existingSuccess = $this->jobPaymentModel
                ->where('job_id', $jobId)
                ->where('purpose', 'inspection_fee')
                ->where('status', 'success')
                ->orderBy('id', 'DESC')
                ->first();

            if ($existingSuccess) {
                return $this->respond([
                    'message' => 'Inspection fee is already paid.',
                    'data' => [
                        'already_paid' => true,
                        'reference'    => $existingSuccess['paystack_reference'] ?? null,
                        'amount'       => (float) $existingSuccess['amount'],
                        'currency'     => $existingSuccess['currency'] ?? $currency,
                    ],
                ]);
            }

            // ── Promotion discount (optional) ──────────────────────────────
            $promotionId     = isset($payload['promotion_id']) ? (int) $payload['promotion_id'] : null;
            $discountApplied = 0.0;
            $finalAmount     = $originalAmount;

            if ($promotionId !== null && $promotionId > 0) {
                $db    = \Config\Database::connect();
                $promo = $db->table('promotions')
                    ->where('id', $promotionId)
                    ->where('status', 'active')
                    ->get()
                    ->getRowArray();

                if ($promo) {
                    [$discountApplied, $finalAmount] =
                        \App\Modules\Customers\Controllers\PromotionValidationController::calculateDiscount(
                            (string) $promo['discount_type'],
                            (float)  $promo['discount_value'],
                            $originalAmount
                        );
                } else {
                    // Invalid promotion — ignore it, do not block payment
                    $promotionId = null;
                }
            }

            $amount = $finalAmount; // amount that will be charged

            $customer = $this->userModel->find((int) $user->id);
            if (!$customer || empty($customer['email'])) {
                return $this->failValidationErrors([
                    'email' => 'Customer email is required before payment can be initialized.',
                ]);
            }

            $reference = 'SCPAY-' . $jobId . '-' . time() . '-' . random_int(1000, 9999);

            $metadata = [
                'job_id'       => $jobId,
                'customer_id'  => (int) $user->id,
                'service_id'   => (int) $job['service_id'],
                'purpose'      => 'inspection_fee',
            ];

            if ($promotionId !== null) {
                $metadata['promotion_id']     = $promotionId;
                $metadata['original_amount']  = $originalAmount;
                $metadata['discount_applied'] = $discountApplied;
            }

            $paystackPayload = [
                'email'    => $customer['email'],
                'amount'   => $this->amountToSubunit($amount, $currency),
                'reference' => $reference,
                'currency' => $currency,
                'metadata' => $metadata,
            ];

            $callbackUrl = $this->paystackCallbackUrl();
            if ($callbackUrl) {
                $paystackPayload['callback_url'] = $callbackUrl;
            }

            $initResponse = $this->callPaystackInitialize($paystackPayload);

            if (($initResponse['status'] ?? false) !== true || empty($initResponse['data']['authorization_url'])) {
                return $this->failServerError($initResponse['message'] ?? 'Unable to initialize payment.');
            }

            $rowId = $this->jobPaymentModel->insert([
                'job_id'               => $jobId,
                'customer_id'          => (int) $user->id,
                'service_id'           => (int) $job['service_id'],
                'provider_id'          => $job['provider_id'] ?? null,
                'purpose'              => 'inspection_fee',
                'amount'               => $amount,
                'currency'             => $currency,
                'gateway'              => 'paystack',
                'paystack_reference'   => $reference,
                'paystack_access_code' => $initResponse['data']['access_code'] ?? null,
                'authorization_url'    => $initResponse['data']['authorization_url'] ?? null,
                'status'               => 'initialized',
                'gateway_message'      => $initResponse['message'] ?? 'Payment initialized',
                'metadata_json'        => json_encode($metadata, JSON_UNESCAPED_UNICODE),
            ], true);

            if (!$rowId) {
                return $this->failServerError('Failed to save initialized payment.');
            }

            // ── Record promotion usage ─────────────────────────────────────
            if ($promotionId !== null && $discountApplied > 0) {
                $db = \Config\Database::connect();
                // Use INSERT IGNORE so duplicate-key on (promotion_id, customer_id, job_id)
                // silently skips if already recorded (idempotent retries)
                $db->query(
                    'INSERT IGNORE INTO promotion_usages
                        (promotion_id, customer_id, job_id, payment_id,
                         original_amount, discount_applied, final_amount, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
                    [
                        $promotionId,
                        (int) $user->id,
                        $jobId,
                        (int) $rowId,
                        $originalAmount,
                        $discountApplied,
                        $amount,
                    ]
                );
            }

            return $this->respondCreated([
                'message' => 'Inspection payment initialized successfully.',
                'data'    => [
                    'job_id'           => $jobId,
                    'reference'        => $reference,
                    'original_amount'  => $originalAmount,
                    'discount_applied' => $discountApplied,
                    'amount'           => $amount,
                    'currency'         => $currency,
                    'authorization_url' => $initResponse['data']['authorization_url'],
                    'access_code'      => $initResponse['data']['access_code'] ?? null,
                ],
            ]);
        } catch (Exception $e) {
            log_message('error', '[CustomerController] initializeInspectionPayment: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    public function verifyPayment(string $reference)
    {
        if ($resp = $this->requireCustomerAccess()) {
            return $resp;
        }

        if ($this->paystackSecretKey() === '') {
            return $this->failServerError('Paystack secret key is not configured.');
        }

        $user = service('request')->auth_payload;

        try {
            $payment = $this->jobPaymentModel
                ->where('paystack_reference', $reference)
                ->where('customer_id', (int) $user->id)
                ->first();

            if (!$payment) {
                return $this->failNotFound('Payment reference not found.');
            }

            $verifyResponse = $this->callPaystackVerify($reference);

            if (($verifyResponse['status'] ?? false) !== true || !isset($verifyResponse['data'])) {
                return $this->failServerError($verifyResponse['message'] ?? 'Unable to verify payment.');
            }

            $updated = $this->updatePaymentFromVerifyPayload($payment, (array) $verifyResponse['data']);

            return $this->respond([
                'message' => 'Payment verification completed.',
                'data' => [
                    'reference' => $updated['paystack_reference'] ?? $reference,
                    'status' => $updated['status'] ?? 'pending',
                    'amount' => isset($updated['amount']) ? (float) $updated['amount'] : 0.0,
                    'currency' => $updated['currency'] ?? 'NGN',
                    'job_id' => isset($updated['job_id']) ? (int) $updated['job_id'] : null,
                ],
            ]);
        } catch (Exception $e) {
            log_message('error', '[CustomerController] verifyPayment: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    public function paystackWebhook()
    {
        if ($this->paystackSecretKey() === '') {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Paystack secret key is not configured.',
            ]);
        }

        try {
            $rawBody = $this->request->getBody() ?? '';
            $signature = $this->request->getHeaderLine('x-paystack-signature');

            $computed = hash_hmac('sha512', $rawBody, $this->paystackSecretKey());
            if ($signature === '' || !hash_equals($computed, $signature)) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => false,
                    'message' => 'Invalid webhook signature.',
                ]);
            }

            $event = json_decode($rawBody, true);
            if (!is_array($event)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => false,
                    'message' => 'Invalid webhook payload.',
                ]);
            }

            if (($event['event'] ?? '') !== 'charge.success') {
                return $this->response->setStatusCode(200)->setJSON([
                    'status' => true,
                    'message' => 'Webhook ignored.',
                ]);
            }

            $data = (array) ($event['data'] ?? []);
            $reference = (string) ($data['reference'] ?? '');

            if ($reference === '') {
                return $this->response->setStatusCode(200)->setJSON([
                    'status' => true,
                    'message' => 'No reference supplied.',
                ]);
            }

            $payment = $this->jobPaymentModel
                ->where('paystack_reference', $reference)
                ->first();

            if (!$payment) {
                return $this->response->setStatusCode(200)->setJSON([
                    'status' => true,
                    'message' => 'Payment reference not found locally.',
                ]);
            }

            if (($payment['status'] ?? '') === 'success') {
                return $this->response->setStatusCode(200)->setJSON([
                    'status' => true,
                    'message' => 'Already processed.',
                ]);
            }

            $updated = $this->updatePaymentFromVerifyPayload($payment, $data);
            $this->jobPaymentModel->update((int) $updated['id'], [
                'webhook_payload' => $rawBody,
            ]);

            return $this->response->setStatusCode(200)->setJSON([
                'status' => true,
                'message' => 'Webhook processed.',
            ]);
        } catch (Exception $e) {
            log_message('error', '[CustomerController] paystackWebhook: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => false,
                'message' => 'Webhook processing failed.',
            ]);
        }
    }
}