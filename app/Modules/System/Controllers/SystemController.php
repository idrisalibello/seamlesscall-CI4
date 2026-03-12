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
     * Ensure IDs are integers in the output array.
     */
    private function formatOutput(array $data): array
    {
        return array_map(function ($row) {
            if (isset($row['id'])) {
                $row['id'] = (int) $row['id'];
            }
            if (isset($row['role_id'])) {
                $row['role_id'] = (int) $row['role_id'];
            }
            if (isset($row['permission_id'])) {
                $row['permission_id'] = (int) $row['permission_id'];
            }
            return $row;
        }, $data);
    }

    public function getRoles()
    {
        if ($forbidden = $this->requirePermission('manage-roles')) {
            return $forbidden;
        }

        $roleModel = new RoleModel();
        $roles = $roleModel->asArray()->findAll();

        return $this->respond($this->formatOutput($roles));
    }

    public function createRole()
    {
        if ($forbidden = $this->requirePermission('manage-roles')) {
            return $forbidden;
        }

        $data = (array) ($this->request->getJSON(true) ?? []);
        $roleName = trim((string) ($data['role_name'] ?? ''));
        $description = isset($data['description']) ? trim((string) $data['description']) : null;

        if ($roleName === '') {
            return $this->failValidationErrors([
                'role_name' => 'The role_name field is required.',
            ]);
        }

        $roleModel = new RoleModel();

        $exists = $roleModel
            ->where('role_name', $roleName)
            ->first();

        if ($exists) {
            return $this->failValidationErrors([
                'role_name' => 'That role name already exists.',
            ]);
        }

        $roleId = $roleModel->insert([
            'role_name'   => $roleName,
            'description' => $description !== '' ? $description : null,
        ]);

        if ($roleId === false) {
            return $this->failServerError('Failed to create role.');
        }

        return $this->respondCreated([
            'id'      => (int) $roleId,
            'message' => 'Role created successfully',
        ]);
    }

    public function getPermissions()
    {
        if ($forbidden = $this->requirePermission('manage-roles')) {
            return $forbidden;
        }

        $permissionModel = new PermissionModel();
        $permissions = $permissionModel
            ->orderBy('group_name', 'ASC')
            ->orderBy('permission_name', 'ASC')
            ->asArray()
            ->findAll();

        return $this->respond($this->formatOutput($permissions));
    }

    public function getRolePermissions($roleId)
    {
        if ($forbidden = $this->requirePermission('manage-roles')) {
            return $forbidden;
        }

        $roleId = (int) $roleId;
        $roleModel = new RoleModel();

        if (!$roleModel->find($roleId)) {
            return $this->failNotFound('Role not found.');
        }

        $permissions = $roleModel->getPermissions($roleId);

        return $this->respond($this->formatOutput($permissions));
    }

    public function updateRolePermissions($roleId)
    {
        if ($forbidden = $this->requirePermission('manage-roles')) {
            return $forbidden;
        }

        $roleId = (int) $roleId;
        $roleModel = new RoleModel();

        if (!$roleModel->find($roleId)) {
            return $this->failNotFound('Role not found.');
        }

        $data = (array) ($this->request->getJSON(true) ?? []);
        $permissionIds = $data['permission_ids'] ?? [];

        if (!is_array($permissionIds)) {
            return $this->failValidationErrors([
                'permission_ids' => 'permission_ids must be an array.',
            ]);
        }

        $permissionIds = array_values(array_unique(array_map('intval', $permissionIds)));

        if (!empty($permissionIds)) {
            $permissionModel = new PermissionModel();
            $validCount = $permissionModel
                ->whereIn('id', $permissionIds)
                ->countAllResults();

            if ($validCount !== count($permissionIds)) {
                return $this->failValidationErrors([
                    'permission_ids' => 'One or more permission IDs are invalid.',
                ]);
            }
        }

        if ($roleModel->updatePermissions($roleId, $permissionIds)) {
            return $this->respond([
                'message' => 'Permissions updated successfully.',
            ]);
        }

        return $this->failServerError('Failed to update permissions.');
    }
}