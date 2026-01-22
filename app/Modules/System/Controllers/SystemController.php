<?php

namespace App\Modules\System\Controllers;

use App\Controllers\BaseController;
use App\Modules\System\Models\RoleModel;
use App\Modules\System\Models\PermissionModel;
use CodeIgniter\API\ResponseTrait;

class SystemController extends BaseController
{
    use ResponseTrait;

    /**
     * A helper function to ensure IDs are integers in the output array.
     */
    private function formatOutput(array $data): array
    {
        return array_map(function ($row) {
            if (isset($row['id'])) {
                $row['id'] = (int)$row['id'];
            }
            if (isset($row['role_id'])) {
                $row['role_id'] = (int)$row['role_id'];
            }
             if (isset($row['permission_id'])) {
                $row['permission_id'] = (int)$row['permission_id'];
            }
            return $row;
        }, $data);
    }

    public function getRoles()
    {
        $roleModel = new RoleModel();
        $roles = $roleModel->asArray()->findAll();
        return $this->respond($this->formatOutput($roles));
    }
    
    public function createRole()
    {
        $roleModel = new RoleModel();
        $data = $this->request->getJSON();

        if (!$roleModel->validate(['role_name' => 'required|is_unique[roles.role_name]'])) {
            return $this->failValidationErrors($roleModel->errors());
        }

        $roleId = $roleModel->insert([
            'role_name' => $data->role_name,
            'description' => $data->description ?? null,
        ]);

        if ($roleId === false) {
            return $this->failServerError('Failed to create role.');
        }

        return $this->respondCreated(['id' => $roleId, 'message' => 'Role created successfully']);
    }

    public function getPermissions()
    {
        $permissionModel = new PermissionModel();
        $permissions = $permissionModel->asArray()->findAll();
        return $this->respond($this->formatOutput($permissions));
    }

    public function getRolePermissions($roleId)
    {
        $roleModel = new RoleModel();
        // This custom method already returns an array of arrays
        $permissions = $roleModel->getPermissions($roleId);
        return $this->respond($this->formatOutput($permissions));
    }

    public function updateRolePermissions($roleId)
    {
        $roleModel = new RoleModel();
        $data = $this->request->getJSON();
        $permissionIds = $data->permission_ids ?? [];

        if (!$roleModel->find($roleId)) {
            return $this->failNotFound('Role not found.');
        }

        if ($roleModel->updatePermissions($roleId, $permissionIds)) {
            return $this->respond(['message' => 'Permissions updated successfully.']);
        }

        return $this->failServerError('Failed to update permissions.');
    }
}