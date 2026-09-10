<?php

namespace App\Support;

class Tgl
{
    private const BULAN = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    public static function id(?string $waktu, bool $jam = true): string
    {
        if (!$waktu) return '—';
        $ts = strtotime($waktu);
        if (!$ts) return '—';
        $t = date('j', $ts) . ' ' . self::BULAN[(int) date('n', $ts)] . ' ' . date('Y', $ts);
        return $jam ? $t . ', ' . date('H:i', $ts) : $t;
    }
}
