<?php

namespace App\Modules\Auth\Models;

use CodeIgniter\Model;

class OtpModel extends Model
{
    protected $table = 'otps';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'otp_hash', 'channel', 'expires_at', 'created_at', 'purpose', 'used_at', 'ip_address'];
    protected $useTimestamps = true;
    protected $updatedField = 'updated_at'; // Define the updated_at field
    protected $returnType = 'array';

    /**
     * Invalidates all previous active OTPs for a given user and purpose.
     */
    public function invalidatePrevious(int $userId, string $purpose): void
    {
        $this->where('user_id', $userId)
             ->where('purpose', $purpose)
             ->where('expires_at >', date('Y-m-d H:i:s')) // Only invalidate active ones
             ->where('used_at IS NULL') // Only invalidate unused ones
             ->set(['used_at' => date('Y-m-d H:i:s')]) // Mark as used
             ->update();
    }

    /**
     * Retrieves a valid (not expired, not used) OTP for a given user and purpose.
     */
    public function getValidOtp(int $userId, string $purpose): ?array
    {
        return $this->where('user_id', $userId)
                    ->where('purpose', $purpose)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->where('used_at IS NULL')
                    ->orderBy('created_at', 'DESC') // Get the latest valid OTP
                    ->first();
    }
}
