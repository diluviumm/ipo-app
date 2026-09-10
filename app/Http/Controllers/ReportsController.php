<?php

namespace App\Http\Controllers;

use App\Services\IpoCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        $tab = $request->query('tab', 'index');
        $year = (int) ($request->query('year') ?: IpoCalculator::latestYear());
        $pid = $u->province_id ? (int) $u->province_id : (int) ($request->query('province_id') ?: 1);
        $province = DB::table('provinces')->where('id', $pid)->first();

        $data = ['tab' => $tab, 'year' => $year, 'pid' => $pid,
            'provinceName' => $province->name ?? 'Semua Provinsi',
            'provinces' => $u->province_id ? [] : DB::table('provinces')->orderBy('name')->get(),
            'years' => $this->yearOptions()];

        if ($tab === 'index') {
            $row = DB::table('ipo_summary as ipo')->join('provinces as p', 'p.id', '=', 'ipo.province_id')
                ->select('ipo.*', 'p.name as province_name')->where('ipo.province_id', $pid)->where('ipo.year', $year)->first();
            if (!$row) {
                IpoCalculator::full($pid, $year);
                $row = DB::table('ipo_summary as ipo')->join('provinces as p', 'p.id', '=', 'ipo.province_id')
                    ->select('ipo.*', 'p.name as province_name')->where('ipo.province_id', $pid)->where('ipo.year', $year)->first();
            }
            if (!$row) abort(404, __('Data tidak ditemukan'));
            $data['row'] = $row;
            $data['score'] = round((float) $row->ipo_score * 100, 2);
        } elseif ($tab === 'ranking') {
            $data['ranking'] = DB::table('ipo_summary as ipo')->join('provinces as p', 'p.id', '=', 'ipo.province_id')
                ->select('ipo.province_id', 'p.name as province_name', 'ipo.ipo_score', 'ipo.kategori')
                ->where('ipo.year', $year)
                ->when($request->query('q'), fn($qq, $qqq) => $qq->where('p.name', 'like', "%{$qqq}%"));
            $urut = $request->query('urut', 'skor');
            if ($urut === 'nama') $data['ranking'] = $data['ranking']->orderBy('p.name')->get();
            else $data['ranking'] = $data['ranking']->orderByDesc('ipo.ipo_score')->get();
            $data['rq'] = trim((string) $request->query('q', ''));
            $data['urut'] = $urut;
        } elseif ($tab === 'trend') {
            if ($u->province_id || $request->query('province_id')) {
                $data['trend'] = DB::table('ipo_summary')->select('year', 'ipo_score', 'kategori')
                    ->where('province_id', $pid)->orderBy('year')->get();
                $data['trendLabel'] = $data['provinceName'];
            } else {
                $data['trend'] = DB::table('ipo_summary')->select('year', DB::raw('AVG(ipo_score) as ipo_score'))
                    ->groupBy('year')->orderBy('year')->get();
                $data['trendLabel'] = 'Nasional (rata-rata)';
            }
        } else {
            $q = DB::table('ipo_summary as ipo')->join('provinces as p', 'p.id', '=', 'ipo.province_id')
                ->select('ipo.*', 'p.name as province_name')->where('ipo.year', $year);
            if ($u->province_id) $q->where('ipo.province_id', $u->province_id);
            elseif ($request->query('province_id')) $q->where('ipo.province_id', $request->query('province_id'));
            $data['dims'] = $q->orderBy('p.name')->get();
        }

        if ($request->expectsJson()) return response()->json($data);
        return view('reports', $data);
    }

    public function pdf(Request $request)
    {
        $u = $request->user();
        $tab = $request->query('tab', 'index');
        $year = (int) ($request->query('year') ?: IpoCalculator::latestYear());
        $pid = $u->province_id ? (int) $u->province_id : (int) ($request->query('province_id') ?: 1);

        if ($tab === 'ranking') {
            $rows = DB::table('ipo_summary as ipo')->join('provinces as p', 'p.id', '=', 'ipo.province_id')
                ->select('p.name as province_name', 'ipo.ipo_score', 'ipo.kategori')
                ->where('ipo.year', $year)->orderByDesc('ipo.ipo_score')->get();
            $pdf = Pdf::loadView('reports_pdf_ranking', ['rows' => $rows, 'year' => $year]);
            return $pdf->download("IPO-Laporan-ranking-{$year}.pdf");
        }

        $row = DB::table('ipo_summary as ipo')->join('provinces as p', 'p.id', '=', 'ipo.province_id')
            ->select('ipo.*', 'p.name as province_name')->where('ipo.province_id', $pid)->where('ipo.year', $year)->first();
        if (!$row) abort(404, __('Data tidak ditemukan'));
        $labels = ['d1_sdm' => 'SDM Olahraga', 'd2_ruang_terbuka' => 'Ruang Terbuka', 'd3_literasi_fisik' => 'Literasi Fisik', 'd4_partisipasi' => 'Partisipasi', 'd5_kebugaran' => 'Kebugaran Jasmani', 'd6_kesehatan' => 'Kesehatan', 'd7_perkembangan_personal' => 'Perkembangan Personal', 'd8_ekonomi' => 'Ekonomi', 'd9_performa' => 'Performa'];
        $pdf = Pdf::loadView('reports_pdf_index', ['row' => $row, 'year' => $year, 'labels' => $labels, 'score' => round((float) $row->ipo_score * 100, 2)]);
        return $pdf->download("IPO-Laporan-index-{$year}.pdf");
    }

    public function csv(Request $request)
    {
        $u = $request->user();
        $tab = $request->query('tab', 'index');
        $year = (int) ($request->query('year') ?: IpoCalculator::latestYear());
        $pid = $u->province_id ? (int) $u->province_id : (int) ($request->query('province_id') ?: 1);

        [$head, $rows] = $this->dataLaporan($u, $tab, $year, $pid);

        return response()->streamDownload(function () use ($head, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, \App\Support\Csv::baris($head), ';');
            foreach ($rows as $r) fputcsv($out, \App\Support\Csv::baris($r), ';');
            fclose($out);
        }, "IPO-Laporan-{$tab}-{$year}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function xlsx(Request $request)
    {
        $u = $request->user();
        $tab = $request->query('tab', 'index');
        $year = (int) ($request->query('year') ?: IpoCalculator::latestYear());
        $pid = $u->province_id ? (int) $u->province_id : (int) ($request->query('province_id') ?: 1);
        [$head, $rows] = $this->dataLaporan($u, $tab, $year, $pid);

        $wb = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $ws = $wb->getActiveSheet();
        $ws->setTitle(substr("IPO {$tab} {$year}", 0, 31));
        $ws->fromArray([\App\Support\Csv::baris($head)], null, 'A1');
        $ws->fromArray(array_map([\App\Support\Csv::class, 'baris'], $rows), null, 'A2');
        $ws->getStyle('A1:' . $ws->getHighestColumn() . '1')->getFont()->setBold(true);
        foreach (range('A', $ws->getHighestColumn()) as $kol) $ws->getColumnDimension($kol)->setAutoSize(true);
        $path = tempnam(sys_get_temp_dir(), 'ipo') . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($wb))->save($path);
        return response()->download($path, "IPO-Laporan-{$tab}-{$year}.xlsx")->deleteFileAfterSend(true);
    }

    private function dataLaporan($u, string $tab, int $year, int $pid): array
    {
        $head = [];
        $rows = [];
        if ($tab === 'ranking') {
            $head = ['No', 'Provinsi', 'Skor', 'Kategori'];
            $n = 0;
            foreach (DB::table('ipo_summary as ipo')->join('provinces as p', 'p.id', '=', 'ipo.province_id')
                ->select('p.name as province_name', 'ipo.ipo_score', 'ipo.kategori')
                ->where('ipo.year', $year)->orderByDesc('ipo.ipo_score')->get() as $r) {
                $rows[] = [++$n, $r->province_name, round((float) $r->ipo_score * 100, 2), $r->kategori];
            }
        } elseif ($tab === 'trend') {
            $head = ['Tahun', 'Skor', 'Kategori'];
            $q = DB::table('ipo_summary')->select('year', 'ipo_score', 'kategori')->where('province_id', $pid)->orderBy('year');
            foreach ($q->get() as $r) $rows[] = [$r->year, round((float) $r->ipo_score * 100, 2), $r->kategori];
        } elseif ($tab === 'dimensions') {
            $head = ['Provinsi', 'SDM', 'Ruang Terbuka', 'Literasi', 'Partisipasi', 'Kebugaran', 'Kesehatan', 'Personal', 'Ekonomi', 'Performa', 'Skor'];
            $q = DB::table('ipo_summary as ipo')->join('provinces as p', 'p.id', '=', 'ipo.province_id')
                ->select('ipo.*', 'p.name as province_name')->where('ipo.year', $year);
            if ($u->province_id) $q->where('ipo.province_id', $u->province_id);
            foreach ($q->orderBy('p.name')->get() as $r) {
                $rows[] = [$r->province_name, $this->pct($r->d1_sdm), $this->pct($r->d2_ruang_terbuka), $this->pct($r->d3_literasi_fisik), $this->pct($r->d4_partisipasi), $this->pct($r->d5_kebugaran), $this->pct($r->d6_kesehatan), $this->pct($r->d7_perkembangan_personal), $this->pct($r->d8_ekonomi), $this->pct($r->d9_performa), $this->pct($r->ipo_score)];
            }
        } else {
            $row = DB::table('ipo_summary as ipo')->join('provinces as p', 'p.id', '=', 'ipo.province_id')
                ->select('ipo.*', 'p.name as province_name')->where('ipo.province_id', $pid)->where('ipo.year', $year)->first();
            if (!$row) abort(404, __('Data tidak ditemukan'));
            $head = ['Dimensi', 'Skor'];
            $labels = ['d1_sdm' => 'SDM Olahraga', 'd2_ruang_terbuka' => 'Ruang Terbuka', 'd3_literasi_fisik' => 'Literasi Fisik', 'd4_partisipasi' => 'Partisipasi', 'd5_kebugaran' => 'Kebugaran Jasmani', 'd6_kesehatan' => 'Kesehatan', 'd7_perkembangan_personal' => 'Perkembangan Personal', 'd8_ekonomi' => 'Ekonomi', 'd9_performa' => 'Performa'];
            foreach ($labels as $k => $label) $rows[] = [$label, $this->pct($row->$k)];
            $rows[] = ['IPO ' . $row->province_name . ' ' . $year, $this->pct($row->ipo_score)];
        }

        return [$head, $rows];
    }

    private function pct($v): float
    {
        return round((float) $v * 100, 2);
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
