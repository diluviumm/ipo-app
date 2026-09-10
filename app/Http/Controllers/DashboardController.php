<?php

namespace App\Http\Controllers;

use App\Services\IpoCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        $year = (int) ($request->query('year') ?: IpoCalculator::latestYear());
        $pid = $u->province_id ? (int) $u->province_id : (int) ($request->query('province_id') ?: 1);

        $summary = DB::table('ipo_summary')->where('province_id', $pid)->where('year', $year)->first();
        if (!$summary) {
            IpoCalculator::full($pid, $year);
            $summary = DB::table('ipo_summary')->where('province_id', $pid)->where('year', $year)->first();
        }
        $province = DB::table('provinces')->where('id', $pid)->first();

        $meta = [
            ['key' => 'd1_sdm', 'label' => 'SDM Olahraga'],
            ['key' => 'd2_ruang_terbuka', 'label' => 'Ruang Terbuka'],
            ['key' => 'd3_literasi_fisik', 'label' => 'Literasi Fisik'],
            ['key' => 'd4_partisipasi', 'label' => 'Partisipasi'],
            ['key' => 'd5_kebugaran', 'label' => 'Kebugaran Jasmani'],
            ['key' => 'd6_kesehatan', 'label' => 'Kesehatan'],
            ['key' => 'd7_perkembangan_personal', 'label' => 'Perkembangan Personal'],
            ['key' => 'd8_ekonomi', 'label' => 'Ekonomi'],
            ['key' => 'd9_performa', 'label' => 'Performa'],
        ];
        $dimensions = [];
        foreach ($meta as $m) {
            $v = $summary ? (float) ($summary->{$m['key']} ?? 0) : 0;
            $dimensions[] = ['key' => $m['key'], 'label' => $m['label'], 'value' => $v, 'display' => (int) round($v * 100)];
        }

        $counts = $this->scopedCounts($pid, $year);
        $trend = DB::table('ipo_summary')->select('year', 'ipo_score', 'kategori')
            ->where('province_id', $pid)->whereBetween('year', [$year - 4, $year])->orderBy('year')->get();
        $nasional = null;
        if (!$u->province_id) {
            $rows = DB::table('ipo_summary as s')->join('provinces as p', 'p.id', '=', 's.province_id')
                ->select('p.name', 's.ipo_score', 's.kategori')->where('s.year', $year)
                ->orderByDesc('s.ipo_score')->get();
            if (count($rows)) {
                $nasional = [
                    'rata' => round($rows->avg('ipo_score') * 100, 2),
                    'atas' => $rows->take(3),
                    'bawah' => $rows->reverse()->take(3),
                    'jml' => count($rows),
                ];
            }
        } else {
            $semua = DB::table('ipo_summary')->where('year', $year)->orderByDesc('ipo_score')->pluck('province_id');
            $pos = $semua->search($pid);
            if ($pos !== false && count($semua)) {
                $nasional = [
                    'rata' => round((float) DB::table('ipo_summary')->where('year', $year)->avg('ipo_score') * 100, 2),
                    'atas' => [], 'bawah' => [],
                    'jml' => count($semua), 'peringkat' => $pos + 1,
                ];
            }
        }

        return view('dashboard', [
            'year' => $year, 'pid' => $pid,
            'provinceName' => $province->name ?? 'Semua Provinsi',
            'score' => $summary ? round((float) $summary->ipo_score * 100, 2) : 0,
            'kategori' => $summary->kategori ?? 'Belum Ada Data',
            'dimensions' => $dimensions,
            'totalRecords' => array_sum($counts), 'counts' => $counts,
            'trend' => $trend, 'nasional' => $nasional,
            'years' => $this->yearOptions(),
            'provinces' => $u->province_id ? [] : DB::table('provinces')->orderBy('name')->get(),
        ]);
    }

    private function scopedCounts(int $pid, int $y): array
    {
        $c = function (string $table, string $join) use ($pid, $y) {
            return DB::table("{$table} as x")->whereRaw($join, [$pid, $y])->count();
        };
        $jSdm = 'EXISTS (SELECT 1 FROM districts d JOIN cities c ON c.id = d.city_id WHERE d.id = x.district_id AND c.province_id = ? AND x.year = ?)';
        $jRuang = 'EXISTS (SELECT 1 FROM villages v JOIN districts d ON d.id = v.district_id JOIN cities c ON c.id = d.city_id WHERE v.id = x.village_id AND c.province_id = ? AND x.year = ?)';
        $jResp = 'EXISTS (SELECT 1 FROM respondents re JOIN villages v ON v.id = re.village_id JOIN districts d ON d.id = v.district_id JOIN cities c ON c.id = d.city_id WHERE re.id = x.respondent_id AND c.province_id = ? AND x.year = ?)';
        $jPerf = 'EXISTS (SELECT 1 FROM cities c WHERE c.id = x.city_id AND c.province_id = ? AND x.year = ?)';
        return [
            'sdm' => $c('sdm_olahraga', $jSdm), 'ruang' => $c('ruang_terbuka', $jRuang),
            'literasi' => $c('literasi_fisik', $jResp), 'partisipasi' => $c('partisipasi', $jResp),
            'kebugaran' => $c('kebugaran', $jResp), 'kesehatan' => $c('kesehatan', $jResp),
            'perkembangan' => $c('perkembangan_personal', $jResp), 'ekonomi' => $c('ekonomi', $jResp),
            'performa' => $c('performa', $jPerf),
        ];
    }

    private function yearOptions(): array
    {
        $tables = ['sdm_olahraga', 'ruang_terbuka', 'literasi_fisik', 'partisipasi', 'kebugaran', 'kesehatan', 'perkembangan_personal', 'ekonomi', 'performa', 'respondents'];
        $set = [];
        foreach ($tables as $t) {
            foreach (DB::table($t)->distinct()->pluck('year') as $y) $set[$y] = true;
        }
        $years = array_keys($set);
        rsort($years);
        return $years ?: [IpoCalculator::latestYear()];
    }
}
