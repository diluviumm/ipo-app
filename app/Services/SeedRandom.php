<?php

namespace App\Services;

// Port mulberry32 PRNG dari seed JS agar data demo deterministik.
class SeedRandom
{
    public static function mulberry32(int $seed): callable
    {
        $a = $seed & 0xFFFFFFFF;
        return function () use (&$a) {
            $a = ($a + 0x6D2B79F5) & 0xFFFFFFFF;
            $t = self::imul($a ^ ($a >> 15), 1 | $a);
            $t = ((($t + self::imul($t ^ ($t >> 7), 61 | $t)) & 0xFFFFFFFF) ^ $t) & 0xFFFFFFFF;
            $t ^= $t >> 14;
            return ($t & 0xFFFFFFFF) / 4294967296;
        };
    }

    private static function imul(int $a, int $b): int
    {
        $a &= 0xFFFFFFFF;
        $b &= 0xFFFFFFFF;
        $ah = ($a >> 16) & 0xFFFF;
        $al = $a & 0xFFFF;
        $bh = ($b >> 16) & 0xFFFF;
        $bl = $b & 0xFFFF;
        return (($al * $bl + ((($ah * $bl + $al * $bh) & 0xFFFF) << 16)) & 0xFFFFFFFF);
    }

    public static function n(float $v, int $d = 2): float
    {
        return round($v, $d);
    }
}
