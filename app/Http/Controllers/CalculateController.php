<?php

namespace App\Http\Controllers;

use App\Services\IpoCalculator;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalculateController extends Controller
{
    public function show(Request $request)
    {
        $u = $request->user();
        $year = (int) ($request->query('year') ?: IpoCalculator::latestYear());
        $pid = $u->province_id ? (int) $u->province_id : (int) ($request->query('province_id') ?: 1);
        $result = IpoCalculator::full($pid, $year);
        $province = DB::table('provinces')->where('id', $pid)->first();

        $labels = [
            'd1_sdm' => 'SDM Olahraga', 'd2_ruang_terbuka' => 'Ruang Terbuka',
            'd3_literasi_fisik' => 'Literasi Fisik', 'd4_partisipasi' => 'Partisipasi',
            'd5_kebugaran' => 'Kebugaran Jasmani', 'd6_kesehatan' => 'Kesehatan',
            'd7_perkembangan_personal' => 'Perkembangan Personal', 'd8_ekonomi' => 'Ekonomi',
            'd9_performa' => 'Performa',
        ];
        $dims = [];
        foreach ($labels as $k => $label) {
            $dims[] = ['label' => $label, 'display' => (int) round(($result[$k] ?? 0) * 100)];
        }

        return view('calculate', [
            'year' => $year, 'pid' => $pid,
            'provinceName' => $province->name ?? 'Semua Provinsi',
            'score' => $result['display_score'], 'kategori' => $result['kategori'],
            'dims' => $dims, 'tanpaData' => $result['_tanpaData'] ?? false,
            'years' => $this->yearOptions(),
            'provinces' => $u->province_id ? [] : DB::table('provinces')->orderBy('name')->get(),
        ]);
    }

    public function recalculate(Request $request)
    {
        $u = $request->user();
        $year = (int) ($request->query('year') ?: IpoCalculator::latestYear());
        $pid = $u->province_id ? (int) $u->province_id : (int) ($request->query('province_id') ?: 1);
        $lama = (float) (DB::table('ipo_summary')->where('province_id', $pid)->where('year', $year)->value('ipo_score') ?? 0);
        IpoCalculator::full($pid, $year);
        $baru = (float) (DB::table('ipo_summary')->where('province_id', $pid)->where('year', $year)->value('ipo_score') ?? 0);
        if (abs($baru - $lama) * 100 >= 3) {
            DB::table('notifikasi')->insert(['province_id' => $pid, 'year' => $year, 'skor_lama' => round($lama * 100, 2), 'skor_baru' => round($baru * 100, 2)]);
        }
        Audit::catat($u, 'hitung', 'ipo_summary', null, ['province_id' => $pid, 'year' => $year]);
        if ($request->expectsJson()) return response()->json(['message' => __('Indeks berhasil dihitung ulang')]);
        return redirect("/hitung?year={$year}&province_id={$pid}")->with('toast', ['type' => 'success', 'text' => 'Indeks berhasil dihitung ulang.']);
    }

    public function history(Request $request, int $provinceId)
    {
        $u = $request->user();
        if ($u->province_id && (int) $u->province_id !== $provinceId) {
            if ($request->expectsJson()) return response()->json(['error' => __('Tidak bisa melihat riwayat provinsi lain')], 403);
            abort(403, __('Tidak bisa melihat riwayat provinsi lain'));
        }
        $data = DB::table('ipo_summary')->where('province_id', $provinceId)->orderBy('year')->get();
        if ($request->expectsJson()) return response()->json(['data' => $data]);
        return redirect('/grafik');
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
