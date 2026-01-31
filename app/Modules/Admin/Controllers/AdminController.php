<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\LedgerModel;
use App\Models\RefundModel;
use App\Models\ActivityLogModel;
use App\Models\EarningsModel;
use App\Models\PayoutsModel;
use App\Modules\System\Models\RoleModel; // NEW
use App\Modules\System\Models\UserRoleModel; // NEW
use CodeIgniter\API\ResponseTrait;
use Exception;

class AdminController extends BaseController
{
    use ResponseTrait;

    /**
     * A helper function to ensure IDs are integers in the output array.
     */
    private function formatOutput(array $data): array
    {
        return array_map(function ($row) {
            if (is_object($row)) { // Handle CodeIgniter's default object return
                if (isset($row->id)) {
                    $row->id = (int)$row->id;
                }
                if (isset($row->user_id)) {
                    $row->user_id = (int)$row->user_id;
                }
                if (isset($row->role_id)) {
                    $row->role_id = (int)$row->role_id;
                }
                if (isset($row->permission_id)) {
                    $row->permission_id = (int)$row->permission_id;
                }
                return (array)$row; // Convert object to array for consistent output
            } else { // Already an array
                if (isset($row['id'])) {
                    $row['id'] = (int)$row['id'];
                }
                if (isset($row['user_id'])) {
                    $row['user_id'] = (int)$row['user_id'];
                }
                if (isset($row['role_id'])) {
                    $row['role_id'] = (int)$row['role_id'];
                }
                if (isset($row['permission_id'])) {
                    $row['permission_id'] = (int)$row['permission_id'];
                }
                return $row;
            }
        }, $data);
    }

