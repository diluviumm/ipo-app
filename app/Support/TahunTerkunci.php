<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class TahunTerkunci
{
    public static function terkunci(int $year): bool
    {
        try {
            return DB::table('tahun_terkunci')->where('year', $year)->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function boleh($user, int $year): bool
    {
        if (!self::terkunci($year)) return true;
        return $user && $user->role === 'admin';
    }
}
