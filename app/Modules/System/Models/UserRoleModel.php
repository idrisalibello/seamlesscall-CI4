<?php

namespace App\Modules\System\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table = 'user_roles';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'role_id'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get roles assigned to a specific user.
     */
    public function getUserRoles(int $userId): array
    {
        return $this->select('roles.id, roles.role_name, roles.description')
                    ->join('roles', 'roles.id = user_roles.role_id')
                    ->where('user_id', $userId)
                    ->asArray()
                    ->findAll();
    }

    /**
     * Update roles assigned to a specific user.
     */
    public function updateUserRoles(int $userId, array $roleIds): bool
    {
        $this->db->transStart();

        // Remove existing roles for the user
        $this->where('user_id', $userId)->delete();

        // Add new roles
        if (!empty($roleIds)) {
            $data = [];
            foreach ($roleIds as $roleId) {
                $data[] = ['user_id' => $userId, 'role_id' => $roleId];
            }
            $this->insertBatch($data);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
