<?php

namespace App\Models;

use CodeIgniter\Model;

class ProviderDisputesModel extends Model
{
    protected $table = 'provider_disputes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array'; // or 'object'
    protected $useSoftDeletes = false; // No soft deletes for disputes

    protected $allowedFields = ['job_id', 'provider_id', 'raised_by', 'status', 'reason', 'resolved_at'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // No validation rules for now, can be added later
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
}
