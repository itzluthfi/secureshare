<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class IdEncryptionService
{
    protected string $cipher = 'AES-256-CBC';
    protected string $key;

    public function __construct()
    {
        // Use a specific key for ID encryption if available, otherwise fallback to APP_KEY
        $key = Config::get('app.id_encryption_key', Config::get('app.key'));
        
        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        $this->key = $key;
    }

    public function encrypt($id): string
    {
        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($id, $this->cipher, $this->key, 0, $iv);

        // Return as base64url encoded string to be URL safe
        return rtrim(strtr(base64_encode($iv . $encrypted), '+/', '-_'), '=');
    }

    public function decrypt($payload)
    {
        // Decode base64url
        $data = base64_decode(strpad(strtr($payload, '-_', '+/')));
        
        $ivLength = openssl_cipher_iv_length($this->cipher);
        
        if (strlen($data) < $ivLength) {
            return null;
        }

        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        return openssl_decrypt($encrypted, $this->cipher, $this->key, 0, $iv);
    }
}

function strpad($str) {
    return str_pad($str, strlen($str) % 4, '=', STR_PAD_RIGHT);
}
