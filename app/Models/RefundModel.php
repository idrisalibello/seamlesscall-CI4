<?php

namespace App\Models;

use CodeIgniter\Model;

class RefundModel extends Model
{
    protected $table = 'refunds';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'amount',
        'reason',
        'status',
        'processed_by',
        'submitted_at',
        'processed_at'
    ];
}
