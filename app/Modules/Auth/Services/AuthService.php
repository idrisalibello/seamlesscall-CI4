<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\UserModel;
use App\Libraries\JWTHandler;
use CodeIgniter\I18n\Time;
use App\Modules\Auth\Models\ProviderModel;
use App\Modules\Auth\Models\OtpModel;

class AuthService
{
    protected $userModel;
    protected $jwt;
    protected $providerModel;
    protected $otpModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->jwt = new JWTHandler();
        $this->providerModel = new ProviderModel();
        $this->otpModel = new OtpModel();
    }

    public function register(array $data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        $id = $this->userModel->insert($data);
        $user = $this->userModel->find($id);

        $user = [
            'id'         => (int) $user['id'],
            'name'       => $user['name'],
            'email'      => $user['email'],
            'phone'      => $user['phone'],
            'role'       => $user['role'],
            'kyc_status' => $user['kyc_status'],
            'created_at' => $user['created_at'],
            'updated_at' => $user['updated_at'],
        ];

        $token = $this->jwt->generateToken([
            'id'   => $user['id'],
            'role' => $user['role'],
        ]);

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    public function createCustomer(array $data)
    {
        $data['role'] = 'customer';
        $data['status'] = 'active';
        return $this->saveUser($data);
    }

    /**
     * Create a pending provider (applies via app)
     */
    public function createPendingProvider(array $data)
    {
        $userData = [
            'name'        => $data['name'] ?? null,
            'email'       => $data['email'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'password'    => isset($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : null,
            'role'        => 'provider',
            'status'      => 'pending',
            'company_name' => $data['company_name'] ?? null,
            'location'    => $data['location'] ?? null,
            'is_company'  => $data['is_company'] ?? 0,
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        if (!$userData['name'] || !$userData['email'] || !$userData['phone']) {
            throw new \Exception('Name, email, and phone are required.');
        }

        if ($this->userModel->where('email', $userData['email'])->orWhere('phone', $userData['phone'])->first()) {
            throw new \Exception('Email or phone already exists.');
        }

        return $this->userModel->insert($userData);
    }



    /**
     * Approve a pending provider (admin action)
     */
    public function approveProvider(int $userId, int $adminId)
    {
        $user = $this->userModel->find($userId);

        if (!$user || $user['provider_status'] !== 'pending') {
            throw new \Exception('Invalid provider application');
        }

        return $this->userModel->update($userId, [
            'is_provider'     => 1,
            'provider_status' => 'approved',
            'approved_by'     => $adminId,
            'approved_at'     => Time::now(),
        ]);
    }


    /**
     * Create a provider directly from admin dashboard
     */
    public function createProvider(array $data, int $adminId)
    {
        $data['role'] = 'provider';
        $data['status'] = 'approved';
        $data['approved_by'] = $adminId;
        $data['approved_at'] = Time::now();
        return $this->saveUser($data);
    }

    /**
     * Create an admin (only callable by existing admin)
     */
    public function createAdmin(array $data, int $creatorAdminId)
    {
        $data['role'] = 'admin';
        $data['status'] = 'active';
        $data['created_by'] = $creatorAdminId;
        return $this->saveUser($data);
    }

    /**
     * Shared user creation logic
     */
    protected function saveUser(array $data)
    {
        // Ensure password is hashed
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Additional validations can go here (email uniqueness, required fields, etc.)
        return $this->userModel->insert($data);
    }

    public function applyAsProvider(int $userId, array $data = [])
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            throw new \Exception('User not found');
        }

        if ($user['role'] === 'admin') {
            throw new \Exception('Admins cannot apply as providers');
        }

        if (!empty($user['provider_status']) && $user['provider_status'] === 'pending') {
            throw new \Exception('Provider application already pending');
        }

        $updateData = [
            'is_provider'      => 0,
            'provider_status'  => 'pending',
            'company_name'     => $data['company_name'] ?? null,
            'location'         => $data['location'] ?? null,
            'is_company'       => $data['is_company'] ?? 0,
            'provider_applied_at' => Time::now(),
        ];

        return $this->userModel->update($userId, $updateData);
    }


    public function login(string $emailOrPhone, string $password)
    {
        $user = $this->userModel
            ->where('email', $emailOrPhone)
            ->orWhere('phone', $emailOrPhone)
            ->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        // cast to correct types
        $userResponse = [
            'id'    => (int) $user['id'],
            'name'  => (string) $user['name'],
            'email' => (string) $user['email'],
            'phone' => (string) $user['phone'],
            'role'  => (string) $user['role'],
        ];

        $token = $this->jwt->generateToken([
            'id'   => $userResponse['id'],
            'role' => $userResponse['role'],
        ]);

        return [
            'user'  => $userResponse,
            'token' => $token,
        ];
    }

    // AuthService.php
public function getUserByEmailOrPhone(string $emailOrPhone): ?array
{
    return $this->userModel
                ->where('email', $emailOrPhone)
                ->orWhere('phone', $emailOrPhone)
                ->first();
}


    public function sendEmailOtp(int $userId, string $purpose = 'login'): bool
    {
        $user = $this->userModel->find($userId);
        if (!$user || !$user['email']) {
            throw new \Exception('User not found or has no email');
        }

        // Invalidate previous OTPs
        $this->otpModel->invalidatePrevious($userId, $purpose);

        // Generate 6-digit OTP
        $otp = random_int(100000, 999999);
        $otpHash = password_hash((string)$otp, PASSWORD_DEFAULT);

        // Expire in 10 minutes
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Store OTP
        $this->otpModel->insert([
            'user_id'    => $userId,
            'purpose'    => $purpose,
            'channel'    => 'email',
            'otp_hash'   => $otpHash,
            'expires_at' => $expiresAt,
        ]);

        // Send Email via CI4 Email service
        $email = \Config\Services ::email();

        $email->setFrom(config('Email')->fromEmail, config('Email')->fromName);
        $email->setTo($user['email']);
        $email->setSubject('Your Seamless Call Verification Code');
        $email->setMessage("Your verification code is: $otp\n\nThis code expires in 10 minutes.");

        return $email->send();
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(int $userId, string $otp, string $purpose = 'login'): bool
    {
        $record = $this->otpModel->getValidOtp($userId, $purpose);

        if (!$record) {
            throw new \Exception('No valid OTP found or OTP expired');
        }

        if (!password_verify($otp, $record['otp_hash'])) {
            throw new \Exception('Invalid OTP');
        }

        // Mark OTP as used
        $this->otpModel->update($record['id'], [
            'used_at' => date('Y-m-d H:i:s')
        ]);

        return true;
    }
}
