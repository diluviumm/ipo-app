<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SampleSeeder extends Seeder
{
    public const PROVINCES = [
        'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Jambi',
        'Sumatera Selatan', 'Bengkulu', 'Lampung', 'Kepulauan Bangka Belitung',
        'Kepulauan Riau', 'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah',
        'DI Yogyakarta', 'Jawa Timur', 'Banten', 'Bali', 'Nusa Tenggara Barat',
        'Nusa Tenggara Timur', 'Kalimantan Barat', 'Kalimantan Tengah',
        'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara', 'Sulawesi Utara',
        'Sulawesi Tengah', 'Sulawesi Selatan', 'Sulawesi Tenggara', 'Gorontalo',
        'Sulawesi Barat', 'Maluku', 'Maluku Utara', 'Papua Barat',
        'Papua', 'Papua Selatan', 'Papua Tengah', 'Papua Pegunungan', 'Papua Barat Daya',
    ];

    public function run(): void
    {
        foreach (self::PROVINCES as $name) {
            DB::table('provinces')->updateOrInsert(['name' => $name], ['name' => $name]);
        }

        $users = [
            ['username' => 'admin', 'password' => 'admin123', 'full_name' => 'Superadmin IPO', 'role' => 'admin', 'province_id' => null],
            ['username' => 'operator_jatim', 'password' => 'jatim123', 'full_name' => 'Operator Jawa Timur', 'role' => 'operator', 'province_id' => 15],
            ['username' => 'operator_jabar', 'password' => 'jabar123', 'full_name' => 'Operator Jawa Barat', 'role' => 'operator', 'province_id' => 12],
            ['username' => 'user_biasa', 'password' => 'user123', 'full_name' => 'User Biasa', 'role' => 'user', 'province_id' => null],
        ];
        foreach ($users as $u) {
            DB::table('users')->updateOrInsert(
                ['username' => $u['username']],
                ['password_hash' => Hash::make($u['password']), 'full_name' => $u['full_name'], 'role' => $u['role'], 'province_id' => $u['province_id']]
            );
        }

        $cityIds = [];
        foreach ([
            [12, 'Kota Bandung'], [12, 'Kab. Bogor'], [15, 'Kota Surabaya'],
            [15, 'Kab. Malang'], [11, 'Kota Jakarta Pusat'], [1, 'Kota Banda Aceh'],
            [17, 'Kota Denpasar'],
        ] as $i => [$prov, $name]) {
            $cityIds[$i] = DB::table('cities')->updateOrInsert(
                ['province_id' => $prov, 'name' => $name], ['province_id' => $prov, 'name' => $name]
            ) ? DB::table('cities')->where('province_id', $prov)->where('name', $name)->value('id') : null;
        }

        $distIds = [];
        foreach ([[0, 'Kec. Bandung Wetan'], [0, 'Kec. Cibeunying'], [2, 'Kec. Genteng'], [2, 'Kec. Wonokromo'], [4, 'Kec. Menteng']] as $j => [$ci, $name]) {
            if (!empty($cityIds[$ci])) {
                $distIds[$j] = DB::table('districts')->updateOrInsert(
                    ['city_id' => $cityIds[$ci], 'name' => $name], ['city_id' => $cityIds[$ci], 'name' => $name]
                ) ? DB::table('districts')->where('city_id', $cityIds[$ci])->where('name', $name)->value('id') : null;
            }
        }

        $villIds = [];
        foreach ([[0, 'Kel. Citarum'], [1, 'Kel. Cihampelas'], [2, 'Kel. Ketabang']] as $k => [$di, $name]) {
            if (!empty($distIds[$di])) {
                $villIds[$k] = DB::table('villages')->updateOrInsert(
                    ['district_id' => $distIds[$di], 'name' => $name], ['district_id' => $distIds[$di], 'name' => $name]
                ) ? DB::table('villages')->where('district_id', $distIds[$di])->where('name', $name)->value('id') : null;
            }
        }
        $villIds = array_values(array_filter($villIds));

        $respIds = [];
        $ageGroups = ['18-25', '26-35', '36-45', '46-55', '56-65'];
        for ($i = 0; $i < 30 && $villIds; $i++) {
            $age = 18 + mt_rand(0, 49);
            $ag = $age < 25 ? '18-25' : ($age < 35 ? '26-35' : ($age < 45 ? '36-45' : ($age < 55 ? '46-55' : '56-65')));
            $respIds[] = DB::table('respondents')->insertGetId([
                'village_id' => $villIds[$i % count($villIds)], 'year' => 2024,
                'age' => $age, 'gender' => $i % 2 ? 'P' : 'L', 'age_group' => $ag,
            ]);
        }

        foreach (DB::table('districts')->pluck('id') as $dId) {
            $pend = 50000 + mt_rand(0, 99999);
            $sdm = 100 + mt_rand(0, 299);
            DB::table('sdm_olahraga')->updateOrInsert(
                ['district_id' => $dId, 'year' => 2024],
                ['jumlah_penduduk_5plus' => $pend, 'jumlah_sdm' => $sdm, 'nilai_aktual' => $sdm / $pend, 'indeks' => min($sdm / $pend / 0.005, 1)]
            );
        }

        $first20 = array_slice($respIds, 0, 20);
        foreach ($first20 as $rId) {
            $pg = 2 + mt_rand() / mt_getrandmax() * 3;
            $sk = 2 + mt_rand() / mt_getrandmax() * 3;
            $pr = 2 + mt_rand() / mt_getrandmax() * 3;
            $na = ($pg + $sk + $pr) / 3;
            DB::table('literasi_fisik')->updateOrInsert(['respondent_id' => $rId, 'year' => 2024], [
                'pengetahuan' => round($pg, 2), 'sikap' => round($sk, 2), 'perilaku' => round($pr, 2),
                'nilai_aktual' => round($na, 4), 'indeks' => round(min($na / 5, 1), 4),
            ]);
            DB::table('partisipasi')->updateOrInsert(['respondent_id' => $rId, 'year' => 2024], [
                'frekuensi' => mt_rand(1, 7), 'durasi' => mt_rand(15, 134), 'intensitas' => mt_rand(1, 10),
            ]);
            $vo2 = 25 + mt_rand() / mt_getrandmax() * 35;
            $kat = $vo2 >= 51.2 ? 'Unggul' : ($vo2 >= 44.3 ? 'Baik Sekali' : ($vo2 >= 38.3 ? 'Baik' : ($vo2 >= 32 ? 'Sedang' : ($vo2 >= 26.8 ? 'Kurang' : 'Kurang Sekali'))));
            DB::table('kebugaran')->updateOrInsert(['respondent_id' => $rId, 'year' => 2024], [
                'vo2max' => round($vo2, 2), 'kategori' => $kat, 'nilai_aktual' => round($vo2, 2),
                'indeks' => round(max(0, min(($vo2 - 20.1) / 32, 1)), 4),
            ]);
            $fs = 2 + mt_rand() / mt_getrandmax() * 3;
            $ps = 2 + mt_rand() / mt_getrandmax() * 3;
            DB::table('kesehatan')->updateOrInsert(['respondent_id' => $rId, 'year' => 2024], [
                'fisik' => round($fs, 2), 'psikis' => round($ps, 2),
                'nilai_aktual' => round(($fs + $ps) / 2, 4), 'indeks' => round(min((($fs + $ps) / 2) / 5, 1), 4),
            ]);
            $rs = 2 + mt_rand() / mt_getrandmax() * 3;
            $ms = 2 + mt_rand() / mt_getrandmax() * 3;
            DB::table('perkembangan_personal')->updateOrInsert(['respondent_id' => $rId, 'year' => 2024], [
                'resiliensi' => round($rs, 2), 'modal_sosial' => round($ms, 2),
                'nilai_aktual' => round(($rs + $ms) / 2, 4), 'indeks' => round(min((($rs + $ms) / 2) / 5, 1), 4),
            ]);
            $bb = 100000 + mt_rand(0, 2000000);
            $bj = 50000 + mt_rand(0, 500000);
            DB::table('ekonomi')->updateOrInsert(['respondent_id' => $rId, 'year' => 2024], [
                'belanja_barang' => $bb, 'belanja_jasa' => $bj, 'total_belanja' => $bb + $bj,
                'kategori_belanja' => ($bb + $bj) > 1000000 ? 'Tinggi' : 'Rendah',
            ]);
        }

        foreach (DB::table('cities')->pluck('id') as $cId) {
            $em = mt_rand(0, 19);
            $pr2 = mt_rand(0, 14);
            $pz = mt_rand(0, 9);
            DB::table('performa')->updateOrInsert(['city_id' => $cId, 'year' => 2024], [
                'medali_emas' => $em, 'medali_perak' => $pr2, 'medali_perunggu' => $pz,
                'nilai_aktual' => $em * 5 + $pr2 * 3 + $pz,
            ]);
        }

        foreach (DB::table('provinces')->pluck('id') as $pid) {
            \App\Services\IpoCalculator::full($pid, 2024);
        }
    }
}
