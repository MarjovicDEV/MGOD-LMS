<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CaptchaModel;
use CodeIgniter\HTTP\ResponseInterface;

class CaptchaController extends BaseController
{
    protected $captchaModel;

    public function __construct()
    {
        $this->captchaModel = new CaptchaModel();
    }

    public function index()
    {
        return $this->refresh();
    }

    /**
     * Create a new captcha challenge and return JSON payload.
     */
    public function refresh(): ResponseInterface
    {
        $captcha = $this->captchaModel->createCaptcha($this->request->getIPAddress());

        if (!$captcha || empty($captcha['token'])) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Unable to generate captcha right now.',
            ]);
        }

        return $this->response->setJSON([
            'success'   => true,
            'token'     => $captcha['token'],
            'image_url' => base_url('captcha/image/' . $captcha['token']) . '?t=' . time(),
        ]);
    }

    /**
     * Render captcha image using stored captcha code and background image.
     */
    public function image(?string $token = null): ResponseInterface
    {
        if (empty($token)) {
            return $this->response->setStatusCode(404)->setBody('Captcha not found.');
        }

        $captcha = $this->captchaModel->getActiveCaptchaByToken($token);
        if (!$captcha) {
            return $this->response->setStatusCode(404)->setBody('Captcha expired or invalid.');
        }

        $backgroundPath = WRITEPATH . 'uploads/captcha/captcha_bg.jpg';
        $image = null;

        if (is_file($backgroundPath)) {
            $image = @imagecreatefromjpeg($backgroundPath);
        }

        if (!$image) {
            $image = imagecreatetruecolor(220, 70);
            $fallback = imagecolorallocate($image, 240, 240, 240);
            imagefilledrectangle($image, 0, 0, 220, 70, $fallback);
        }

        $width = imagesx($image);
        $height = imagesy($image);

        for ($i = 0; $i < 6; $i++) {
            $lineColor = imagecolorallocatealpha(
                $image,
                random_int(80, 180),
                random_int(80, 180),
                random_int(80, 180),
                95
            );
            imageline(
                $image,
                random_int(0, $width),
                random_int(0, $height),
                random_int(0, $width),
                random_int(0, $height),
                $lineColor
            );
        }

        $captchaText = strtoupper((string) $captcha['captcha_code']);
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($captchaText);
        $textHeight = imagefontheight($font);

        $x = (int) max(10, (($width - $textWidth) / 2));
        $y = (int) max(10, (($height - $textHeight) / 2));

        $shadowColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 30, 30, 30);

        imagestring($image, $font, $x + 1, $y + 1, $captchaText, $shadowColor);
        imagestring($image, $font, $x, $y, $captchaText, $textColor);

        ob_start();
        imagepng($image);
        $imageData = (string) ob_get_clean();
        imagedestroy($image);

        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setBody($imageData);
    }
}
