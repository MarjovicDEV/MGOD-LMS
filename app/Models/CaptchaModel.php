<?php

namespace App\Models;

use CodeIgniter\Model;

class CaptchaModel extends Model
{
    protected $table            = 'captchas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'captcha_token',
        'captcha_code',
        'ip_address',
        'expires_at',
        'is_used',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    const CAPTCHA_LENGTH = 6;
    const CAPTCHA_EXPIRY_MINUTES = 5;

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Generate random captcha text.
     */
    public function generateCaptchaCode(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';

        for ($i = 0; $i < self::CAPTCHA_LENGTH; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $code;
    }

    /**
     * Create a new captcha challenge.
     */
    public function createCaptcha(?string $ipAddress = null)
    {
        $this->cleanupExpiredCaptchas();

        $captchaToken = bin2hex(random_bytes(32));
        $captchaCode = $this->generateCaptchaCode();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::CAPTCHA_EXPIRY_MINUTES . ' minutes'));

        $captchaId = $this->insert([
            'captcha_token' => $captchaToken,
            'captcha_code'  => $captchaCode,
            'ip_address'    => $ipAddress,
            'expires_at'    => $expiresAt,
            'is_used'       => 0,
        ]);

        if (!$captchaId) {
            return false;
        }

        return [
            'id'         => $captchaId,
            'token'      => $captchaToken,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Get active captcha challenge by token.
     */
    public function getActiveCaptchaByToken(string $token): ?array
    {
        $captcha = $this->where('captcha_token', $token)
            ->where('is_used', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();

        return $captcha ?: null;
    }

    /**
     * Verify captcha input and mark challenge as used when valid.
     */
    public function verifyCaptcha(string $token, string $inputCode, ?string $ipAddress = null): bool
    {
        $captcha = $this->getActiveCaptchaByToken($token);

        if (!$captcha) {
            return false;
        }

        if (!empty($captcha['ip_address']) && !empty($ipAddress) && $captcha['ip_address'] !== $ipAddress) {
            return false;
        }

        $isValid = strtoupper(trim($inputCode)) === strtoupper((string) $captcha['captcha_code']);

        if (!$isValid) {
            return false;
        }

        $this->update($captcha['id'], ['is_used' => 1]);

        return true;
    }

    /**
     * Delete old or consumed captcha rows.
     */
    public function cleanupExpiredCaptchas(): int
    {
        $deletedExpired = $this->where('expires_at <', date('Y-m-d H:i:s'))->delete();
        $deletedUsed = $this->where('is_used', 1)
            ->where('updated_at <', date('Y-m-d H:i:s', strtotime('-1 day')))
            ->delete();

        return (int) $deletedExpired + (int) $deletedUsed;
    }
}
