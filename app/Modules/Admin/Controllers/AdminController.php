<?php

namespace App\Modules\Admin\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use Exception;

class AdminController extends BaseController
{
    use ResponseTrait;

    public function getProviderApplications()
    {
        $user = $this->request->user;
        if (!$user || !isset($user->role) || $user->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Admins only.');
        }

        $userModel = new UserModel();
        $applications = $userModel->where('provider_status', 'pending')->findAll();

        return $this->respond(['data' => $applications]);
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

        // Harden admin ID extraction (Task 2)
        $adminUser = $this->request->user;
        if (!isset($adminUser) || !isset($adminUser->id) || !is_numeric($adminUser->id)) {
            log_message('error', 'Admin ID not found or invalid in JWT payload for approval/rejection.');
            return $this->failUnauthorized('Authenticated admin ID is required to perform this action.');
        }
        $adminId = (int) $adminUser->id;
        log_message('debug', 'Resolved Admin ID: ' . $adminId); // Temporary logging (Task 4)

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
                $data['role'] = 'Provider'; // Set role to Provider on approval
            } else {
                $data['provider_status'] = 'rejected';
                $data['is_provider'] = 0;
                // If rejected, the role should remain as it was or be explicitly set to 'Customer' if that's the default.
                // For now, not changing role on rejection assuming it was 'Customer' or 'Pending Provider'.
            }

            $data['approved_by'] = $adminId;
            $data['approved_at'] = date('Y-m-d H:i:s');
            log_message('debug', 'Update payload for user ' . $userId . ': ' . json_encode($data)); // Temporary logging (Task 4)


            $updateResult = $userModel->update($userId, $data);
            log_message('debug', 'UserModel update result for user ' . $userId . ': ' . ($updateResult ? 'Success' : 'Failure')); // Temporary logging (Task 4)

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
        // Ensure only Admin users can access this
        $adminUser = $this->request->user;
        if (!$adminUser || !isset($adminUser->role) || $adminUser->role !== 'Admin') {
            return $this->failUnauthorized('Access denied. Only Admin users can create new admin users.');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'phone' => 'required|min_length[10]|max_length[15]', // Basic phone validation
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
                'role' => 'Admin', // Fixed role for admin creation
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
}
