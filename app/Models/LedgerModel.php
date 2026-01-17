<?php

namespace App\Models;

use CodeIgniter\Model;

class LedgerModel extends Model
{
    protected $table = 'ledger';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'transaction_type',
        'amount',
        'description',
        'reference',
        'created_at'
    ];
}
