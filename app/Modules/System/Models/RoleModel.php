<?php

namespace App\Modules\System\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $allowedFields = ['role_name', 'description'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getPermissions($roleId)
    {
        return $this->db->table('role_permissions')
            ->where('role_id', $roleId)
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->get()->getResultArray();
    }

    public function updatePermissions($roleId, $permissionIds)
    {
        // Start a transaction
        $this->db->transStart();

        // Remove existing permissions
        $this->db->table('role_permissions')->where('role_id', $roleId)->delete();

        // Add new permissions
        if (!empty($permissionIds)) {
            $data = [];
            foreach ($permissionIds as $permissionId) {
                $data[] = ['role_id' => $roleId, 'permission_id' => $permissionId];
            }
            $this->db->table('role_permissions')->insertBatch($data);
        }

        // Complete the transaction
        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
