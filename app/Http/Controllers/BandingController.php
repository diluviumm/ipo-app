<?php

namespace App\Http\Controllers;

use App\Services\IpoCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BandingController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) ($request->query('year') ?: IpoCalculator::latestYear());
        $u = $request->user();
        $def = $u->province_id ? [(int) $u->province_id] : [1, 15];
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $request->query('p', $def)))));
        $ids = array_slice($ids, 0, 4);
        $provs = DB::table('provinces')->whereIn('id', $ids)->orderBy('name')->get();
        $dims = [];
        $labels = ['d1_sdm' => 'SDM', 'd2_ruang_terbuka' => 'Ruang', 'd3_literasi_fisik' => 'Literasi', 'd4_partisipasi' => 'Partisipasi', 'd5_kebugaran' => 'Bugar', 'd6_kesehatan' => 'Sehat', 'd7_perkembangan_personal' => 'Personal', 'd8_ekonomi' => 'Ekonomi', 'd9_performa' => 'Performa'];
        foreach ($provs as $p) {
            $r = IpoCalculator::full($p->id, $year);
            $skor = [];
            foreach ($labels as $k => $label) $skor[$k] = round((float) ($r[$k] ?? 0) * 100, 1);
            $dims[] = ['id' => $p->id, 'nama' => $p->name, 'skor' => $skor, 'ipo' => round((float) $r['ipo_score'] * 100, 2)];
        }
        if ($request->expectsJson()) return response()->json(['year' => $year, 'data' => $dims]);
        if ($request->query('ekspor') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('banding_pdf', ['year' => $year, 'dims' => $dims, 'labels' => $labels])->setPaper('a4', 'landscape');
            return $pdf->download("IPO-Banding-{$year}.pdf");
        }
        return view('banding', ['year' => $year, 'dims' => $dims, 'labels' => $labels, 'pilih' => $ids,
            'provinces' => DB::table('provinces')->orderBy('name')->get(), 'years' => range($year, 2020)]);
    }
}