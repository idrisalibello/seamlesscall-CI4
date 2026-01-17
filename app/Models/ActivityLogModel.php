<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table = 'activity_log';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'action',
        'description',
        'created_at'
    ];
}
