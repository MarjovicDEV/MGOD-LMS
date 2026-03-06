<?php

namespace App\Models;

use CodeIgniter\Model;

class OtpModel extends Model
{
    protected $table            = 'otp_verifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'email',
        'otp_code',
        'otp_type',
        'expires_at',
        'verified_at',
        'attempts',
        'max_attempts',
        'is_used',
        'ip_address',
        'user_agent'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // OTP Configuration
    const OTP_LENGTH = 6;
    const OTP_EXPIRY_MINUTES = 10; // OTP expires in 10 minutes
    const MAX_ATTEMPTS = 3;
    const RATE_LIMIT_MINUTES = 1; // Wait 1 minute before requesting new OTP

    /**
     * Generate a random OTP code
     *
     * @return string
     */
    public function generateOTP(): string
    {
        return str_pad((string)random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Create and send OTP to user
     *
     * @param int $userId User ID
     * @param string $email Email address
     * @param string $type OTP type (login, registration, etc.)
     * @return array|false Returns OTP data or false on failure
     */
    public function createOTP(int $userId, string $email, string $type = 'login')
    {
        // Check rate limiting
        if (!$this->canRequestOTP($email)) {
            return false;
        }

        // Invalidate any existing OTPs for this user and type
        $this->invalidateExistingOTPs($userId, $email, $type);

        // Generate new OTP
        $otpCode = $this->generateOTP();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::OTP_EXPIRY_MINUTES . ' minutes'));

        $request = \Config\Services::request();
        
        $otpData = [
            'user_id'      => $userId,
            'email'        => $email,
            'otp_code'     => $otpCode,
            'otp_type'     => $type,
            'expires_at'   => $expiresAt,
            'max_attempts' => self::MAX_ATTEMPTS,
            'ip_address'   => $request->getIPAddress(),
            'user_agent'   => $request->getUserAgent()->getAgentString()
        ];

        $otpId = $this->insert($otpData);

        if ($otpId) {
            return [
                'id'         => $otpId,
                'otp_code'   => $otpCode,
                'expires_at' => $expiresAt,
                'email'      => $email
            ];
        }

        return false;
    }

    /**
     * Verify OTP code
     *
     * @param string $email Email address
     * @param string $otpCode OTP code to verify
     * @param string $type OTP type
     * @return array|false Returns verification result
     */
    public function verifyOTP(string $email, string $otpCode, string $type = 'login')
    {
        // Find the OTP record
        $otp = $this->where('email', $email)
                    ->where('otp_code', $otpCode)
                    ->where('otp_type', $type)
                    ->where('is_used', 0)
                    ->orderBy('created_at', 'DESC')
                    ->first();

        if (!$otp) {
            return [
                'success' => false,
                'message' => 'Invalid OTP code.'
            ];
        }

        // Check if OTP has expired
        if (strtotime($otp['expires_at']) < time()) {
            return [
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.'
            ];
        }

        // Check if max attempts exceeded
        if ($otp['attempts'] >= $otp['max_attempts']) {
            return [
                'success' => false,
                'message' => 'Maximum verification attempts exceeded. Please request a new OTP.'
            ];
        }

        // Increment attempts
        $this->update($otp['id'], [
            'attempts' => $otp['attempts'] + 1
        ]);

        // Mark as verified and used
        $this->update($otp['id'], [
            'verified_at' => date('Y-m-d H:i:s'),
            'is_used'     => 1
        ]);

        return [
            'success' => true,
            'message' => 'OTP verified successfully.',
            'user_id' => $otp['user_id']
        ];
    }

    /**
     * Check if user can request a new OTP (rate limiting)
     *
     * @param string $email Email address
     * @return bool
     */
    public function canRequestOTP(string $email): bool
    {
        $recentOTP = $this->where('email', $email)
                          ->where('created_at >', date('Y-m-d H:i:s', strtotime('-' . self::RATE_LIMIT_MINUTES . ' minutes')))
                          ->orderBy('created_at', 'DESC')
                          ->first();

        return $recentOTP === null;
    }

    /**
     * Invalidate existing OTPs for a user
     *
     * @param int $userId User ID
     * @param string $email Email address
     * @param string $type OTP type
     * @return void
     */
    public function invalidateExistingOTPs(int $userId, string $email, string $type): void
    {
        $this->where('user_id', $userId)
             ->where('email', $email)
             ->where('otp_type', $type)
             ->where('is_used', 0)
             ->set(['is_used' => 1])
             ->update();
    }

    /**
     * Clean up expired OTPs (should be run via cron job)
     *
     * @return int Number of deleted records
     */
    public function cleanupExpiredOTPs(): int
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))
                    ->where('created_at <', date('Y-m-d H:i:s', strtotime('-24 hours')))
                    ->delete();
    }

    /**
     * Get OTP statistics for a user
     *
     * @param int $userId User ID
     * @return array
     */
    public function getUserOTPStats(int $userId): array
    {
        $total = $this->where('user_id', $userId)->countAllResults(false);
        $verified = $this->where('verified_at IS NOT NULL')->countAllResults(false);
        $expired = $this->where('expires_at <', date('Y-m-d H:i:s'))->countAllResults(false);
        $failed = $this->where('attempts >=', 'max_attempts')->countAllResults();

        return [
            'total'    => $total,
            'verified' => $verified,
            'expired'  => $expired,
            'failed'   => $failed
        ];
    }

    /**
     * Check if OTP is valid and not expired
     *
     * @param string $email Email address
     * @param string $otpCode OTP code
     * @return bool
     */
    public function isValidOTP(string $email, string $otpCode): bool
    {
        $otp = $this->where('email', $email)
                    ->where('otp_code', $otpCode)
                    ->where('is_used', 0)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->first();

        return $otp !== null;
    }

    /**
     * Get remaining time for OTP expiry
     *
     * @param string $email Email address
     * @param string $otpCode OTP code
     * @return int|false Remaining seconds or false if not found
     */
    public function getRemainingTime(string $email, string $otpCode)
    {
        $otp = $this->where('email', $email)
                    ->where('otp_code', $otpCode)
                    ->where('is_used', 0)
                    ->first();

        if ($otp) {
            $remaining = strtotime($otp['expires_at']) - time();
            return max(0, $remaining);
        }

        return false;
    }

    /**
     * Resend OTP (invalidate old one and create new)
     *
     * @param int $userId User ID
     * @param string $email Email address
     * @param string $type OTP type
     * @return array|false
     */
    public function resendOTP(int $userId, string $email, string $type = 'login')
    {
        // Check rate limiting
        if (!$this->canRequestOTP($email)) {
            return [
                'success' => false,
                'message' => 'Please wait before requesting another OTP.',
                'wait_time' => self::RATE_LIMIT_MINUTES * 60
            ];
        }

        return $this->createOTP($userId, $email, $type);
    }
}
