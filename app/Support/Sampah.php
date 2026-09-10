<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class Sampah
{
    public static function buang($user, string $tabel, string $dim, int $rowId, array $row): void
    {
        DB::table('sampah')->insert([
            'tabel' => $tabel, 'dim' => $dim, 'row_id' => $rowId,
            'data' => json_encode($row, JSON_UNESCAPED_UNICODE),
            'dihapus_oleh' => $user ? $user->username : null,
        ]);
    }

    public static function pulihkan($user, int $id): bool
    {
        $s = DB::table('sampah')->where('id', $id)->first();
        if (!$s) return false;
        $data = json_decode($s->data, true);
        if (!is_array($data)) return false;
        unset($data['id']);
        $baru = DB::table($s->tabel)->insertGetId($data);
        Audit::catat($user, 'pulih', $s->tabel, $baru, ['dim' => $s->dim, 'dari_sampah' => $id]);
        DB::table('sampah')->where('id', $id)->delete();
        return true;
    }
}
