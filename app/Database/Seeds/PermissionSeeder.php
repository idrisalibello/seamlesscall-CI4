<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [];

        foreach ($this->basePermissions() as $permission) {
            $permissions[$permission['permission_name']] = $permission;
        }

        foreach ($this->discoverAdminRoutePermissions() as $permission) {
            $permissions[$permission['permission_name']] = $permission;
        }

        $table = $this->db->table('permissions');

        foreach (array_values($permissions) as $permission) {
            $existing = $table
                ->where('permission_name', $permission['permission_name'])
                ->get()
                ->getRowArray();

            $payload = [
                'group_name'      => $permission['group_name'],
                'permission_name' => $permission['permission_name'],
                'description'     => $permission['description'],
                'updated_at'      => date('Y-m-d H:i:s'),
            ];

            if ($existing) {
                $table->where('id', $existing['id'])->update($payload);
            } else {
                $payload['created_at'] = date('Y-m-d H:i:s');
                $table->insert($payload);
            }
        }
    }

    private function basePermissions(): array
    {
        return [
            ['group_name' => 'Dashboard', 'permission_name' => 'view-dashboard', 'description' => 'Access admin dashboard'],

            ['group_name' => 'Reports', 'permission_name' => 'view-reports-overview', 'description' => 'View reports overview'],
            ['group_name' => 'Reports', 'permission_name' => 'view-operations-reports', 'description' => 'View operations reports'],
            ['group_name' => 'Reports', 'permission_name' => 'view-provider-reports', 'description' => 'View provider reports'],
            ['group_name' => 'Reports', 'permission_name' => 'view-customer-reports', 'description' => 'View customer reports'],
            ['group_name' => 'Reports', 'permission_name' => 'view-finance-reports', 'description' => 'View finance reports'],
            ['group_name' => 'Reports', 'permission_name' => 'view-promotion-reports', 'description' => 'View promotion reports'],
            ['group_name' => 'Reports', 'permission_name' => 'export-reports', 'description' => 'Export reports as CSV or print'],

            ['group_name' => 'System', 'permission_name' => 'manage-roles', 'description' => 'Manage roles and permissions'],
            ['group_name' => 'System', 'permission_name' => 'manage-integrations', 'description' => 'Manage integrations'],
            ['group_name' => 'System', 'permission_name' => 'manage-feature-toggles', 'description' => 'Manage feature toggles'],
            ['group_name' => 'System', 'permission_name' => 'manage-maintenance-mode', 'description' => 'Manage maintenance mode'],
        ];
    }

    private function discoverAdminRoutePermissions(): array
    {
        $routesPath = APPPATH . 'Modules/Admin/Config/Routes.php';
        if (!is_file($routesPath)) {
            return [];
        }

        $contents = file_get_contents($routesPath);
        if ($contents === false) {
            return [];
        }

        preg_match_all(
            '/\$routes->(?:get|post|put|patch|delete|match)\((?:\[[^\]]+\],\s*)?\'([^\']+)\'\s*,/i',
            $contents,
            $matches
        );

        $paths = array_unique($matches[1] ?? []);
        $permissions = [];

        foreach ($paths as $path) {
            $permission = $this->permissionForAdminPath($path);
            if ($permission !== null) {
                $permissions[$permission['permission_name']] = $permission;
            }
        }

        return array_values($permissions);
    }

    private function permissionForAdminPath(string $path): ?array
    {
        $normalized = trim($path);

        $map = [
            'provider-applications'              => ['People', 'manage-provider-applications', 'Review provider applications'],
            'customers'                          => ['People', 'view-customers', 'View customers'],
            'providers'                          => ['People', 'view-providers', 'View providers'],
            'users'                              => ['System', 'view-users', 'View list of admin users'],
            'users/(:num)'                       => ['System', 'edit-users', 'Edit admin user details'],
            'users/(:num)/roles'                 => ['System', 'assign-user-roles', 'Assign roles to admin users'],

            'verification-queue'                 => ['People', 'view-verification-queue', 'View verification queue'],
            'verification-queue/(:num)'          => ['People', 'view-verification-queue', 'View verification queue item'],
            'verification-queue/(:num)/approve'  => ['People', 'manage-verification-queue', 'Approve verification cases'],
            'verification-queue/(:num)/reject'   => ['People', 'manage-verification-queue', 'Reject verification cases'],
            'verification-queue/(:num)/escalate' => ['People', 'manage-verification-queue', 'Escalate verification cases'],

            'providers/performance'              => ['People', 'view-provider-performance', 'View provider performance'],
            'providers/(:num)/performance'       => ['People', 'view-provider-performance', 'View provider performance detail'],
            'providers/(:num)/ratings'           => ['People', 'view-provider-performance', 'View provider ratings'],
            'providers/(:num)/disputes'          => ['People', 'view-provider-performance', 'View provider disputes'],

            'finance/earnings'                   => ['Finance', 'view-earnings-overview', 'View earnings overview'],
            'finance/payouts'                    => ['Finance', 'view-payouts', 'View provider payouts'],
            'finance/payouts/summary'            => ['Finance', 'view-payouts', 'View provider payout summary'],
            'finance/payouts/(:num)/mark-paid'   => ['Finance', 'manage-payouts', 'Mark payouts as paid'],
            'finance/payouts/(:num)/mark-failed' => ['Finance', 'manage-payouts', 'Mark payouts as failed'],

            'finance/commission-config'          => ['Finance', 'manage-platform-commissions', 'Manage platform commission configuration'],
            'finance/commissions'                => ['Finance', 'view-platform-commissions', 'View platform commissions'],
            'finance/commissions/summary'        => ['Finance', 'view-platform-commissions', 'View platform commission summary'],
            'finance/commissions/(:num)/confirm' => ['Finance', 'manage-platform-commissions', 'Confirm commission'],

            'finance/refunds'                    => ['Finance', 'view-refunds-disputes', 'View refunds'],
            'finance/refunds/summary'            => ['Finance', 'view-refunds-disputes', 'View refund summary'],
            'finance/refunds/(:num)/status'      => ['Finance', 'manage-refunds-disputes', 'Manage refund statuses'],
            'finance/disputes'                   => ['Finance', 'view-refunds-disputes', 'View disputes'],
            'finance/disputes/summary'           => ['Finance', 'view-refunds-disputes', 'View dispute summary'],
            'finance/disputes/(:num)/status'     => ['Finance', 'manage-refunds-disputes', 'Manage dispute statuses'],
            'refunds/(:num)/status'              => ['Finance', 'manage-refunds-disputes', 'Manage refund statuses'],

            'finance/ledger'                     => ['Finance', 'view-ledger', 'View financial ledger'],
            'finance/ledger/summary'             => ['Finance', 'view-ledger', 'View financial ledger summary'],

            'pricing-rules'                      => ['Configuration', 'manage-pricing', 'Manage pricing rules'],
            'pricing-rules/summary'              => ['Configuration', 'manage-pricing', 'View pricing rules summary'],
            'pricing-rules/(:num)'               => ['Configuration', 'manage-pricing', 'View pricing rule details'],
            'pricing-rules/(:num)/status'        => ['Configuration', 'manage-pricing', 'Update pricing rule status'],
            'pricing/profiles'                   => ['Configuration', 'manage-pricing', 'Manage pricing profiles'],
            'pricing/summary'                    => ['Configuration', 'manage-pricing', 'View pricing summary'],
            'pricing/profiles/(:num)'            => ['Configuration', 'manage-pricing', 'View pricing profile details'],
            'pricing/profiles/(:num)/adjustments' => ['Configuration', 'manage-pricing', 'Manage pricing adjustments'],
            'pricing/adjustments/(:num)'         => ['Configuration', 'manage-pricing', 'Update pricing adjustments'],
            'pricing/adjustments/(:num)/status'  => ['Configuration', 'manage-pricing', 'Update pricing adjustment status'],

            'coverage'                           => ['Configuration', 'manage-coverage', 'Manage coverage'],
            'coverage/(:num)'                    => ['Configuration', 'manage-coverage', 'View coverage details'],
            'coverages'                          => ['Configuration', 'manage-coverage', 'Manage coverages'],
            'coverages/(:num)'                   => ['Configuration', 'manage-coverage', 'Update coverage'],

            'coverage-rules'                     => ['Configuration', 'manage-availability', 'Manage availability rules'],
            'coverage-rules/(:num)'              => ['Configuration', 'manage-availability', 'View availability rule details'],
            'coverage-rules/(:num)/status'       => ['Configuration', 'manage-availability', 'Update availability rule status'],

            'promotions'                         => ['Configuration', 'manage-promotions', 'Manage promotions'],
            'promotions/(:num)'                  => ['Configuration', 'manage-promotions', 'View promotion details'],
            'promotions/(:num)/status'           => ['Configuration', 'manage-promotions', 'Update promotion status'],

            'categories/(:num)/services'         => ['Configuration', 'manage-categories', 'Manage category services'],
            'services/(:num)'                    => ['Configuration', 'manage-categories', 'Manage services'],

            'reports/overview'                   => ['Reports', 'view-reports-overview', 'View reports overview'],
            'reports/operations'                 => ['Reports', 'view-operations-reports', 'View operations reports'],
            'reports/providers'                  => ['Reports', 'view-provider-reports', 'View provider reports'],
            'reports/customers'                  => ['Reports', 'view-customer-reports', 'View customer reports'],
            'reports/finance'                    => ['Reports', 'view-finance-reports', 'View finance reports'],
            'reports/promotions'                 => ['Reports', 'view-promotion-reports', 'View promotion reports'],
        ];

        if (!isset($map[$normalized])) {
            return null;
        }

        [$group, $permissionName, $description] = $map[$normalized];

        return [
            'group_name'      => $group,
            'permission_name' => $permissionName,
            'description'     => $description,
        ];
    }
}