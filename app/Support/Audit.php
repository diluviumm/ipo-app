<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class Audit
{
    public static function catat($user, string $aksi, string $tabel, ?int $rowId = null, array $detail = []): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => $user?->id,
            'username' => $user?->username,
            'aksi' => $aksi,
            'tabel' => $tabel,
            'row_id' => $rowId,
            'detail' => $detail ? json_encode($detail, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }
}