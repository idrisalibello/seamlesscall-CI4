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
        $payload = $this->request->auth_payload ?? null;

        if (!$payload || !isset($payload['id'])) {
            return $this->failUnauthorized('Authentication required');
        }

        $userId = (int) $payload['id'];
        $role   = $payload['role'] ?? null;


        $rules = [
            'company_name' => 'permit_empty|min_length[2]',
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
        $payload = $this->request->auth_payload ?? null;

        if (!$payload || !isset($payload['id'])) {
            return $this->failUnauthorized('Authentication required');
        }

        $adminId = (int) $payload['id'];

        // Optional but recommended: enforce Admin role here
        if (($payload['role'] ?? null) !== 'Admin') {
            return $this->failForbidden('Admin access required');
        }


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
        $payload = $this->request->auth_payload ?? null;

        if (!$payload || !isset($payload['id'])) {
            return $this->failUnauthorized('Authentication required');
        }

        $adminId = (int) $payload['id'];

        // Optional but recommended: enforce Admin role here
        if (($payload['role'] ?? null) !== 'Admin') {
            return $this->failForbidden('Admin access required');
        }
        $payload = $this->request->auth_payload ?? null;

        if (!$payload || !isset($payload['id'])) {
            return $this->failUnauthorized('Authentication required');
        }

        $adminId = (int) $payload['id'];

        // Optional but recommended: enforce Admin role here
        if (($payload['role'] ?? null) !== 'Admin') {
            return $this->failForbidden('Admin access required');
        }

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
        $payload = $this->request->auth_payload ?? null;

        if (!$payload || !isset($payload['id'])) {
            return $this->failUnauthorized('Authentication required');
        }

        $adminId = (int) $payload['id'];

        // Optional but recommended: enforce Admin role here
        if (($payload['role'] ?? null) !== 'Admin') {
            return $this->failForbidden('Admin access required');
        }

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

        if (!$this->validate($rules)) {
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

    /**
     * Handles a request to send a login OTP to a user's email or phone.
     */
    public function requestLoginOtp()
    {
        $data = $this->request->getJSON(true);
        $identifier = $data['identifier'] ?? null;

        if (empty($identifier)) {
            return $this->failValidationErrors(['identifier' => 'Email or phone number is required']);
        }

        try {
            $channels = $this->authService->sendLoginOtpToAllChannels($identifier);
            return $this->respond([
                'status' => 'success',
                'message' => 'OTP sent successfully',
                'channels' => $channels
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }


    /**
     * Handles login by verifying an OTP and issuing a JWT.
     */
    public function loginWithOtp()
    {
        $data = $this->request->getJSON(true);
        $identifier = $data['identifier'] ?? null;
        $otp = $data['otp'] ?? null;

        if (empty($identifier) || empty($otp)) {
            return $this->failValidationErrors([
                'identifier' => 'Email or phone number is required',
                'otp'        => 'OTP is required'
            ]);
        }

        try {
            $token = $this->authService->loginWithOtp($identifier, $otp);

            if ($token) {
                return $this->respond(['status' => 'success', 'token' => $token]);
            }

            return $this->failUnauthorized('Login failed. The OTP may be invalid or expired.');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
    public function loginWithGoogle()
    {
        $data = $this->request->getJSON(true);

        try {
            $result = $this->authService->loginWithGoogle($data);
            return $this->respond(['status' => 'success', 'data' => $result]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}
