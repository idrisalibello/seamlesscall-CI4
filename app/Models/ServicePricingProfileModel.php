<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicePricingProfileModel extends Model
{
    protected $table      = 'service_pricing_profiles';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'service_id',
        'pricing_basis',
        'inspection_fee',
        'minimum_job_fee',
        'price_band_min',
        'price_band_max',
        'currency',
        'status',
        'notes_for_client',
        'notes_for_provider',
        'allow_band_override',
        'max_override_percent',
        'require_admin_review',
        'auto_flag_dispute_threshold',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
