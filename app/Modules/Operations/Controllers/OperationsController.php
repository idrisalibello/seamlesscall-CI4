<?php

namespace App\Modules\Operations\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\JobModel;
use App\Models\UserModel; // For provider/customer details
use Exception;

class OperationsController extends BaseController
{
    use ResponseTrait;

    protected $jobModel;
    protected $userModel;

    public function __construct()
    {
        $this->jobModel = new JobModel();
        $this->userModel = new UserModel();
    }

    /**
     * Helper function to format job output, ensuring IDs are integers.
     */
    private function formatOutput(array $data): array
    {
        return array_map(function ($row) {
            if (is_object($row)) {
                $row = (array)$row;
            }
            if (isset($row['id'])) {
                $row['id'] = (int)$row['id'];
            }
            if (isset($row['customer_id'])) {
                $row['customer_id'] = (int)$row['customer_id'];
            }
            if (isset($row['provider_id'])) {
                $row['provider_id'] = (int)$row['provider_id'];
            }
            if (isset($row['service_id'])) {
                $row['service_id'] = (int)$row['service_id'];
            }
            return $row;
        }, $data);
    }

    /**
     * Get a list of active jobs for the authenticated provider.
     * Accessible by Provider role.
     */
    public function getProviderActiveJobs()
    {
        $providerUser = service('request')->auth_payload;
        if (!$providerUser || !isset($providerUser->id) || $providerUser->role !== 'Provider') {
            return $this->failUnauthorized('Access denied. Providers only.');
        }

        try {
            $jobs = $this->jobModel->getProviderActiveJobs((int)$providerUser->id);
            return $this->respond(['data' => $this->formatOutput($jobs)]);
        } catch (Exception $e) {
            log_message('error', '[OperationsController] getProviderActiveJobs: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    /**
     * Get details of a specific active job for the authenticated provider.
     * Accessible by Provider role.
     */
    public function getProviderJobDetails(int $jobId)
    {
        $providerUser = service('request')->auth_payload;
        if (!$providerUser || !isset($providerUser->id) || $providerUser->role !== 'Provider') {
            return $this->failUnauthorized('Access denied. Providers only.');
        }

        try {
            $job = $this->jobModel->getProviderJobDetails($jobId, (int)$providerUser->id);

            if (!$job) {
                return $this->failNotFound('Job not found or not assigned to you.');
            }
            return $this->respond(['data' => $this->formatOutput([$job])[0]]);
        } catch (Exception $e) {
            log_message('error', '[OperationsController] getProviderJobDetails: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    /**
     * Update the status of a job (e.g., complete, cancel, escalate)
     * Accessible by Provider role.
     */
    public function updateJobStatus(int $jobId)
    {
        $providerUser = service('request')->auth_payload;
        if (!$providerUser || !isset($providerUser->id) || $providerUser->role !== 'Provider') {
            return $this->failUnauthorized('Access denied. Providers only.');
        }

        $input = $this->request->getJSON(true);
        $rules = [
            'status' => 'required|in_list[active,completed,cancelled,escalated]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        $status = $input['status'];

        try {
            // Ensure the job belongs to the provider before updating
            $job = $this->jobModel->find($jobId);
            if (!$job || $job['provider_id'] !== (int)$providerUser->id) {
                return $this->failUnauthorized('You are not authorized to update this job.');
            }

            if ($this->jobModel->updateJobStatus($jobId, $status)) {
                return $this->respondUpdated(['message' => 'Job status updated successfully.']);
            } else {
                return $this->failServerError('Failed to update job status.');
            }
        } catch (Exception $e) {
            log_message('error', '[OperationsController] updateJobStatus: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    /**
     * Assign a job to a provider.
     * Accessible by Admin role.
     */
    public function assignJob(int $jobId)
    {
        $adminUser = service('request')->auth_payload;
        if (!$adminUser || $adminUser->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $input = $this->request->getJSON(true);
        $rules = [
            'provider_id' => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }
        $providerId = $input['provider_id'];

        try {
            // Verify job exists and is not already assigned/completed/cancelled
            $job = $this->jobModel->find($jobId);
            if (!$job) {
                return $this->failNotFound('Job not found.');
            }
            if (!in_array($job['status'], ['pending', 'scheduled'])) {
                return $this->failForbidden('Job cannot be assigned in its current status.');
            }

            // Verify provider exists and is actually a provider
            $provider = $this->userModel->find($providerId);
            if (!$provider || $provider['role'] !== 'Provider') {
                return $this->failNotFound('Provider not found or not a valid provider role.');
            }
            
            // Use updateJobStatus helper in JobModel, but pass providerId to update assigned_at
            $this->jobModel->updateJobStatus($jobId, 'active', $providerId);
            $this->jobModel->update($jobId, ['provider_id' => $providerId, 'assigned_at' => date('Y-m-d H:i:s'), 'status' => 'active']);


            return $this->respondUpdated(['message' => 'Job assigned successfully.']);
        } catch (Exception $e) {
            log_message('error', '[OperationsController] assignJob: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    // Admin endpoints for viewing all active jobs and any job details
    
    /**
     * Get a list of active jobs for Admin.
     * Accessible by Admin role.
     */
    public function getAdminActiveJobs()
    {
        $adminUser = service('request')->auth_payload;
        if (!$adminUser || $adminUser->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        try {
            $jobs = $this->jobModel->getAdminActiveJobs();
            return $this->respond(['data' => $this->formatOutput($jobs)]);
        } catch (Exception $e) {
            log_message('error', '[OperationsController] getAdminActiveJobs: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    /**
     * Get a list of pending jobs for Admin.
     * Accessible by Admin role.
     */
    public function getAdminPendingJobs()
    {
        $adminUser = service('request')->auth_payload;
        if (!$adminUser || $adminUser->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        try {
            $jobs = $this->jobModel->getAdminPendingJobs();
            return $this->respond(['data' => $this->formatOutput($jobs)]);
        } catch (Exception $e) {
            log_message('error', '[OperationsController] getAdminPendingJobs: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    /**
     * Get details of any specific job for Admin.
     * Accessible by Admin role.
     */
    public function getAdminJobDetails(int $jobId)
    {
        $adminUser = service('request')->auth_payload;
        if (!$adminUser || $adminUser->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        try {
            $job = $this->jobModel->getAdminJobDetails($jobId);

            if (!$job) {
                return $this->failNotFound('Job not found.');
            }
            return $this->respond(['data' => $this->formatOutput([$job])[0]]);
        } catch (Exception $e) {
            log_message('error', '[OperationsController] getAdminJobDetails: ' . $e->getMessage());
            return $this->failServerError('An unexpected error occurred.');
        }
    }
}
