<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

// Registry 10 kunci dimensi — port dimensions.js (config + scope + turunan).
class Dimensions
{
    public const CONFIG = [
        'sdm' => [
            'table' => 'sdm_olahraga', 'label' => 'SDM Olahraga',
            'fields' => ['district_id', 'year', 'jumlah_penduduk_5plus', 'jumlah_sdm'],
            'required' => ['district_id', 'year', 'jumlah_penduduk_5plus', 'jumlah_sdm'],
            'numerics' => ['jumlah_penduduk_5plus', 'jumlah_sdm'],
            'fk' => 'district_id',
        ],
        'ruang-terbuka' => [
            'table' => 'ruang_terbuka', 'label' => 'Ruang Terbuka',
            'fields' => ['village_id', 'year', 'jumlah_penduduk_5plus', 'luas_m2'],
            'required' => ['village_id', 'year', 'jumlah_penduduk_5plus', 'luas_m2'],
            'numerics' => ['jumlah_penduduk_5plus', 'luas_m2'],
            'fk' => 'village_id',
        ],
        'literasi-fisik' => [
            'table' => 'literasi_fisik', 'label' => 'Literasi Fisik',
            'fields' => ['respondent_id', 'year', 'pengetahuan', 'sikap', 'perilaku'],
            'required' => ['respondent_id', 'year', 'pengetahuan', 'sikap', 'perilaku'],
            'numerics' => ['pengetahuan', 'sikap', 'perilaku'],
            'fk' => 'respondent_id',
        ],
        'partisipasi' => [
            'table' => 'partisipasi', 'label' => 'Partisipasi',
            'fields' => ['respondent_id', 'year', 'frekuensi', 'durasi', 'intensitas'],
            'required' => ['respondent_id', 'year', 'frekuensi', 'durasi', 'intensitas'],
            'numerics' => ['frekuensi', 'durasi', 'intensitas'],
            'fk' => 'respondent_id',
        ],
        'kebugaran' => [
            'table' => 'kebugaran', 'label' => 'Kebugaran Jasmani',
            'fields' => ['respondent_id', 'year', 'vo2max'],
            'required' => ['respondent_id', 'year', 'vo2max'],
            'numerics' => ['vo2max'],
            'fk' => 'respondent_id',
        ],
        'kesehatan' => [
            'table' => 'kesehatan', 'label' => 'Kesehatan',
            'fields' => ['respondent_id', 'year', 'fisik', 'psikis'],
            'required' => ['respondent_id', 'year', 'fisik', 'psikis'],
            'numerics' => ['fisik', 'psikis'],
            'fk' => 'respondent_id',
        ],
        'perkembangan-personal' => [
            'table' => 'perkembangan_personal', 'label' => 'Perkembangan Personal',
            'fields' => ['respondent_id', 'year', 'resiliensi', 'modal_sosial'],
            'required' => ['respondent_id', 'year', 'resiliensi', 'modal_sosial'],
            'numerics' => ['resiliensi', 'modal_sosial'],
            'fk' => 'respondent_id',
        ],
        'ekonomi' => [
            'table' => 'ekonomi', 'label' => 'Ekonomi',
            'fields' => ['respondent_id', 'year', 'belanja_barang', 'belanja_jasa'],
            'required' => ['respondent_id', 'year', 'belanja_barang', 'belanja_jasa'],
            'numerics' => ['belanja_barang', 'belanja_jasa'],
            'fk' => 'respondent_id',
        ],
        'performa' => [
            'table' => 'performa', 'label' => 'Performa',
            'fields' => ['city_id', 'year', 'medali_emas', 'medali_perak', 'medali_perunggu'],
            'required' => ['city_id', 'year'],
            'numerics' => ['medali_emas', 'medali_perak', 'medali_perunggu'],
            'fk' => 'city_id',
        ],
        'responden' => [
            'table' => 'respondents', 'label' => 'Responden Survei',
            'fields' => ['village_id', 'year', 'age', 'gender', 'age_group'],
            'required' => ['village_id', 'year', 'age', 'gender', 'age_group'],
            'numerics' => ['age'],
            'fk' => 'village_id',
        ],
    ];

    public static function get(string $key): ?array
    {
        return self::CONFIG[$key] ?? null;
    }

    public static function keys(): array
    {
        return array_keys(self::CONFIG);
    }

