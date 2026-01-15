<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'provider',
        'provider_id',
        'is_provider',
        'provider_status',
        'company_name',
        'services', 
        'location',
        'is_company',
        'provider_applied_at',
        'approved_by',
        'approved_at'
    ];

    protected $useTimestamps = true;

    public function findUserByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }

    public function findUserByProviderId(string $providerId)
    {
        return $this->where('provider_id', $providerId)->first();
    }

    public function createUser(array $data)
    {
        return $this->insert($data);
    }

    public function countCustomers(): int
    {
        return $this->where('role', 'Customer')->countAllResults();
    }

    public function countProviders(): int
    {
        return $this->where('role', 'Provider')->countAllResults();
    }
}
