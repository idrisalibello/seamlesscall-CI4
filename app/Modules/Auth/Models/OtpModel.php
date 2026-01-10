<?php

namespace App\Modules\Auth\Models;

use CodeIgniter\Model;

class OtpModel extends Model
{
    protected $table = 'otps';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'otp', 'channel', 'expires_at', 'created_at'];
    protected $useTimestamps = true; // will auto-manage created_at and updated_at
    protected $returnType = 'array';
}
