<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CaptchaService
{
    public static function verifyToken($token)
    {
        try {
            $res = Http::asForm()->post('https://hcaptcha.com/siteverify', [
                'secret' => config('config.captcha.secret'),
                'response' => $token
            ]);
        } catch (Exception $e) {
            return false;
        }

        if(!$res->ok()) {
            return false;
        }

        $json = $res->json();

        if(!$json) {
            return false;
        }

        if(isset($json['success'])) {
            return $json['success'];
        }

        return false;
    }
}
