<?php

namespace App\Http\Controllers;

use App\Services\IpoCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GraphController extends Controller
{
    public function show(Request $request)
    {
        $u = $request->user();
        $year = (int) ($request->query('year') ?: IpoCalculator::latestYear());
        $pid = $u->province_id ? (int) $u->province_id : (int) ($request->query('province_id') ?: 1);
        $result = IpoCalculator::full($pid, $year);
        $province = DB::table('provinces')->where('id', $pid)->first();
        $trendRows = DB::table('ipo_summary')->select('year', 'ipo_score', 'kategori')
            ->where('province_id', $pid)->orderByDesc('year')->limit(5)->get()->reverse()->values();
        $trend = $trendRows->map(fn($t) => ['year' => $t->year, 'score' => (int) round($t->ipo_score * 100), 'kategori' => $t->kategori])->all();

        return view('graph', [
            'year' => $year, 'pid' => $pid,
            'provinceName' => $province->name ?? 'Semua Provinsi',
            'trend' => $trend,
            'years' => $this->yearOptions(),
            'provinces' => $u->province_id ? [] : DB::table('provinces')->orderBy('name')->get(),
        ]);
    }

    private function yearOptions(): array
    {
        $set = [];
        foreach (['ipo_summary'] as $t) {
            foreach (DB::table($t)->distinct()->pluck('year') as $y) $set[$y] = true;
        }
        $years = array_keys($set);
        rsort($years);
        return $years ?: [IpoCalculator::latestYear()];
    }
}
