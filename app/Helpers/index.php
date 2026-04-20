<?php

/**
 * Helper functions for the application.
 *
 * @package App\Helpers
 */

use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

/**
 * Calculate the number of minutes past the specific end time.
 *
 * @param mixed $endTime Carbon instance or parsable date string.
 * @return float
 */

if (!function_exists("get_over_time")) {
    function get_over_time($endTime): float
    {
        $now = Carbon::now();

        if ($now->lessThanOrEqualTo($endTime)) {
            return 0;
        }

        return $now->diffInMinutes($endTime);
    }
}

/**
 * Encrypt a string payload using AES-256-CBC.
 *
 * @param string $payload
 * @param string $encryptMethod
 * @return string Base64 encoded encrypted string.
 */
if (!function_exists("cryptEncrypt")) {
    function cryptEncrypt(
        string $payload,
        $encryptMethod = "AES-256-CBC"
    ): string {
        // hash
        $key = hash("sha256", config("common.encrypt_secret_key"));
        $iv = substr(hash("sha256", config("common.secret_iv")), 0, 16);

        return base64_encode(
            openssl_encrypt($payload, $encryptMethod, $key, 0, $iv)
        );
    }
}

/**
 * Decrypt a string payload using AES-256-CBC.
 *
 * @param string $payload Base64 encoded encrypted string.
 * @param string $encryptMethod
 * @return string Decrypted string.
 */
if (!function_exists("cryptDecrypt")) {
    function cryptDecrypt(
        string $payload,
        $encryptMethod = "AES-256-CBC"
    ): string {
        // hash
        $key = hash("sha256", config("common.encrypt_secret_key"));
        $iv = substr(hash("sha256", config("common.secret_iv")), 0, 16);

        return openssl_decrypt(
            base64_decode($payload),
            $encryptMethod,
            $key,
            0,
            $iv
        );
    }
}
