<?php

namespace Database\Seeders;

use App\Services\IpoCalculator;
use App\Services\SeedRandom;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Port seedNasional2024.js — 1 kota + 2 kecamatan + 2 desa + 8 responden
// per provinsi (2024, deterministik). Provinsi yang sudah punya data
// SDM 2024 dilewati.
class Nasional2024Seeder extends Seeder
{
    public const YEAR = 2024;

    public const CAPITALS = [
        1 => 'Banda Aceh', 2 => 'Medan', 3 => 'Padang', 4 => 'Pekanbaru', 5 => 'Jambi',
        6 => 'Palembang', 7 => 'Bengkulu', 8 => 'Bandar Lampung', 9 => 'Pangkal Pinang',
        10 => 'Tanjung Pinang', 11 => 'Jakarta Pusat', 12 => 'Bandung', 13 => 'Semarang',
        14 => 'Yogyakarta', 15 => 'Surabaya', 16 => 'Serang', 17 => 'Denpasar', 18 => 'Mataram',
        19 => 'Kupang', 20 => 'Pontianak', 21 => 'Palangka Raya', 22 => 'Banjarmasin',
        23 => 'Samarinda', 24 => 'Tanjung Selor', 25 => 'Manado', 26 => 'Palu',
        27 => 'Makassar', 28 => 'Kendari', 29 => 'Gorontalo', 30 => 'Mamuju',
        31 => 'Ambon', 32 => 'Ternate', 33 => 'Manokwari', 34 => 'Jayapura',
        35 => 'Merauke', 36 => 'Nabire', 37 => 'Wamena', 38 => 'Sorong',
    ];

    public static function ageGroup(int $age): string
    {
        if ($age < 25) return '18-25';
        if ($age < 35) return '26-35';
        if ($age < 45) return '36-45';
        if ($age < 55) return '46-55';
        return '56-65';
    }

    public static function kebKategori(float $v): string
    {
        if ($v >= 52.1) return 'Baik Sekali';
        if ($v >= 44.3) return 'Baik';
        if ($v >= 38.3) return 'Cukup';
        if ($v >= 32) return 'Sedang';
        if ($v >= 26.8) return 'Kurang';
        return 'Kurang Sekali';
    }

    public function run(): void
    {
        $added = 0;
        foreach (DB::table('provinces')->orderBy('id')->get() as $p) {
            $has = DB::table('sdm_olahraga as s')
                ->join('districts as d', 'd.id', '=', 's.district_id')
                ->join('cities as c', 'c.id', '=', 'd.city_id')
                ->where('c.province_id', $p->id)->where('s.year', self::YEAR)->exists();
            if ($has) continue;
            $this->seedOne($p->id, self::CAPITALS[$p->id] ?? $p->name);
            $added++;
        }

        foreach (DB::table('provinces')->pluck('id') as $pid) {
            IpoCalculator::full($pid, self::YEAR);
        }
        $this->command->info("Nasional2024: {$added} provinsi ditambah, IPO 2024 dihitung.");
    }

