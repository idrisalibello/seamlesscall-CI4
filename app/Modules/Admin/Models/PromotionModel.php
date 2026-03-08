<?php

namespace App\Modules\Admin\Models;

use CodeIgniter\Model;

class PromotionModel extends Model
{
    protected $table = 'promotions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'title',
        'description',
        'promotion_type',
        'discount_type',
        'discount_value',
        'code',
        'category_id',
        'service_id',
        'provider_id',
        'start_date',
        'end_date',
        'usage_limit',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}