    public function getProviderApplications()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'AdminController: User payload for getProviderApplications: ' . json_encode($user));

        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $userModel = new UserModel();
        $applications = $userModel->where('provider_status', 'pending')->findAll();

        return $this->respond(['data' => $this->formatOutput($applications)]);
    }

    /**
     * Approve or reject a provider application.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function approveOrRejectProvider()
    {
        $rules = [
            'user_id' => 'required|integer',
            'action' => 'required|in_list[approve,reject]',
        ];

        if (!$this->validate($rules)) {
            log_message('debug', 'Validation failed: ' . json_encode($this->validator->getErrors()));
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $adminUser = service('request')->auth_payload;
        log_message('debug', 'AdminController: Admin user payload for approveOrRejectProvider: ' . json_encode($adminUser));

        if (!$adminUser || !isset($adminUser->id) || !is_numeric($adminUser->id)) {
            log_message('error', 'Admin ID not found or invalid in JWT payload for approval/rejection.');
            return $this->failUnauthorized('Authenticated admin ID is required to perform this action.');
        }
        $adminId = (int) $adminUser->id;
        log_message('debug', 'Resolved Admin ID: ' . $adminId);

        $userId = $this->request->getVar('user_id');
        $action = $this->request->getVar('action');
        
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            log_message('debug', 'User not found for ID: ' . $userId);
            return $this->failNotFound('User not found.');
        }

        try {
            $data = [];
            if ($action === 'approve') {
                $data['provider_status'] = 'approved';
                $data['is_provider'] = 1;
                $data['role'] = 'Provider';
            } else {
                $data['provider_status'] = 'rejected';
                $data['is_provider'] = 0;
            }

            $data['approved_by'] = $adminId;
            $data['approved_at'] = date('Y-m-d H:i:s');
            log_message('debug', 'Update payload for user ' . $userId . ': ' . json_encode($data));

            $updateResult = $userModel->update($userId, $data);
            log_message('debug', 'UserModel update result for user ' . $userId . ': ' . ($updateResult ? 'Success' : 'Failure'));

            if (!$updateResult) {
                 $errors = $userModel->errors();
                 log_message('error', 'Provider approval failed for user ID: ' . $userId . ' - Errors: ' . print_r($errors, true));
                return $this->failServerError('Failed to update provider status.');
            }

            return $this->respondUpdated(['message' => 'Provider application has been successfully ' . $action . 'd.']);

        } catch (Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    /**
     * Create a new admin user.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function createAdmin()
    {
        $adminUser = $this->request->auth_payload ?? null;
        log_message('debug', 'AdminController: Admin user payload for createAdmin: ' . json_encode($adminUser));

        if (!$adminUser || !isset($adminUser->role) || $adminUser->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Only Admin users can create new admin users.');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'phone' => 'required|min_length[10]|max_length[15]',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            log_message('debug', 'Admin creation validation failed: ' . json_encode($this->validator->getErrors()));
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $userModel = new UserModel();

        try {
            $data = [
                'name' => $this->request->getJsonVar('name'),
                'email' => $this->request->getJsonVar('email'),
                'phone' => $this->request->getJsonVar('phone'),
                'password' => password_hash($this->request->getJsonVar('password'), PASSWORD_DEFAULT),
                'role' => 'Admin',
            ];

            if (!$userModel->insert($data)) {
                $errors = $userModel->errors();
                log_message('error', 'Failed to create admin user: ' . print_r($errors, true));
                return $this->failServerError('Failed to create admin user.');
            }

            return $this->respondCreated(['message' => 'Admin user created successfully.']);

        } catch (Exception $e) {
            log_message('error', '[ERROR] createAdmin: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    public function getCustomers()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'AdminController: User payload for getCustomers: ' . json_encode($user));

        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $userModel = new UserModel();
        $customers = $userModel->where('role', 'Customer')->findAll();

        return $this->respond(['data' => $this->formatOutput($customers)]);
    }

    public function getUserDetails($userId)
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'AdminController: User payload for getUserDetails: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $userModel = new UserModel();
        $customer = $userModel->find($userId);

        if (!$customer) {
            return $this->failNotFound('User not found');
        }

        // CodeIgniter's Model::find() returns objects by default.
        return $this->respond(['data' => $this->formatOutput([$customer])[0]]);
    }

    public function getUserLedger($userId)
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'AdminController: User payload for getUserLedger: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $ledgerModel = new LedgerModel();
        $ledgerEntries = $ledgerModel->where('user_id', $userId)->findAll();

        return $this->respond(['data' => $this->formatOutput($ledgerEntries)]);
    }

    public function getUserRefunds($userId)
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'AdminController: User payload for getUserRefunds: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $refundModel = new RefundModel();
        $refunds = $refundModel->where('user_id', $userId)->findAll();

        return $this->respond(['data' => $this->formatOutput($refunds)]);
    }

    public function getUserActivityLog($userId)
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'AdminController: User payload for getUserActivityLog: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $activityLogModel = new ActivityLogModel();
        $activityLogs = $activityLogModel->where('user_id', $userId)->findAll();

        return $this->respond(['data' => $this->formatOutput($activityLogs)]);
    }

    public function getProviders()
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'AdminController: User payload for getProviders: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $userModel = new UserModel();
        $providers = $userModel->where('role', 'Provider')->findAll();

        return $this->respond(['data' => $this->formatOutput($providers)]);
    }

    public function getProviderEarnings($providerId)
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'AdminController: User payload for getProviderEarnings: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $earningsModel = new EarningsModel();
        $earnings = $earningsModel->where('provider_id', $providerId)->findAll();

        return $this->respond(['data' => $this->formatOutput($earnings)]);
    }

    public function getProviderPayouts($providerId)
    {
        $user = service('request')->auth_payload;
        log_message('debug', 'AdminController: User payload for getProviderPayouts: ' . json_encode($user));
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $payoutsModel = new PayoutsModel();
        $payouts = $payoutsModel->where('provider_id', $providerId)->findAll();

        return $this->respond(['data' => $this->formatOutput($payouts)]);
    }
public function getEarningsOverview()
{
    $user = service('request')->auth_payload;
    log_message('debug', 'AdminController: User payload for getEarningsOverview: ' . json_encode($user));
    if (!$user || !isset($user->role) || $user->role !== 'Admin') {
        return $this->failUnauthorized('Access denied. Admins only.');
    }

    $from = $this->request->getGet('from_date'); // YYYY-MM-DD
    $to   = $this->request->getGet('to_date');   // YYYY-MM-DD

    $page = max(1, (int) ($this->request->getGet('page') ?? 1));
    $pageSize = (int) ($this->request->getGet('page_size') ?? 20);
    if (!in_array($pageSize, [20, 50, 100], true)) {
        $pageSize = 20;
    }
    $offset = ($page - 1) * $pageSize;

    // Range is mandatory (UI rule)
    if (!$from || !$to) {
        return $this->failValidationErrors('from_date and to_date are required.');
    }

    $db = db_connect();

    // =========================
    // Lifetime totals (all time)
    // =========================
    $life = $db->table('earnings e')
        ->select('
            COALESCE(SUM(e.amount), 0) as total,
            COALESCE(SUM(e.amount), 0) as completed,
            0 as pending
        ')
        ->get()
        ->getRowArray();

    $lifetimeTotals = [
        'total'     => (float) ($life['total'] ?? 0),
        'completed' => (float) ($life['completed'] ?? 0),
        'pending'   => (float) ($life['pending'] ?? 0),
    ];

    // =========================
    // Range totals (selected range)
    // =========================
    $range = $db->table('earnings e')
        ->select('
            COALESCE(SUM(e.amount), 0) as total,
            COALESCE(SUM(e.amount), 0) as completed,
            0 as pending
        ')
        ->where('DATE(e.created_at) >=', $from)
        ->where('DATE(e.created_at) <=', $to)
        ->get()
        ->getRowArray();

    $rangeTotals = [
        'total'     => (float) ($range['total'] ?? 0),
        'completed' => (float) ($range['completed'] ?? 0),
        'pending'   => (float) ($range['pending'] ?? 0),
    ];

    // =========================
    // Trend (daily if <= 31 days, else monthly)
    // =========================
    $days = (strtotime($to) - strtotime($from)) / 86400;
    $groupExpr = ($days <= 31)
        ? 'DATE(e.created_at)'
        : 'DATE_FORMAT(e.created_at, "%Y-%m")';

    $trendRows = $db->table('earnings e')
        ->select("$groupExpr as label, COALESCE(SUM(e.amount), 0) as total")
        ->where('DATE(e.created_at) >=', $from)
        ->where('DATE(e.created_at) <=', $to)
        ->groupBy('label')
        ->orderBy('label', 'ASC')
        ->get()
        ->getResultArray();

    $trend = array_map(function ($r) {
        return [
            'label' => $r['label'],
            'total' => (float) $r['total'],
        ];
    }, $trendRows);

    // =========================
    // Rows (paginated)
    // =========================
    $rowsBase = $db->table('earnings e')
        ->select('
            e.id,
            e.provider_id,
            u.name as provider,
            e.amount,
            e.description,
            e.job_id,
            e.created_at
        ')
        ->join('users u', 'u.id = e.provider_id', 'left')
        ->where('DATE(e.created_at) >=', $from)
        ->where('DATE(e.created_at) <=', $to);

    // count (clone to avoid builder mutation issues)
    $totalRows = (clone $rowsBase)->countAllResults();

    $rows = (clone $rowsBase)
        ->orderBy('e.created_at', 'DESC')
        ->limit($pageSize, $offset)
        ->get()
        ->getResultArray();

    // Normalize to match Flutter table expectations
    $normalized = array_map(function ($r) {
        return [
            'id'        => (int) $r['id'],
            'date'      => $r['created_at'],
            'provider'  => $r['provider'] ?? 'Unknown',
            'amount'    => (float) $r['amount'],
            'status'    => 'Completed',
            'reference' => 'EARN-' . $r['id'],

            'provider_id' => (int) $r['provider_id'],
            'job_id'      => $r['job_id'] !== null ? (int) $r['job_id'] : null,
            'description' => $r['description'],
            'created_at'  => $r['created_at'],
        ];
    }, $rows);

    $totalPages = max(1, (int) ceil($totalRows / $pageSize));

    return $this->respond([
        'data' => [
            'lifetimeTotals' => $lifetimeTotals,
            'rangeTotals'    => $rangeTotals,
            'trend'          => $trend,
            'rows'           => $normalized,
            'pagination'     => [
                'page'        => $page,
                'page_size'   => $pageSize,
                'total_rows'  => (int) $totalRows,
                'total_pages' => $totalPages,
            ],
        ],
    ]);
}




    // NEW ENDPOINTS BELOW (User Management for Roles & Permissions)

    /**
     * Get all users.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getUsers()
    {
        $adminUser = service('request')->auth_payload;
        if (!$adminUser || !isset($adminUser->role) || $adminUser->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $userModel = new UserModel();
        $users = $userModel->asArray()->findAll(); // Get all users, not just customers/providers

        return $this->respond(['data' => $this->formatOutput($users)]);
    }

    /**
     * Update user details.
     *
     * @param int $userId
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function updateUser(int $userId)
    {
        $adminUser = service('request')->auth_payload;
        if (!$adminUser || !isset($adminUser->role) || $adminUser->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $userModel = new UserModel();
        $userToUpdate = $userModel->find($userId);

        if (!$userToUpdate) {
            return $this->failNotFound('User not found.');
        }

        $rules = [
            'name' => 'permit_empty|min_length[3]|max_length[255]',
            'phone' => 'permit_empty|min_length[10]|max_length[15]',
            'status' => 'permit_empty|in_list[pending,active,suspended]',
            'role' => 'permit_empty|in_list[Admin,Provider,Customer]', // Allow updating the original role
            'kyc_status' => 'permit_empty|in_list[Pending,Verified,Rejected]',
            'is_provider' => 'permit_empty|in_list[0,1]',
            'provider_status' => 'permit_empty|in_list[pending,approved,rejected]',
            // Password change should be a separate endpoint for security
            // Email change should be handled carefully due to uniqueness
        ];

        $input = $this->request->getJSON(true); // Get as associative array
        $allowedFields = ['name', 'phone', 'status', 'role', 'kyc_status', 'is_provider', 'provider_status'];
        $updateData = array_intersect_key($input, array_flip($allowedFields));

        // Skip validation for fields not present in the input
        $validationRules = [];
        foreach ($updateData as $field => $value) {
            if (isset($rules[$field])) {
                $validationRules[$field] = $rules[$field];
            }
        }

        if (!empty($validationRules) && !$this->validate($validationRules)) {
            return $this->failValidationErrors("errors");
        }

        try {
            if ($userModel->update($userId, $updateData)) {
                return $this->respondUpdated(['message' => 'User updated successfully.']);
            } else {
                $errors = $userModel->errors();
                if (!empty($errors)) {
                    return $this->failValidationErrors($errors);
                }
                return $this->failServerError('Failed to update user.');
            }
        } catch (Exception $e) {
            log_message('error', '[ERROR] updateUser: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            return $this->failServerError('An unexpected error occurred.');
        }
    }

    /**
     * Get roles assigned to a specific user.
     *
     * @param int $userId
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getUserRoles(int $userId)
    {
        $adminUser = service('request')->auth_payload;
        if (!$adminUser || !isset($adminUser->role) || $adminUser->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $userModel = new UserModel();
        if (!$userModel->find($userId)) {
            return $this->failNotFound('User not found.');
        }

        $userRoleModel = new UserRoleModel();
        $userRoles = $userRoleModel->getUserRoles($userId);

        return $this->respond(['data' => $this->formatOutput($userRoles)]);
    }

    /**
     * Update roles assigned to a specific user.
     *
     * @param int $userId
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function updateUserRoles(int $userId)
    {
        $adminUser = service('request')->auth_payload;
        if (!$adminUser || !isset($adminUser->role) || $adminUser->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $userModel = new UserModel();
        if (!$userModel->find($userId)) {
            return $this->failNotFound('User not found.');
        }

        $input = $this->request->getJSON(true); // Get as associative array
        $roleIds = $input['role_ids'] ?? [];

        // Validate $roleIds to ensure they are integers
        if (!is_array($roleIds) || !array_walk($roleIds, 'is_int')) {
            return $this->failValidationErrors(['role_ids' => 'Role IDs must be an array of integers.']);
        }

        $userRoleModel = new UserRoleModel();
        if ($userRoleModel->updateUserRoles($userId, $roleIds)) {
            return $this->respondUpdated(['message' => 'User roles updated successfully.']);
        }

        return $this->failServerError('Failed to update user roles.');
    }
}
