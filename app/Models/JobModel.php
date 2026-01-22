<?php

namespace App\Models;

use CodeIgniter\Model;

class JobModel extends Model
{
    protected $table = 'jobs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array'; // Or 'object' if preferred
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'customer_id',
        'provider_id',
        'service_id',
        'title',
        'description',
        'status',
        'scheduled_time',
        'completed_at',
        'cancelled_at',
        'assigned_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'customer_id'    => 'required|integer',
        'service_id'     => 'required|integer',
        'title'          => 'required|min_length[3]|max_length[255]',
        'description'    => 'permit_empty',
        'status'         => 'required|in_list[pending,active,scheduled,completed,cancelled,escalated]',
        'scheduled_time' => 'required|valid_date',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get active jobs for a provider.
     * An "active" job could be 'active' or 'scheduled'.
     */
    public function getProviderActiveJobs(int $providerId): array
    {
        return $this->select('jobs.*, users.name as customer_name, services.name as service_name')
                    ->join('users', 'users.id = jobs.customer_id')
                    ->join('services', 'services.id = jobs.service_id')
                    ->where('jobs.provider_id', $providerId)
                    ->whereIn('jobs.status', ['active', 'scheduled'])
                    ->findAll();
    }

    /**
     * Get job details by ID for a provider, ensuring it's an active/scheduled job.
     */
    public function getProviderJobDetails(int $jobId, int $providerId): ?array
    {
        return $this->select('jobs.*, users.name as customer_name, users.phone as customer_phone, services.name as service_name')
                    ->join('users', 'users.id = jobs.customer_id')
                    ->join('services', 'services.id = jobs.service_id')
                    ->where('jobs.id', $jobId)
                    ->where('jobs.provider_id', $providerId)
                    ->whereIn('jobs.status', ['active', 'scheduled'])
                    ->first();
    }

    /**
     * Get active jobs for admin.
     * An "active" job could be 'active' or 'scheduled'.
     */
    public function getAdminActiveJobs(): array
    {
        return $this->select('jobs.*, users.name as customer_name, providers.name as provider_name, services.name as service_name')
                    ->join('users', 'users.id = jobs.customer_id')
                    ->join('users as providers', 'providers.id = jobs.provider_id', 'left') // Left join for optional provider
                    ->join('services', 'services.id = jobs.service_id')
                    ->whereIn('jobs.status', ['active', 'scheduled'])
                    ->findAll();
    }

    /**
     * Get job details by ID for an admin, ensuring it's an active/scheduled job.
     */
    public function getAdminJobDetails(int $jobId): ?array
    {
        return $this->select('jobs.*, users.name as customer_name, users.phone as customer_phone, providers.name as provider_name, providers.phone as provider_phone, services.name as service_name')
                    ->join('users', 'users.id = jobs.customer_id')
                    ->join('users as providers', 'providers.id = jobs.provider_id', 'left') // Left join for optional provider
                    ->join('services', 'services.id = jobs.service_id')
                    ->where('jobs.id', $jobId)
                    ->whereIn('jobs.status', ['active', 'scheduled'])
                    ->first();
    }

    /**
     * Update job status and relevant timestamps.
     */
    public function updateJobStatus(int $jobId, string $status, ?int $userId = null): bool
    {
        $data = ['status' => $status];

        if ($status === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'cancelled') {
            $data['cancelled_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'assigned' && $userId !== null) { // Custom status for assignment, not a job status enum value
            $data['provider_id'] = $userId;
            $data['assigned_at'] = date('Y-m-d H:i:s');
            // Do not change job status here, assignment is an action not a status
        }
        
        // This is crucial: only update if the job exists and matches criteria for update
        return $this->update($jobId, $data);
    }
}
