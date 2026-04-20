<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use DateTime;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\Types\TypeCode128;
use Picqer\Barcode\PngRenderer;

/**
 * Class Helper
 *
 * General-purpose helper class providing utilities for string manipulation,
 * date calculations, encryption/decryption, and barcode generation.
 *
 * @package App\Helpers
 */
class Helper
{
    /**
     * Number show number row
     *
     * @return array<int>
     */
    public static function listPerPages()
    {
        return [15, 50, 100, 200, 500];
    }

    /**
     * Trim string
     *
     * @param string $pString
     *
     * @return string
     */
    public static function mbTrim($pString)
    {
        return preg_replace(
            "/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u",
            "",
            $pString
        );
    }

    /**
     * Add minutes to a specific date.
     *
     * @param string|DateTime $date
     * @param int $minutes
     * @return Carbon
     */
    public static function getDataAddMinutes($date, int $minutes)
    {
        $dateAdd = Carbon::parse($date);

        return $dateAdd->addMinutes($minutes);
    }

    /**
     * Subtract minutes from a specific date.
     *
     * @param string|DateTime $date
     * @param int $minutes
     * @return Carbon
     */
    public static function getDataSubMinutes($date, int $minutes)
    {
        $dateSub = Carbon::parse($date);

        return $dateSub->subMinutes($minutes);
    }

    /**
     * Generate an array of years from the current year back to a specified year.
     *
     * @param int $year Start year.
     * @return array<int>
     */
    public static function listYearsToCurrentYear($year)
    {
        $yearArr = [];
        $yearCurrent = Carbon::now()->year;

        for ($y = $yearCurrent; $y >= $year; $y--) {
            $yearArr[] = $y;
        }

        return $yearArr;
    }

    /**
     * Encrypt a string payload using AES-256-CBC.
     *
     * @param string $payload
     * @param string $encryptMethod
     * @return string Base64 encoded encrypted string.
     */
    public static function cryptEncrypt(
        string $payload,
        string $encryptMethod = "AES-256-CBC"
    ): string {
        $key = hash("sha256", config("common.encrypt_secret_key"));
        $iv = substr(hash("sha256", config("common.secret_iv")), 0, 16);

        return base64_encode(
            openssl_encrypt($payload, $encryptMethod, $key, 0, $iv)
        );
    }

    /**
     * Decrypt a string payload using AES-256-CBC.
     *
     * @param string $payload Base64 encoded encrypted string.
     * @param string $encryptMethod
     * @return string Decrypted string.
     */
    public static function cryptDecrypt(
        string $payload,
        string $encryptMethod = "AES-256-CBC"
    ): string {
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

    /**
     * Calculate the great-circle distance between two points (Haversine formula).
     *
     * @param float $latitudeFrom
     * @param float $longitudeFrom
     * @param float $latitudeTo
     * @param float $longitudeTo
     * @param int $earthRadius In meters.
     * @return float Distance in meters.
     */
    public static function haversineGreatCircleDistance(
        $latitudeFrom,
        $longitudeFrom,
        $latitudeTo,
        $longitudeTo,
        $earthRadius = 6371000
    ) {
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle =
            2 *
            asin(
                sqrt(
                    pow(sin($latDelta / 2), 2) +
                        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
                )
            );
        return $angle * $earthRadius;
    }

    /**
     * Generate a random password string.
     *
     * @param int|null $length
     * @return string
     */
    public static function randomPassword($length = null)
    {
        if (is_null($length)) {
            $length = config('const.PASSWORD_LENGTH');
        }

        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        $specialChars = '[]/.=,:@$!%*#?&+_-';

        $password =
            $letters[rand(0, strlen($letters) - 1)] .
            $digits[rand(0, strlen($digits) - 1)] .
            $specialChars[rand(0, strlen($specialChars) - 1)];

        $remainingLength = $length - 3;
        $password .= Str::random($remainingLength);

        return str_shuffle($password);
    }

    /**
     * Generate a random barcode identifier based on UUID.
     *
     * @return string
     */
    public static function generateBarcode()
    {
        $uuid = Str::uuid()->toString();
        $barcodeData = strtoupper(str_replace('-', '', $uuid));

        return substr($barcodeData, 0, config('const.BARCODE_LENGTH'));
    }

    /**
     * Get value name_en from name
     * @param string $name
     * @return string
     */
    public function getNameEnFromName(string $name): string
    {
        $name = mb_strtolower($name, 'UTF-8');
        $accents = [
            'a' => ['à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ'],
            'e' => ['è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ'],
            'i' => ['ì', 'í', 'ị', 'ỉ', 'ĩ'],
            'o' => ['ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ'],
            'u' => ['ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ'],
            'y' => ['ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ'],
            'd' => ['đ'],
        ];
        foreach ($accents as $nonAccentedChar => $accentedChars) {
            $name = str_replace($accentedChars, $nonAccentedChar, $name);
        }
        $name = preg_replace('/[^a-z\s]/', '', $name);
        $name = preg_replace('/\s+/', '_', trim($name));
        return $name;
    }
}
