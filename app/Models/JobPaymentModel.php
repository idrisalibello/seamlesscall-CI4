<?php

namespace App\Models;

use CodeIgniter\Model;

class JobPaymentModel extends Model
{
    protected $table = 'job_payments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'job_id',
        'customer_id',
        'service_id',
        'provider_id',
        'purpose',
        'amount',
        'currency',
        'gateway',
        'paystack_reference',
        'paystack_access_code',
        'paystack_transaction_id',
        'authorization_url',
        'status',
        'gateway_message',
        'paid_at',
        'verified_at',
        'metadata_json',
        'webhook_payload',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'job_id' => 'required|integer',
        'customer_id' => 'required|integer',
        'service_id' => 'required|integer',
        'purpose' => 'required|max_length[50]',
        'amount' => 'required|decimal',
        'currency' => 'required|max_length[10]',
        'status' => 'required|max_length[30]',
    ];

    public function latestForJobPurpose(int $jobId, string $purpose): ?array
    {
        return $this->where('job_id', $jobId)
            ->where('purpose', $purpose)
            ->orderBy('id', 'DESC')
            ->first();
    }
}