    private function seedOne(int $pid, string $capital): void
    {
        $N = [SeedRandom::class, 'n'];
        $rnd = SeedRandom::mulberry32($pid * 7919 + 13);
        $q = 0.45 + $rnd() * 0.5;
        $Y = self::YEAR;

        $cityId = DB::table('cities')->insertGetId(['province_id' => $pid, 'name' => 'Kota ' . $capital]);
        $dIds = [
            DB::table('districts')->insertGetId(['city_id' => $cityId, 'name' => 'Kec. ' . $capital . ' Utara']),
            DB::table('districts')->insertGetId(['city_id' => $cityId, 'name' => 'Kec. ' . $capital . ' Selatan']),
        ];
        foreach ($dIds as $dId) {
            $pend = 40000 + (int) floor($rnd() * 90000);
            $sdm = (int) floor($pend * 0.005 * $q);
            $na = $sdm / $pend;
            DB::table('sdm_olahraga')->insert([
                'district_id' => $dId, 'year' => $Y, 'jumlah_penduduk_5plus' => $pend,
                'jumlah_sdm' => $sdm, 'nilai_aktual' => $N($na, 5), 'indeks' => $N(min($na / 0.005, 1), 4),
            ]);
        }
        foreach ($dIds as $i => $dId) {
            $vId = DB::table('villages')->insertGetId(['district_id' => $dId, 'name' => 'Kel. ' . $capital . ' ' . ($i + 1)]);
            $pend = 8000 + (int) floor($rnd() * 15000);
            $luas = (int) floor($pend * 3.5 * $q);
            $naRT = $luas / $pend;
            DB::table('ruang_terbuka')->insert([
                'village_id' => $vId, 'year' => $Y, 'jumlah_penduduk_5plus' => $pend,
                'luas_m2' => $luas, 'nilai_aktual' => $N($naRT, 5), 'indeks' => $N(min($naRT / 3.5, 1), 4),
            ]);
            for ($k = 0; $k < 4; $k++) {
                $age = 18 + (int) floor($rnd() * 50);
                $rId = DB::table('respondents')->insertGetId([
                    'village_id' => $vId, 'year' => $Y, 'age' => $age,
                    'gender' => $rnd() < 0.5 ? 'L' : 'P', 'age_group' => self::ageGroup($age),
                ]);
                $sk = fn() => $N(1 + $rnd() * 4 * $q + 0.3, 2);
                $p = $sk();
                $s = $sk();
                $pr = $sk();
                $naL = ($p + $s + $pr) / 3;
                DB::table('literasi_fisik')->insert([
                    'respondent_id' => $rId, 'year' => $Y, 'pengetahuan' => $p, 'sikap' => $s,
                    'perilaku' => $pr, 'nilai_aktual' => $N($naL, 4), 'indeks' => $N(min($naL / 5, 1), 4),
                ]);
                DB::table('partisipasi')->insert([
                    'respondent_id' => $rId, 'year' => $Y,
                    'frekuensi' => 1 + (int) floor($rnd() * 6 * $q + 0.5),
                    'durasi' => 15 + (int) floor($rnd() * 105),
                    'intensitas' => 1 + (int) floor($rnd() * 3),
                ]);
                $vo2 = $N(24 + $rnd() * 26 * $q + 3, 2);
                DB::table('kebugaran')->insert([
                    'respondent_id' => $rId, 'year' => $Y, 'vo2max' => $vo2,
                    'kategori' => self::kebKategori($vo2), 'nilai_aktual' => $vo2,
                    'indeks' => $N(max(0, min(($vo2 - 20.1) / 32, 1)), 4),
                ]);
                $f = $sk();
                $ps = $sk();
                DB::table('kesehatan')->insert([
                    'respondent_id' => $rId, 'year' => $Y, 'fisik' => $f, 'psikis' => $ps,
                    'nilai_aktual' => $N(($f + $ps) / 2, 4), 'indeks' => $N(min((($f + $ps) / 2) / 5, 1), 4),
                ]);
                $rs = $sk();
                $ms = $sk();
                DB::table('perkembangan_personal')->insert([
                    'respondent_id' => $rId, 'year' => $Y, 'resiliensi' => $rs, 'modal_sosial' => $ms,
                    'nilai_aktual' => $N(($rs + $ms) / 2, 4), 'indeks' => $N(min((($rs + $ms) / 2) / 5, 1), 4),
                ]);
                $bb = (int) floor((100000 + $rnd() * 2000000) * $q);
                $bj = (int) floor((50000 + $rnd() * 500000) * $q);
                DB::table('ekonomi')->insert([
                    'respondent_id' => $rId, 'year' => $Y, 'belanja_barang' => $bb,
                    'belanja_jasa' => $bj, 'total_belanja' => $bb + $bj,
                    'kategori_belanja' => ($bb + $bj) > 1000000 ? 'Tinggi' : 'Rendah',
                ]);
            }
        }
        $em = (int) floor($rnd() * 22 * $q);
        $pr2 = (int) floor($rnd() * 16 * $q);
        $pz = (int) floor($rnd() * 12 * $q);
        $naPf = $em * 5 + $pr2 * 3 + $pz;
        DB::table('performa')->insert([
            'city_id' => $cityId, 'year' => $Y, 'medali_emas' => $em,
            'medali_perak' => $pr2, 'medali_perunggu' => $pz,
            'nilai_aktual' => $naPf, 'indeks' => $N(min($naPf / 200, 1), 4),
        ]);
    }
}
