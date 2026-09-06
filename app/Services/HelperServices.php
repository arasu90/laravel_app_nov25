<?php
namespace App\Services;

class HelperServices
{
    public static function twoDecimals(float|string|int $value): float
    {
        return round((float) $value, 2);
    }

    public static function datetimeFormat(string $value, string $format = 'Y-m-d H:i:s'): string
    {
        return \Carbon\Carbon::parse($value)->format($format);
    }
}
