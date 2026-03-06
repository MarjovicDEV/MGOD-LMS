<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailVerificationModel extends Model
{
    protected $table            = 'email_verifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'email',
        'verification_token',
        'expires_at',
        'verified_at',
        'is_used',
        'ip_address'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Verification Configuration
    const TOKEN_LENGTH = 32; // 32 bytes = 64 hex characters
    const EXPIRY_HOURS = 24; // Verification link expires in 24 hours

    /**
     * Generate a unique verification token
     *
     * @return string
     */
    protected function generateToken(): string
    {
        return bin2hex(random_bytes(self::TOKEN_LENGTH));
    }

    /**
     * Create email verification record
     *
     * @param int $userId User ID
     * @param string $email Email address
     * @return array|false Returns verification data or false on failure
     */
    public function createVerification(int $userId, string $email)
    {
        // Invalidate existing tokens for this user/email
        $this->invalidateExistingTokens($userId, $email);

        // Generate unique token
        $token = $this->generateToken();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::EXPIRY_HOURS . ' hours'));

        $request = \Config\Services::request();
        
        $verificationData = [
            'user_id'            => $userId,
            'email'              => $email,
            'verification_token' => $token,
            'expires_at'         => $expiresAt,
            'ip_address'         => $request->getIPAddress()
        ];

        $verificationId = $this->insert($verificationData);

        if ($verificationId) {
            return [
                'id'                 => $verificationId,
                'verification_token' => $token,
                'expires_at'         => $expiresAt,
                'email'              => $email
            ];
        }

        return false;
    }

    /**
     * Verify email using token
     *
     * @param string $token Verification token
     * @return array Verification result
     */
    public function verifyToken(string $token): array
    {
        // Find the verification record
        $verification = $this->where('verification_token', $token)
                             ->where('is_used', 0)
                             ->first();

        if (!$verification) {
            return [
                'success' => false,
                'message' => 'Invalid verification link. The link may have already been used.'
            ];
        }

        // Check if token has expired
        if (strtotime($verification['expires_at']) < time()) {
            return [
                'success' => false,
                'message' => 'Verification link has expired. Please request a new verification email.',
                'expired' => true
            ];
        }

        // Mark as verified and used
        $this->update($verification['id'], [
            'verified_at' => date('Y-m-d H:i:s'),
            'is_used'     => 1
        ]);

        return [
            'success' => true,
            'message' => 'Email verified successfully!',
            'user_id' => $verification['user_id'],
            'email'   => $verification['email']
        ];
    }

    /**
     * Invalidate existing verification tokens for a user
     *
     * @param int $userId User ID
     * @param string $email Email address
     * @return void
     */
    public function invalidateExistingTokens(int $userId, string $email): void
    {
        $this->where('user_id', $userId)
             ->where('email', $email)
             ->where('is_used', 0)
             ->set(['is_used' => 1])
             ->update();
    }

    /**
     * Check if user has verified their email
     *
     * @param int $userId User ID
     * @return bool
     */
    public function isEmailVerified(int $userId): bool
    {
        $verified = $this->where('user_id', $userId)
                         ->where('verified_at IS NOT NULL')
                         ->where('is_used', 1)
                         ->first();

        return $verified !== null;
    }

    /**
     * Clean up expired verification tokens (should be run via cron job)
     *
     * @return int Number of deleted records
     */
    public function cleanupExpiredTokens(): int
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))
                    ->where('created_at <', date('Y-m-d H:i:s', strtotime('-7 days')))
                    ->delete();
    }

    /**
     * Get verification status for a user
     *
     * @param int $userId User ID
     * @return array
     */
    public function getVerificationStatus(int $userId): array
    {
        $verification = $this->where('user_id', $userId)
                             ->orderBy('created_at', 'DESC')
                             ->first();

        if (!$verification) {
            return [
                'has_verification' => false,
                'is_verified'      => false,
                'is_expired'       => false
            ];
        }

        $isExpired = strtotime($verification['expires_at']) < time();
        $isVerified = !empty($verification['verified_at']);

        return [
            'has_verification' => true,
            'is_verified'      => $isVerified,
            'is_expired'       => $isExpired,
            'email'            => $verification['email'],
            'created_at'       => $verification['created_at'],
            'expires_at'       => $verification['expires_at']
        ];
    }

    /**
     * Resend verification email (creates new token)
     *
     * @param int $userId User ID
     * @param string $email Email address
     * @return array|false
     */
    public function resendVerification(int $userId, string $email)
    {
        return $this->createVerification($userId, $email);
    }
}
