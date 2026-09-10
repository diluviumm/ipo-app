<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Port seedOperators.js — 38 operator provinsi + admin + user_biasa.
// IDEMPOTEN: updateOrInsert by username — akun existing tidak diubah.
class OperatorsSeeder extends Seeder
{
    public const OPERATORS = [
        ['operator_aceh', 'Operator Aceh', 1], ['operator_sumut', 'Operator Sumatera Utara', 2],
        ['operator_sumbar', 'Operator Sumatera Barat', 3], ['operator_riau', 'Operator Riau', 4],
        ['operator_jambi', 'Operator Jambi', 5], ['operator_sumsel', 'Operator Sumatera Selatan', 6],
        ['operator_bengkulu', 'Operator Bengkulu', 7], ['operator_lampung', 'Operator Lampung', 8],
        ['operator_babel', 'Operator Kepulauan Bangka Belitung', 9], ['operator_kepri', 'Operator Kepulauan Riau', 10],
        ['operator_jakarta', 'Operator DKI Jakarta', 11], ['operator_jabar', 'Operator Jawa Barat', 12],
        ['operator_jateng', 'Operator Jawa Tengah', 13], ['operator_diy', 'Operator DI Yogyakarta', 14],
        ['operator_jatim', 'Operator Jawa Timur', 15], ['operator_banten', 'Operator Banten', 16],
        ['operator_bali', 'Operator Bali', 17], ['operator_ntb', 'Operator Nusa Tenggara Barat', 18],
        ['operator_ntt', 'Operator Nusa Tenggara Timur', 19], ['operator_kalbar', 'Operator Kalimantan Barat', 20],
        ['operator_kalteng', 'Operator Kalimantan Tengah', 21], ['operator_kalsel', 'Operator Kalimantan Selatan', 22],
        ['operator_kaltim', 'Operator Kalimantan Timur', 23], ['operator_kaltara', 'Operator Kalimantan Utara', 24],
        ['operator_sulut', 'Operator Sulawesi Utara', 25], ['operator_sulteng', 'Operator Sulawesi Tengah', 26],
        ['operator_sulsel', 'Operator Sulawesi Selatan', 27], ['operator_sultra', 'Operator Sulawesi Tenggara', 28],
        ['operator_gorontalo', 'Operator Gorontalo', 29], ['operator_sulbar', 'Operator Sulawesi Barat', 30],
        ['operator_maluku', 'Operator Maluku', 31], ['operator_malut', 'Operator Maluku Utara', 32],
        ['operator_pabar', 'Operator Papua Barat', 33], ['operator_papua', 'Operator Papua', 34],
        ['operator_pasel', 'Operator Papua Selatan', 35], ['operator_pateng', 'Operator Papua Tengah', 36],
        ['operator_papeg', 'Operator Papua Pegunungan', 37], ['operator_pabdaya', 'Operator Papua Barat Daya', 38],
    ];

    public function run(): void
    {
        $new = 0;
        $all = array_merge(
            [['admin', 'Superadmin IPO', 'admin', null, 'admin123']],
            array_map(fn($o) => [$o[0], $o[1], 'operator', $o[2], substr($o[0], 9) . '123'], self::OPERATORS),
            [['user_biasa', 'User Biasa', 'user', null, 'user123']],
        );
        foreach ($all as [$username, $full, $role, $prov, $pass]) {
            $exists = DB::table('users')->where('username', $username)->exists();
            DB::table('users')->updateOrInsert(
                ['username' => $username],
                ['password_hash' => Hash::make($pass), 'full_name' => $full, 'role' => $role, 'province_id' => $prov]
            );
            if (!$exists) $new++;
        }
        $total = DB::table('users')->count();
        $this->command->info("Operators: {$new} akun baru, total {$total} users.");
    }
}
