<?php
namespace App\Modules\Auth\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name','email','phone','password','role','kyc_status'];
    protected $useTimestamps = true;
    protected $returnType = 'array';

}
