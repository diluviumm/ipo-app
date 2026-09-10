<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

// Port 1:1 dari services/calculator.js — rumus identik agar skor sama.
class IpoCalculator
{
    public const CATEGORIES = [
        ['min' => 0, 'max' => 25, 'label' => 'Sangat Kurang'],
        ['min' => 26, 'max' => 50, 'label' => 'Kurang'],
        ['min' => 51, 'max' => 75, 'label' => 'Cukup'],
        ['min' => 76, 'max' => 100, 'label' => 'Baik'],
    ];

    public static function kategori(float $score): string
    {
        $display = $score * 100;
        foreach (self::CATEGORIES as $cat) {
            if ($display >= $cat['min'] && $display <= $cat['max']) {
                return $cat['label'];
            }
        }
        return 'Sangat Kurang';
    }

    public static function kategoriFromDisplay(?float $display): string
    {
        if ($display === null) return 'Belum Ada Data';
        if ($display >= 76) return 'Baik';
        if ($display >= 51) return 'Cukup';
        if ($display >= 26) return 'Kurang';
        return 'Sangat Kurang';
    }

    public static function kebugaranKategori(float $vo2max): string
    {
        if ($vo2max >= 51.2) return 'Unggul';
        if ($vo2max >= 44.3) return 'Baik Sekali';
        if ($vo2max >= 38.3) return 'Baik';
        if ($vo2max >= 32.0) return 'Sedang';
        if ($vo2max >= 26.8) return 'Kurang';
        return 'Kurang Sekali';
    }

    private static function respJoin(string $alias, string $yearCol): string
    {
        return "JOIN respondents r ON {$alias}.respondent_id = r.id "
            . 'JOIN villages v ON r.village_id = v.id '
            . 'JOIN districts d ON v.district_id = d.id '
            . 'JOIN cities c ON d.city_id = c.id ';
    }

    public static function provinceSDM(int $pid, int $year): float
    {
        $r = DB::selectOne(
            'SELECT AVG(s.indeks) v FROM sdm_olahraga s '
            . 'JOIN districts d ON s.district_id = d.id '
            . 'JOIN cities c ON d.city_id = c.id '
            . 'WHERE c.province_id = ? AND s.year = ?', [$pid, $year]);
        return (float) ($r->v ?? 0);
    }

    public static function provinceRuangTerbuka(int $pid, int $year): float
    {
        $r = DB::selectOne(
            'SELECT AVG(r.indeks) v FROM ruang_terbuka r '
            . 'JOIN villages v ON r.village_id = v.id '
            . 'JOIN districts d ON v.district_id = d.id '
            . 'JOIN cities c ON d.city_id = c.id '
            . 'WHERE c.province_id = ? AND r.year = ?', [$pid, $year]);
        return (float) ($r->v ?? 0);
    }

    public static function provinceLiterasi(int $pid, int $year): float
    {
        $r = DB::selectOne(
            'SELECT AVG(l.indeks) v FROM literasi_fisik l ' . self::respJoin('l', 'l.year')
            . 'WHERE c.province_id = ? AND l.year = ?', [$pid, $year]);
        return (float) ($r->v ?? 0);
    }

    public static function provincePartisipasi(int $pid, int $year): float
    {
        $r = DB::selectOne(
            'SELECT COUNT(CASE WHEN p.frekuensi >= 3 THEN 1 END) aktif, COUNT(*) total '
            . 'FROM partisipasi p ' . self::respJoin('p', 'p.year')
            . 'WHERE c.province_id = ? AND p.year = ?', [$pid, $year]);
        if (!$r || !$r->total) return 0;
        return $r->aktif / $r->total;
    }

    public static function provinceKebugaran(int $pid, int $year): float
    {
        $r = DB::selectOne(
            'SELECT AVG(k.indeks) v FROM kebugaran k ' . self::respJoin('k', 'k.year')
            . 'WHERE c.province_id = ? AND k.year = ?', [$pid, $year]);
        return (float) ($r->v ?? 0);
    }

