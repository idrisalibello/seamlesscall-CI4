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
        // Bands are guidance; can optionally be per-unit.
        'band_is_per_unit',
        'unit_label',
        'currency',
        'status',
        'notes_for_client',
        'notes_for_provider',
        'allow_band_override',
        'max_override_percent',
        'require_admin_review',
        'auto_flag_dispute_threshold',
        // Alert-only thresholds (no hard restriction).
        'warn_variance_percent',
        'critical_variance_percent',
        'require_reason_over_critical',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
