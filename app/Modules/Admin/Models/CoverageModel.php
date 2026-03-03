<?php

namespace App\Modules\Admin\Models;

use CodeIgniter\Model;

class CoverageModel extends Model
{
    protected $table = 'coverages';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name',
        'region',
        'is_active'
    ];

    protected $useTimestamps = false;
}