    // Provinsi pemilik baris lewat rantai FK (district/city/village/respondent).
    public static function resolveProvince(string $dim, array $row): ?int
    {
        $id = static function ($v) {
            return ($v === null || $v === '' ? null : (int) $v);
        };
        if ($dim === 'sdm' && $id($row['district_id'] ?? null)) {
            return DB::table('districts as d')->join('cities as c', 'c.id', '=', 'd.city_id')
                ->where('d.id', $id($row['district_id']))->value('c.province_id');
        }
        if (in_array($dim, ['ruang-terbuka', 'responden'], true) && $id($row['village_id'] ?? null)) {
            return DB::table('villages as v')->join('districts as d', 'd.id', '=', 'v.district_id')
                ->join('cities as c', 'c.id', '=', 'd.city_id')
                ->where('v.id', $id($row['village_id']))->value('c.province_id');
        }
        if ($dim === 'performa' && $id($row['city_id'] ?? null)) {
            return DB::table('cities')->where('id', $id($row['city_id']))->value('province_id');
        }
        if ($id($row['respondent_id'] ?? null)) {
            return DB::table('respondents as r')->join('villages as v', 'v.id', '=', 'r.village_id')
                ->join('districts as d', 'd.id', '=', 'v.district_id')
                ->join('cities as c', 'c.id', '=', 'd.city_id')
                ->where('r.id', $id($row['respondent_id']))->value('c.province_id');
        }
        return null;
    }

    // true = boleh; false = operator menulis provinsi lain.
    public static function assertOwnProvince($user, string $dim, array $row): bool
    {
        if (!$user->province_id) return true;
        $owner = self::resolveProvince($dim, $row);
        return $owner === null || (int) $owner === (int) $user->province_id;
    }

    // Kolom turunan dari field mentah (rumus = services/calculator).
    public static function derived(string $dim, array $b): array
    {
        $num = fn($v) => ($v === null || $v === '' ? null : (float) $v);
        $out = [];
        switch ($dim) {
            case 'sdm':
                $pend = $num($b['jumlah_penduduk_5plus'] ?? null);
                $sdm = $num($b['jumlah_sdm'] ?? null);
                if ($pend && $sdm !== null) {
                    $na = $sdm / $pend;
                    $out = ['nilai_aktual' => $na, 'indeks' => min($na / 0.005, 1)];
                }
                break;
            case 'ruang-terbuka':
                $pend = $num($b['jumlah_penduduk_5plus'] ?? null);
                $luas = $num($b['luas_m2'] ?? null);
                if ($pend && $luas !== null) {
                    $na = $luas / $pend;
                    $out = ['nilai_aktual' => $na, 'indeks' => min($na / 3.5, 1)];
                }
                break;
            case 'literasi-fisik':
                $p = $num($b['pengetahuan'] ?? null);
                $s = $num($b['sikap'] ?? null);
                $pr = $num($b['perilaku'] ?? null);
                if ($p !== null && $s !== null && $pr !== null) {
                    $na = ($p + $s + $pr) / 3;
                    $out = ['nilai_aktual' => $na, 'indeks' => min($na / 5, 1)];
                }
                break;
            case 'kebugaran':
                $v = $num($b['vo2max'] ?? null);
                if ($v !== null) {
                    $out = [
                        'kategori' => \App\Services\IpoCalculator::kebugaranKategori($v),
                        'nilai_aktual' => $v, 'indeks' => max(0, min(($v - 20.1) / 32, 1)),
                    ];
                }
                break;
            case 'kesehatan':
                $f = $num($b['fisik'] ?? null);
                $ps = $num($b['psikis'] ?? null);
                if ($f !== null && $ps !== null) {
                    $na = ($f + $ps) / 2;
                    $out = ['nilai_aktual' => $na, 'indeks' => min($na / 5, 1)];
                }
                break;
            case 'perkembangan-personal':
                $r = $num($b['resiliensi'] ?? null);
                $m = $num($b['modal_sosial'] ?? null);
                if ($r !== null && $m !== null) {
                    $na = ($r + $m) / 2;
                    $out = ['nilai_aktual' => $na, 'indeks' => min($na / 5, 1)];
                }
                break;
            case 'ekonomi':
                if (isset($b['belanja_barang']) || isset($b['belanja_jasa'])) {
                    $total = ($num($b['belanja_barang'] ?? null) ?? 0) + ($num($b['belanja_jasa'] ?? null) ?? 0);
                    $out = ['total_belanja' => $total, 'kategori_belanja' => $total > 1000000 ? 'Tinggi' : 'Rendah'];
                }
                break;
            case 'performa':
                if (isset($b['medali_emas']) || isset($b['medali_perak']) || isset($b['medali_perunggu'])) {
                    $na = ($num($b['medali_emas'] ?? null) ?? 0) * 5
                        + ($num($b['medali_perak'] ?? null) ?? 0) * 3
                        + ($num($b['medali_perunggu'] ?? null) ?? 0);
                    $out = ['nilai_aktual' => $na, 'indeks' => min($na / 200, 1)];
                }
                break;
        }
        return $out;
    }
}
