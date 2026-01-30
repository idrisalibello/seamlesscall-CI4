<?php
namespace App\Modules\Auth\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = ['name',
        'email',
        'phone',
        'password',
        'role',
        'provider',
        'provider_id',
        'is_provider',
        'provider_status',
        'company_name',
         'services', 
        'location',
        'decision_reason',
        'is_company',
        'provider_applied_at'];
    
    protected $useTimestamps = true;
    protected $returnType = 'array';

}
