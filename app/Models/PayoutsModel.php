<?php

namespace App\Models;

use CodeIgniter\Model;

class PayoutsModel extends Model
{
    protected $table = 'payouts';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'provider_id',
        'amount',
        'status',
        'payment_method',
        'transaction_id',
        'requested_at',
        'processed_at'
    ];
}
