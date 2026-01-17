<?php

namespace App\Models;

use CodeIgniter\Model;

class EarningsModel extends Model
{
    protected $table = 'earnings';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'provider_id',
        'amount',
        'description',
        'job_id',
        'created_at'
    ];
}
