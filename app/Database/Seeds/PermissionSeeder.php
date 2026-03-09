<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Dashboard
            ['group_name' => 'Dashboard', 'permission_name' => 'view-dashboard', 'description' => 'Access admin dashboard'],

            // Operations
            ['group_name' => 'Operations', 'permission_name' => 'view-active-jobs', 'description' => 'View active jobs'],
            ['group_name' => 'Operations', 'permission_name' => 'view-pending-jobs', 'description' => 'View pending jobs'],
            ['group_name' => 'Operations', 'permission_name' => 'view-scheduled-jobs', 'description' => 'View scheduled jobs'],
            ['group_name' => 'Operations', 'permission_name' => 'view-cancelled-jobs', 'description' => 'View cancelled jobs'],
            ['group_name' => 'Operations', 'permission_name' => 'use-dispatch-center', 'description' => 'Access dispatch center'],
            ['group_name' => 'Operations', 'permission_name' => 'manage-escalations', 'description' => 'View and manage escalations'],

            // People
            ['group_name' => 'People', 'permission_name' => 'view-customers', 'description' => 'View customers'],
            ['group_name' => 'People', 'permission_name' => 'view-providers', 'description' => 'View providers'],
            ['group_name' => 'People', 'permission_name' => 'manage-provider-applications', 'description' => 'Review provider applications'],
            ['group_name' => 'People', 'permission_name' => 'view-verification-queue', 'description' => 'View verification queue'],
            ['group_name' => 'People', 'permission_name' => 'manage-verification-queue', 'description' => 'Approve, reject, or escalate verification cases'],
            ['group_name' => 'People', 'permission_name' => 'view-provider-performance', 'description' => 'View provider performance'],

            // Finance
            ['group_name' => 'Finance', 'permission_name' => 'view-earnings-overview', 'description' => 'View earnings overview'],
            ['group_name' => 'Finance', 'permission_name' => 'view-payouts', 'description' => 'View provider payouts'],
            ['group_name' => 'Finance', 'permission_name' => 'manage-payouts', 'description' => 'Mark payouts paid or failed'],
            ['group_name' => 'Finance', 'permission_name' => 'view-platform-commissions', 'description' => 'View platform commissions'],
            ['group_name' => 'Finance', 'permission_name' => 'manage-platform-commissions', 'description' => 'Manage platform commission configuration and confirmations'],
            ['group_name' => 'Finance', 'permission_name' => 'view-refunds-disputes', 'description' => 'View refunds and disputes'],
            ['group_name' => 'Finance', 'permission_name' => 'manage-refunds-disputes', 'description' => 'Manage refunds and disputes'],
            ['group_name' => 'Finance', 'permission_name' => 'view-ledger', 'description' => 'View financial ledger'],

            // Configuration
            ['group_name' => 'Configuration', 'permission_name' => 'manage-categories', 'description' => 'Manage categories and services'],
            ['group_name' => 'Configuration', 'permission_name' => 'manage-pricing', 'description' => 'Manage pricing rules and pricing profiles'],
            ['group_name' => 'Configuration', 'permission_name' => 'manage-coverage', 'description' => 'Manage coverage areas'],
            ['group_name' => 'Configuration', 'permission_name' => 'manage-availability', 'description' => 'Manage availability and coverage rules'],
            ['group_name' => 'Configuration', 'permission_name' => 'manage-promotions', 'description' => 'Manage promotions'],
            ['group_name' => 'Configuration', 'permission_name' => 'manage-appearance', 'description' => 'Manage appearance configuration'],

            // Reports
            ['group_name' => 'Reports', 'permission_name' => 'view-roles-permissions-report', 'description' => 'View roles and permissions report'],
            ['group_name' => 'Reports', 'permission_name' => 'view-integrations-report', 'description' => 'View integrations report'],
            ['group_name' => 'Reports', 'permission_name' => 'view-feature-toggles-report', 'description' => 'View feature toggles report'],
            ['group_name' => 'Reports', 'permission_name' => 'view-maintenance-mode-report', 'description' => 'View maintenance mode report'],
            ['group_name' => 'Reports', 'permission_name' => 'view-audit-trail-report', 'description' => 'View audit trail report'],
            ['group_name' => 'Reports', 'permission_name' => 'view-system-health-report', 'description' => 'View system health report'],

            // System
            ['group_name' => 'System', 'permission_name' => 'view-users', 'description' => 'View list of users'],
            ['group_name' => 'System', 'permission_name' => 'create-users', 'description' => 'Create a new user'],
            ['group_name' => 'System', 'permission_name' => 'edit-users', 'description' => 'Edit user details'],
            ['group_name' => 'System', 'permission_name' => 'delete-users', 'description' => 'Delete users'],
            ['group_name' => 'System', 'permission_name' => 'assign-user-roles', 'description' => 'Assign roles to users'],
            ['group_name' => 'System', 'permission_name' => 'create-admin-users', 'description' => 'Create admin users'],
            ['group_name' => 'System', 'permission_name' => 'manage-roles', 'description' => 'Manage roles and permissions'],
            ['group_name' => 'System', 'permission_name' => 'manage-integrations', 'description' => 'Manage integrations'],
            ['group_name' => 'System', 'permission_name' => 'manage-feature-toggles', 'description' => 'Manage feature toggles'],
            ['group_name' => 'System', 'permission_name' => 'manage-maintenance-mode', 'description' => 'Manage maintenance mode'],
        ];

        $table = $this->db->table('permissions');

        foreach ($permissions as $permission) {
            $existing = $table
                ->where('permission_name', $permission['permission_name'])
                ->get()
                ->getRowArray();

            if ($existing) {
                $table
                    ->where('id', $existing['id'])
                    ->update([
                        'group_name'  => $permission['group_name'],
                        'description' => $permission['description'],
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
            } else {
                $table->insert([
                    'group_name'      => $permission['group_name'],
                    'permission_name' => $permission['permission_name'],
                    'description'     => $permission['description'],
                    'created_at'      => date('Y-m-d H:i:s'),
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}