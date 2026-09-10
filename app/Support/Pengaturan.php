<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class Pengaturan
{
    public static function get(string $kunci, string $default = '0'): string
    {
        try {
            return (string) (DB::table('pengaturan')->where('kunci', $kunci)->value('nilai') ?? $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $kunci, string $nilai): void
    {
        DB::table('pengaturan')->updateOrInsert(['kunci' => $kunci], ['nilai' => $nilai]);
    }

    public static function registerTutup(): bool
    {
        return self::get('register_tutup') === '1';
    }
}
