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
        if (!$user || $user->role !== 'Admin') {
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
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $userId = $this->request->getVar('user_id');
        $action = $this->request->getVar('action');
        $adminId = $this->request->user->id; // Assuming admin user is authenticated and available in the request

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
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

            if (!$userModel->update($userId, $data)) {
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
}
