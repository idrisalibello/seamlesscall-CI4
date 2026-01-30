<?php

namespace App\Models;

use CodeIgniter\Model;

class ProviderRatingsModel extends Model
{
    protected $table = 'provider_ratings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array'; // or 'object'
    protected $useSoftDeletes = false; // No soft deletes for ratings

    protected $allowedFields = ['job_id', 'provider_id', 'customer_id', 'rating', 'comment'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // No validation rules for now, can be added later
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
}