    public static function provinceKesehatan(int $pid, int $year): float
    {
        $r = DB::selectOne(
            'SELECT AVG(k.indeks) v FROM kesehatan k ' . self::respJoin('k', 'k.year')
            . 'WHERE c.province_id = ? AND k.year = ?', [$pid, $year]);
        return (float) ($r->v ?? 0);
    }

    public static function provincePerkembangan(int $pid, int $year): float
    {
        $r = DB::selectOne(
            'SELECT AVG(pp.indeks) v FROM perkembangan_personal pp ' . self::respJoin('pp', 'pp.year')
            . 'WHERE c.province_id = ? AND pp.year = ?', [$pid, $year]);
        return (float) ($r->v ?? 0);
    }

    public static function provinceEkonomi(int $pid, int $year): float
    {
        $r = DB::selectOne(
            'SELECT AVG(e.total_belanja) v FROM ekonomi e ' . self::respJoin('e', 'e.year')
            . 'WHERE c.province_id = ? AND e.year = ?', [$pid, $year]);
        return min(((float) ($r->v ?? 0)) / 5000000, 1);
    }

    public static function provincePerforma(int $pid, int $year): float
    {
        $r = DB::selectOne(
            'SELECT SUM(p.medali_emas) emas, SUM(p.medali_perak) perak, SUM(p.medali_perunggu) perunggu '
            . 'FROM performa p JOIN cities c ON p.city_id = c.id '
            . 'WHERE c.province_id = ? AND p.year = ?', [$pid, $year]);
        if (!$r) return 0;
        $na = ($r->emas ?? 0) * 5 + ($r->perak ?? 0) * 3 + ($r->perunggu ?? 0);
        return min($na / 200, 1);
    }

    // Hitung penuh IPO satu provinsi+tahun. Tanpa data sumber → tanpa upsert.
    public static function full(int $pid, int $year): array
    {
        $dims = [
            'd1_sdm' => self::provinceSDM($pid, $year),
            'd2_ruang_terbuka' => self::provinceRuangTerbuka($pid, $year),
            'd3_literasi_fisik' => self::provinceLiterasi($pid, $year),
            'd4_partisipasi' => self::provincePartisipasi($pid, $year),
            'd5_kebugaran' => self::provinceKebugaran($pid, $year),
            'd6_kesehatan' => self::provinceKesehatan($pid, $year),
            'd7_perkembangan_personal' => self::provincePerkembangan($pid, $year),
            'd8_ekonomi' => self::provinceEkonomi($pid, $year),
            'd9_performa' => self::provincePerforma($pid, $year),
        ];
        $score = array_sum($dims) / 9;
        $result = array_merge(
            ['province_id' => $pid, 'year' => $year],
            $dims,
            [
                'ipo_score' => $score,
                'display_score' => round($score * 100, 2),
                'kategori' => self::kategori($score),
            ]
        );

        $adaData = count(array_filter($dims, fn($d) => $d)) > 0;
        if (!$adaData) {
            $result['_tanpaData'] = true;
            return $result;
        }

        DB::table('ipo_summary')->upsert(
            array_merge($dims, [
                'province_id' => $pid, 'year' => $year,
                'ipo_score' => $score, 'kategori' => self::kategori($score),
            ]),
            ['province_id', 'year']
        );
        return $result;
    }

    // Tahun terbaru yang punya data SUMBER (abaikan ipo_summary).
    public static function latestYear(): int
    {
        $max = 0;
        foreach (['sdm_olahraga', 'ruang_terbuka', 'literasi_fisik', 'partisipasi', 'kebugaran', 'kesehatan', 'perkembangan_personal', 'ekonomi', 'performa', 'respondents'] as $t) {
            try {
                $m = DB::table($t)->max('year');
                if ($m > $max) $max = $m;
            } catch (\Throwable $e) { /* abaikan */
            }
        }
        return $max ?: (int) date('Y');
    }
}
