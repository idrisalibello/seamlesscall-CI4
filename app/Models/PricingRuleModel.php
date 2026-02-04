<?php

namespace App\Models;

use CodeIgniter\Model;

class PricingRuleModel extends Model
{
    protected $table      = 'pricing_rules';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'label',
        'scope',
        'category_id',
        'service_id',
        'charge_type',
        'amount',
        'status',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
