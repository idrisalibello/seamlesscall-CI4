<?php

namespace App\Modules\Admin\Models;

use CodeIgniter\Model;

class CoverageRuleModel extends Model
{
    protected $table = 'coverage_rules';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'category_id',
        'state',
        'lga',
        'city',
        'availability_days',
        'availability_time_start',
        'availability_time_end',
        'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}