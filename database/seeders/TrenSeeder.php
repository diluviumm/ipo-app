<?php

namespace Database\Seeders;

use App\Services\IpoCalculator;
use App\Services\SeedRandom;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Port seedTren.js — skala data 2024 ke 2020–2023 (faktor 0.68→0.92)
// + noise deterministik. Tahun yang sudah punya summary dilewati.
class TrenSeeder extends Seeder
{
    public const YEARS = [2020, 2021, 2022, 2023];
    public const BASE = 2024;

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
        $rows = 0;
        foreach (DB::table('provinces')->orderBy('id')->pluck('id') as $pid) {
            foreach (self::YEARS as $y) {
                $has = DB::table('ipo_summary')->where('province_id', $pid)->where('year', $y)->exists();
                if ($has) continue;
                $rows += $this->seedYear($pid, $y);
                IpoCalculator::full($pid, $y);
            }
        }
        $this->command->info("Tren 2020–2023 selesai: {$rows} baris.");
    }

    private function seedYear(int $pid, int $y): int
    {
        $N = [SeedRandom::class, 'n'];
        $B = self::BASE;
        $rows = 0;
        $f = 0.68 + ($y - 2020) * 0.08;
        $rnd = SeedRandom::mulberry32($pid * 100003 + $y);
        $nz = fn() => 0.97 + $rnd() * 0.06;

        $sdms = DB::table('sdm_olahraga as s')
            ->join('districts as d', 'd.id', '=', 's.district_id')
            ->join('cities as c', 'c.id', '=', 'd.city_id')
            ->where('c.province_id', $pid)->where('s.year', $B)
            ->select('s.district_id', 's.jumlah_penduduk_5plus', 's.jumlah_sdm')->get();
        foreach ($sdms as $s) {
            $jml = max(1, (int) round($s->jumlah_sdm * $f * $nz()));
            $na = $jml / $s->jumlah_penduduk_5plus;
            DB::table('sdm_olahraga')->insert([
                'district_id' => $s->district_id, 'year' => $y,
                'jumlah_penduduk_5plus' => $s->jumlah_penduduk_5plus, 'jumlah_sdm' => $jml,
                'nilai_aktual' => $N($na, 5), 'indeks' => $N(min($na / 0.005, 1), 4),
            ]);
            $rows++;
        }

        $rts = DB::table('ruang_terbuka as r')
            ->join('villages as v', 'v.id', '=', 'r.village_id')
            ->join('districts as d', 'd.id', '=', 'v.district_id')
            ->join('cities as c', 'c.id', '=', 'd.city_id')
            ->where('c.province_id', $pid)->where('r.year', $B)
            ->select('r.village_id', 'r.jumlah_penduduk_5plus', 'r.luas_m2')->get();
        foreach ($rts as $r) {
            $luas = max(100, (int) round($r->luas_m2 * $f * $nz()));
            $na = $luas / $r->jumlah_penduduk_5plus;
            DB::table('ruang_terbuka')->insert([
                'village_id' => $r->village_id, 'year' => $y,
                'jumlah_penduduk_5plus' => $r->jumlah_penduduk_5plus, 'luas_m2' => $luas,
                'nilai_aktual' => $N($na, 5), 'indeks' => $N(min($na / 3.5, 1), 4),
            ]);
            $rows++;
        }

        $resps = DB::table('literasi_fisik as lit')
            ->join('respondents as r', 'r.id', '=', 'lit.respondent_id')
            ->join('villages as v', 'v.id', '=', 'r.village_id')
            ->join('districts as d', 'd.id', '=', 'v.district_id')
            ->join('cities as c', 'c.id', '=', 'd.city_id')
            ->where('c.province_id', $pid)->where('lit.year', $B)
            ->distinct()->pluck('lit.respondent_id');

        $sc = function ($v) use ($f, $nz, $N) {
            return $N(min(5, max(1, 1 + ($v - 1) * $f * $nz())), 2);
        };

        foreach ($resps as $rid) {
            $L = DB::table('literasi_fisik')->where('respondent_id', $rid)->where('year', $B)->first();
            if (!$L) continue;
            $p = $sc($L->pengetahuan);
            $s = $sc($L->sikap);
            $pr = $sc($L->perilaku);
            $naL = ($p + $s + $pr) / 3;
            DB::table('literasi_fisik')->insert([
                'respondent_id' => $rid, 'year' => $y, 'pengetahuan' => $p, 'sikap' => $s,
                'perilaku' => $pr, 'nilai_aktual' => $N($naL, 4), 'indeks' => $N(min($naL / 5, 1), 4),
            ]);
            $rows++;

            $P = DB::table('partisipasi')->where('respondent_id', $rid)->where('year', $B)->first();
            if ($P) {
                DB::table('partisipasi')->insert([
                    'respondent_id' => $rid, 'year' => $y,
                    'frekuensi' => max(1, (int) round($P->frekuensi * $f * $nz())),
                    'durasi' => $P->durasi, 'intensitas' => $P->intensitas,
                ]);
                $rows++;
            }
            $K = DB::table('kebugaran')->where('respondent_id', $rid)->where('year', $B)->first();
            if ($K) {
                $vo2 = $N(min(60, max(20, 20.1 + ($K->vo2max - 20.1) * $f * $nz())), 2);
                DB::table('kebugaran')->insert([
                    'respondent_id' => $rid, 'year' => $y, 'vo2max' => $vo2,
                    'kategori' => self::kebKategori($vo2), 'nilai_aktual' => $vo2,
                    'indeks' => $N(max(0, min(($vo2 - 20.1) / 32, 1)), 4),
                ]);
                $rows++;
            }
            $Ks = DB::table('kesehatan')->where('respondent_id', $rid)->where('year', $B)->first();
            if ($Ks) {
                $fk = $sc($Ks->fisik);
                $pk = $sc($Ks->psikis);
                DB::table('kesehatan')->insert([
                    'respondent_id' => $rid, 'year' => $y, 'fisik' => $fk, 'psikis' => $pk,
                    'nilai_aktual' => $N(($fk + $pk) / 2, 4), 'indeks' => $N(min((($fk + $pk) / 2) / 5, 1), 4),
                ]);
                $rows++;
            }
            $PP = DB::table('perkembangan_personal')->where('respondent_id', $rid)->where('year', $B)->first();
            if ($PP) {
                $rs = $sc($PP->resiliensi);
                $ms = $sc($PP->modal_sosial);
                DB::table('perkembangan_personal')->insert([
                    'respondent_id' => $rid, 'year' => $y, 'resiliensi' => $rs, 'modal_sosial' => $ms,
                    'nilai_aktual' => $N(($rs + $ms) / 2, 4), 'indeks' => $N(min((($rs + $ms) / 2) / 5, 1), 4),
                ]);
                $rows++;
            }
            $E = DB::table('ekonomi')->where('respondent_id', $rid)->where('year', $B)->first();
            if ($E) {
                $bb = max(10000, (int) round($E->belanja_barang * $f * $nz()));
                $bj = max(5000, (int) round($E->belanja_jasa * $f * $nz()));
                DB::table('ekonomi')->insert([
                    'respondent_id' => $rid, 'year' => $y, 'belanja_barang' => $bb,
                    'belanja_jasa' => $bj, 'total_belanja' => $bb + $bj,
                    'kategori_belanja' => ($bb + $bj) > 1000000 ? 'Tinggi' : 'Rendah',
                ]);
                $rows++;
            }
        }

        $perfs = DB::table('performa as pf')
            ->join('cities as c', 'c.id', '=', 'pf.city_id')
            ->where('c.province_id', $pid)->where('pf.year', $B)
            ->select('pf.city_id', 'pf.medali_emas', 'pf.medali_perak', 'pf.medali_perunggu')->get();
        foreach ($perfs as $pf) {
            $em = (int) floor($pf->medali_emas * $f);
            $pr2 = (int) floor($pf->medali_perak * $f);
            $pz = (int) floor($pf->medali_perunggu * $f);
            $na = $em * 5 + $pr2 * 3 + $pz;
            DB::table('performa')->insert([
                'city_id' => $pf->city_id, 'year' => $y, 'medali_emas' => $em,
                'medali_perak' => $pr2, 'medali_perunggu' => $pz,
                'nilai_aktual' => $na, 'indeks' => $N(min($na / 200, 1), 4),
            ]);
            $rows++;
        }
        return $rows;
    }
}
