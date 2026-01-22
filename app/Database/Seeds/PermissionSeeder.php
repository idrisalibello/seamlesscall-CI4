<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // User Management
            ['group_name' => 'Users', 'permission_name' => 'view-users', 'description' => 'View list of users'],
            ['group_name' => 'Users', 'permission_name' => 'create-users', 'description' => 'Create a new user'],
            ['group_name' => 'Users', 'permission_name' => 'edit-users', 'description' => 'Edit user details'],
            ['group_name' => 'Users', 'permission_name' => 'delete-users', 'description' => 'Delete a user'],

            // Provider Management
            ['group_name' => 'Providers', 'permission_name' => 'view-providers', 'description' => 'View list of providers'],
            ['group_name' => 'Providers', 'permission_name' => 'approve-providers', 'description' => 'Approve or reject provider applications'],
            
            // Role & Permission Management
            ['group_name' => 'System', 'permission_name' => 'manage-roles', 'description' => 'Manage roles and permissions'],
            
            // Reports
            ['group_name' => 'Reports', 'permission_name' => 'view-reports', 'description' => 'View system reports'],

            // Finance
            ['group_name' => 'Finance', 'permission_name' => 'view-ledger', 'description' => 'View financial ledger'],
            ['group_name' => 'Finance', 'permission_name' => 'manage-payouts', 'description' => 'Manage provider payouts'],
            ['group_name' => 'Finance', 'permission_name' => 'manage-refunds', 'description' => 'Manage customer refunds'],
        ];

        $this->db->table('permissions')->insertBatch($permissions);
    }
}