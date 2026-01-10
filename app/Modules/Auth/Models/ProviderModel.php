<?php

namespace App\Modules\Auth\Models;

use CodeIgniter\Model;

class ProviderModel extends Model
{
    protected $table      = 'providers'; // your table for provider applications
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name', 'email', 'phone', 'company', 'address', 'status', 'created_at'
    ];
    protected $useTimestamps = false;
}
