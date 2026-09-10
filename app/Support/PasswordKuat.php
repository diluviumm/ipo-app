<?php

namespace App\Support;

class PasswordKuat
{
    private const LEMAH = [
        '123456', '12345678', '123123', 'password', 'qwerty',
        'abcdef', 'admin123', 'user123', 'ipo123', 'operator',
        '123456789', '12345', '111111', '000000',
    ];

    public static function cek(?string $pw): ?string
    {
        $p = strtolower(trim((string) $pw));
        if (in_array($p, self::LEMAH, true)) return 'Password terlalu umum — pilih yang lebih unik.';
        if (preg_match('/^(.)\1+$/', $p)) return 'Password tidak boleh satu karakter berulang.';
        return null;
    }
}