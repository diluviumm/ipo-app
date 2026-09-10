<?php

namespace App\Support;

class Csv
{
    public static function aman(mixed $v): string
    {
        $s = (string) $v;
        if ($s !== '' && str_contains("=+-@\t\r", $s[0])) {
            return "'" . $s;
        }
        return $s;
    }

    /** @param array $baris */
    public static function baris(array $baris): array
    {
        return array_map([self::class, 'aman'], $baris);
    }
}
