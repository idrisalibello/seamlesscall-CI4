<?php

namespace App\Modules\Auth\Controllers;

use App\Controllers\BaseController;
use App\Modules\Auth\Services\AuthService;
use CodeIgniter\API\ResponseTrait;

class AuthController extends BaseController
{
    use ResponseTrait;

    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Customer / Admin registration
     */
    public function register()
    {
        $data = $this->request->getJSON(true);

        if (!is_array($data)) {
            return $this->fail('Invalid JSON payload', 400);
        }

        $rules = [
            'name'     => 'required|min_length[3]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'phone'    => 'required|is_unique[users.phone]',
            'password' => 'required|min_length[8]',
            'role'     => 'permit_empty|in_list[Admin,Provider,Customer]'
        ];

        if (!$this->validateData($data, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Default role
        $data['role'] ??= 'Customer';

        $result = $this->authService->register($data);

        return $this->respondCreated(['data' => $result]);
    }

    /**
     * LEGACY: Provider applies by creating a new account
     * Kept for backward compatibility
     */
    public function applyProvider()
    {
        $rules = [
            'name'     => 'required',
            'email'    => 'required|valid_email',
            'phone'    => 'required',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);

        try {
            $this->authService->createPendingProvider($data);
            return $this->respond([
                'status'  => 'success',
                'message' => 'Application submitted successfully. Await admin approval.'
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * NEW: Logged-in customer applies to become a provider
     */
    public function applyAsProvider()
    {
        $userId = session('user_id');

        if (!$userId) {
            return $this->failUnauthorized('Authentication required');
        }

        $rules = [
            'company_name' => 'required|min_length[2]',
            'location'     => 'required|min_length[2]',
            'services'     => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);

        try {
            $this->authService->applyAsProvider((int) $userId, $data);

            return $this->respond([
                'status'  => 'success',
                'message' => 'Provider application submitted. Await admin approval.'
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }


    /**
     * Admin approves provider
     */
    public function approveProvider($providerId)
    {
        $adminId = session('user_id');

        try {
            $this->authService->approveProvider((int) $providerId, (int) $adminId);
            return $this->respond([
                'status'  => 'success',
                'message' => 'Provider approved successfully.'
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Admin creates provider directly
     */
    public function createProvider()
    {
        $data = $this->request->getPost();
        $adminId = session('user_id');

        try {
            $this->authService->createProvider($data, $adminId);
            return $this->respond([
                'status'  => 'success',
                'message' => 'Provider created successfully.'
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Admin creates another admin
     */
    public function createAdmin()
    {
        $data = $this->request->getPost();
        $adminId = session('user_id');

        try {
            $this->authService->createAdmin($data, $adminId);
            return $this->respond([
                'status'  => 'success',
                'message' => 'Admin created successfully.'
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Login
     */
    public function login()
    {
        $data = $this->request->getJSON(true);

        $rules = [
            'email_or_phone' => 'required',
            'password'       => 'required',
        ];

        if (!$this->validate($rules, $data)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $result = $this->authService->login(
            $data['email_or_phone'],
            $data['password']
        );

        if (!$result) {
            return $this->failUnauthorized('Invalid credentials');
        }

        return $this->respond([
            'data' => [
                'user'  => $result['user'],
                'token' => $result['token'],
            ],
        ]);
    }

    // AuthController.php
    public function requestEmailOtp()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['email_or_phone'])) {
            return $this->failValidationErrors(['email_or_phone' => 'Email or phone is required']);
        }

        $user = $this->authService->getUserByEmailOrPhone($data['email_or_phone']);
        if (!$user) {
            return $this->failNotFound('User not found');
        }

        try {
            $this->authService->sendEmailOtp((int)$user['id']);
            return $this->respond(['status' => 'success', 'message' => 'OTP sent to email']);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function verifyOtp()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['email_or_phone']) || empty($data['otp'])) {
            return $this->failValidationErrors([
                'email_or_phone' => 'Email or phone required',
                'otp'            => 'OTP required'
            ]);
        }

        $user = $this->authService->getUserByEmailOrPhone($data['email_or_phone']);
        if (!$user) {
            return $this->failNotFound('User not found');
        }

        try {
            $this->authService->verifyOtp((int)$user['id'], $data['otp']);
            return $this->respond(['status' => 'success', 'message' => 'OTP verified successfully']);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}
