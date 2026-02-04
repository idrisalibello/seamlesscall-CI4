<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicePricingAdjustmentModel extends Model
{
    protected $table      = 'service_pricing_adjustments';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'profile_id',
        'label',
        'adjustment_type',
        'value',
        'max_allowed',
        'applies_phase',
        'requires_client_approval',
        'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
