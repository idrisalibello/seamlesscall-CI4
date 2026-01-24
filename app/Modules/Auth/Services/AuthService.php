<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\UserModel;
use App\Libraries\JWTHandler;
use App\Libraries\WhatsAppService;
use CodeIgniter\I18n\Time;
use App\Modules\Auth\Models\ProviderModel;
use App\Modules\Auth\Models\OtpModel;
use Exception;

class AuthService
{
    protected UserModel $userModel;
    protected JWTHandler $jwt;
    protected ProviderModel $providerModel;
    protected OtpModel $otpModel;
    protected WhatsAppService $whatsAppService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->jwt = new JWTHandler();
        $this->providerModel = new ProviderModel();
        $this->otpModel = new OtpModel();
        $this->whatsAppService = new WhatsAppService();
    }

    public function register(array $data)
    {
        // Normalize phone to E.164
        if (isset($data['phone'])) {
            $data['phone'] = $this->normalizePhone($data['phone']);
        }

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

    public function login(string $emailOrPhone, string $password)
    {
        // Normalize phone if input is numeric
        $emailOrPhone = $this->normalizeIdentifier($emailOrPhone);

        $user = $this->getUserByEmailOrPhone($emailOrPhone);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

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

    public function getUserByEmailOrPhone(string $emailOrPhone): ?array
    {
        return $this->userModel
            ->groupStart()
            ->where('email', $emailOrPhone)
            ->orWhere('phone', $emailOrPhone)
            ->groupEnd()
            ->first();
    }

    public function requestLoginOtp(string $identifier): bool
    {
        $identifier = $this->normalizeIdentifier($identifier);

        $user = $this->getUserByEmailOrPhone($identifier);
        if (!$user) {
            throw new Exception('User not found');
        }

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $this->_sendEmailOtp((int)$user['id'], 'login');
        }

        return $this->_sendWhatsAppOtp((int)$user['id'], 'login');
    }

    public function sendLoginOtpToAllChannels(string $identifier): array
{
    $identifier = $this->normalizeIdentifier($identifier);
    $user = $this->getUserByEmailOrPhone($identifier);

    if (!$user) {
        throw new \Exception('User not found');
    }

    $otp = random_int(100000, 999999);
    $otpString = (string) $otp;

    // Store OTP for both channels
    $this->_storeOtp((int)$user['id'], 'login', 'email', $otpString);
    $this->_storeOtp((int)$user['id'], 'login', 'whatsapp', $otpString);

    $channels = [];

    // Send Email
    if (!empty($user['email'])) {
        log_message('debug', '[OTP][EMAIL] Attempting email OTP send. user_id={id} to={to}', [
            'id' => $user['id'] ?? null,
            'to' => $user['email'],
        ]);

        $email = \Config\Services::email();
        $email->setFrom(config('Email')->fromEmail, config('Email')->fromName);
        $email->setTo($user['email']);
        $email->setSubject('Your Seamless Call Verification Code');
        $email->setMessage("Your verification code is: $otpString. This code expires in 5 minutes.");

        try {
            $sent = $email->send();
            log_message('debug', '[OTP][EMAIL] CI Email send() returned: {sent}', [
                'sent' => $sent ? 'true' : 'false',
            ]);

            if (!$sent) {
                $debug = $email->printDebugger(['headers', 'subject', 'body']);
                log_message('error', '[OTP][EMAIL] CI Email debugger output: {debug}', [
                    'debug' => $debug,
                ]);
            }

            if ($sent) {
                $channels[] = 'email';
            }
        } catch (\Throwable $e) {
            log_message('error', '[OTP][EMAIL] Exception during email send: {msg} in {file}:{line}', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    // Send WhatsApp
    if (!empty($user['phone'])) {
        $phone = $this->normalizePhone($user['phone']);
        if ($this->whatsAppService->sendOtp($phone, $otpString)) {
            $channels[] = 'whatsapp';
        }
    }

    if (empty($channels)) {
        throw new \Exception('Failed to send OTP to any channel');
    }

    return $channels;
}

    // --- Provider/Admin methods remain unchanged ---
    public function createProvider(array $data, int $adminId)
    {
        $data['role'] = 'provider';
        $data['status'] = 'approved';
        $data['approved_by'] = $adminId;
        $data['approved_at'] = Time::now();
        return $this->saveUser($data);
    }

    public function createAdmin(array $data, int $creatorAdminId)
    {
        $data['role'] = 'admin';
        $data['status'] = 'active';
        $data['created_by'] = $creatorAdminId;
        return $this->saveUser($data);
    }

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

    protected function saveUser(array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $this->userModel->insert($data);
    }

    public function applyAsProvider(int $userId, array $data = [])
    {
        $user = $this->userModel->find($userId);

        if (!$user) throw new \Exception('User not found');
        if ($user['role'] === 'admin') throw new \Exception('Admins cannot apply as providers');
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

    public function loginWithOtp(string $identifier, string $otp): ?string
    {
        $identifier = $this->normalizeIdentifier($identifier);

        $user = $this->getUserByEmailOrPhone($identifier);
        if (!$user) throw new Exception('User not found');

        $otpVerified = $this->verifyOtp((int)$user['id'], $otp, 'login');

        if ($otpVerified) {
            $userResponse = [
                'id'    => (int) $user['id'],
                'name'  => (string) $user['name'],
                'email' => (string) $user['email'],
                'phone' => (string) $user['phone'],
                'role'  => (string) $user['role'],
            ];
            return $this->jwt->generateToken([
                'id'   => $userResponse['id'],
                'role' => $userResponse['role'],
            ]);
        }

        return null;
    }

    public function verifyOtp(int $userId, string $otp, string $purpose = 'login'): bool
    {
        $record = $this->otpModel->getValidOtp($userId, $purpose);
        if (!$record) throw new Exception('No valid OTP found or OTP has expired');
        if (!password_verify($otp, $record['otp_hash'])) throw new Exception('Invalid OTP provided');

        $this->otpModel->update($record['id'], ['used_at' => date('Y-m-d H:i:s')]);
        return true;
    }

    private function _sendEmailOtp(int $userId, string $purpose): bool
    {
        $user = $this->userModel->find($userId);
        if (!$user || !$user['email']) throw new Exception('User not found or has no email');

        $otp = random_int(100000, 999999);
        $this->_storeOtp($userId, $purpose, 'email', (string)$otp);

        $email = \Config\Services::email();
        $email->setFrom(config('Email')->fromEmail, config('Email')->fromName);
        $email->setTo($user['email']);
        $email->setSubject('Your Seamless Call Verification Code');
        $email->setMessage("Your verification code is: $otp. This code expires in 5 minutes.");

        return $email->send();
    }

    /**
     * Login or register a user via Google OAuth
     *
     * @param array $googleData ['id' => google_user_id, 'email' => email, 'name' => full name]
     * @return array ['user' => [...], 'token' => string]
     * @throws \Exception
     */
    // public function loginWithGoogle(array $googleData): array
    // {
    //     if (empty($googleData['email']) || empty($googleData['id'])) {
    //         throw new \Exception('Google login requires email and Google ID');
    //     }

    //     // Check if user already exists by email
    //     $user = $this->userModel->where('email', $googleData['email'])->first();

    //     if (!$user) {
    //         // New user: create account without password
    //         $userData = [
    //             'name'       => $googleData['name'] ?? 'Unnamed',
    //             'email'      => $googleData['email'],
    //             'google_id'  => $googleData['id'],
    //             'role'       => 'Customer', // default role
    //             'status'     => 'active',
    //             'created_at' => date('Y-m-d H:i:s'),
    //         ];

    //         $id = $this->userModel->insert($userData);
    //         $user = $this->userModel->find($id);
    //     } else {
    //         // Optional: update google_id if missing
    //         if (empty($user['google_id'])) {
    //             $this->userModel->update($user['id'], ['google_id' => $googleData['id']]);
    //             $user['google_id'] = $googleData['id'];
    //         }
    //     }

    //     // Prepare response like password login
    //     $userResponse = [
    //         'id'    => (int) $user['id'],
    //         'name'  => (string) $user['name'],
    //         'email' => (string) $user['email'],
    //         'phone' => (string) ($user['phone'] ?? null),
    //         'role'  => (string) $user['role'],
    //     ];

    //     // Generate JWT using existing logic
    //     $token = $this->jwt->generateToken([
    //         'id'   => $userResponse['id'],
    //         'role' => $userResponse['role'],
    //     ]);

    //     return [
    //         'user'  => $userResponse,
    //         'token' => $token,
    //     ];
    // }
    /**
     * Login or register a user via Google OAuth
     *
     * @param array $googleData ['id' => google_user_id, 'email' => email, 'name' => full name]
     * @return array ['user' => [...], 'token' => string]
     * @throws \Exception
     */
    public function loginWithGoogle(array $googleData): array
    {
        if (empty($googleData['email']) || empty($googleData['id'])) {
            throw new \Exception('Google login requires email and Google ID');
        }

        // Check if user already exists by email
        $user = $this->userModel->where('email', $googleData['email'])->first();

        if (!$user) {
            // New user: create account without password
            $userData = [
                'name'       => $googleData['name'] ?? 'Unnamed',
                'email'      => $googleData['email'],
                'google_id'  => $googleData['id'],
                'role'       => 'Customer', // default role
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $id = $this->userModel->insert($userData);
            $user = $this->userModel->find($id);
        } else {
            // Optional: update google_id if missing
            if (empty($user['google_id'])) {
                $this->userModel->update($user['id'], ['google_id' => $googleData['id']]);
                $user['google_id'] = $googleData['id'];
            }
        }

        // Prepare response like password login
        $userResponse = [
            'id'    => (int) $user['id'],
            'name'  => (string) $user['name'],
            'email' => (string) $user['email'],
            'phone' => (string) ($user['phone'] ?? null),
            'role'  => (string) $user['role'],
        ];

        // Generate JWT using existing logic
        $token = $this->jwt->generateToken([
            'id'   => $userResponse['id'],
            'role' => $userResponse['role'],
        ]);

        return [
            'user'  => $userResponse,
            'token' => $token,
        ];
    }



    private function _sendWhatsAppOtp(int $userId, string $purpose): bool
    {
        $user = $this->userModel->find($userId);
        if (!$user || !$user['phone']) {
            log_message('error', "WhatsApp OTP failed: User not found or phone missing for ID $userId");
            throw new \Exception('User not found or has no phone number');
        }

        $otp = random_int(100000, 999999);
        $this->_storeOtp($userId, $purpose, 'whatsapp', (string)$otp);

        $phone = $this->normalizePhone($user['phone']);
        log_message('debug', "Sending WhatsApp OTP $otp to $phone for user ID $userId");

        try {
            $sent = $this->whatsAppService->sendOtp($phone, (string)$otp);
            log_message('debug', 'WhatsApp send result: ' . json_encode($sent));
            return $sent;
        } catch (\Exception $e) {
            log_message('error', 'WhatsApp send exception: ' . $e->getMessage());
            return false;
        }
    }


    private function _storeOtp(int $userId, string $purpose, string $channel, string $otp): void
    {
        $this->otpModel->invalidatePrevious($userId, $purpose);
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $this->otpModel->insert([
            'user_id'    => $userId,
            'purpose'    => $purpose,
            'channel'    => $channel,
            'otp_hash'   => $otpHash,
            'expires_at' => $expiresAt,
            'ip_address' => request()->getIPAddress(),
        ]);
    }

    public function createPendingProvider(array $data)
    {
        $userData = [
            'name'        => $data['name'] ?? null,
            'email'       => $data['email'] ?? null,
            'phone'       => isset($data['phone']) ? $this->normalizePhone($data['phone']) : null,
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

    // --- Helper: normalize phone numbers ---
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone); // remove non-digits
        if (str_starts_with($phone, '0')) {
            $phone = '+234' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        return $phone;
    }

    private function normalizeIdentifier(string $identifier): string
    {
        // Normalize only if it looks like a phone number
        if (preg_match('/^\d{8,15}$/', preg_replace('/\D+/', '', $identifier))) {
            return $this->normalizePhone($identifier);
        }
        return $identifier;
    }
}
