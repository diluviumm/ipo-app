<?php

namespace App\Http\Controllers;

use App\Services\IpoCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function provinsi()
    {
        return response()->json(['data' => DB::table('provinces')->select('id', 'name')->orderBy('name')->get()]);
    }

    public function ranking(Request $request)
    {
        $year = (int) ($request->query('year') ?: IpoCalculator::latestYear());
        $rows = DB::table('ipo_summary as ipo')
            ->join('provinces as p', 'p.id', '=', 'ipo.province_id')
            ->select('p.name as provinsi', 'ipo.ipo_score', 'ipo.kategori')
            ->where('ipo.year', $year)->orderByDesc('ipo.ipo_score')->get()
            ->map(fn($r) => ['provinsi' => $r->provinsi, 'skor' => round((float) $r->ipo_score * 100, 2), 'kategori' => $r->kategori]);
        return response()->json(['tahun' => $year, 'data' => $rows]);
    }

    public function tren(int $province)
    {
        $rows = DB::table('ipo_summary')->select('year as tahun', 'ipo_score', 'kategori')
            ->where('province_id', $province)->orderBy('year')->get()
            ->map(fn($r) => ['tahun' => $r->tahun, 'skor' => round((float) $r->ipo_score * 100, 2), 'kategori' => $r->kategori]);
        return response()->json(['province_id' => $province, 'data' => $rows]);
    }
}