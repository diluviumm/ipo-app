<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SistemController extends Controller
{
    public function index()
    {
        $db = config('database.connections.sqlite.database');
        $size = is_file($db) ? filesize($db) : 0;
        $tabel = ['audit_logs', 'notifikasi', 'ipo_summary', 'users'];
        $jml = [];
        foreach ($tabel as $t) {
            try { $jml[$t] = \Illuminate\Support\Facades\DB::table($t)->count(); }
            catch (\Throwable $e) { $jml[$t] = 0; }
        }
        try {
            $tahun = \Illuminate\Support\Facades\DB::table('ipo_summary')->selectRaw('DISTINCT year')->orderByDesc('year')->pluck('year');
            $terkunci = \Illuminate\Support\Facades\DB::table('tahun_terkunci')->pluck('year')->all();
        } catch (\Throwable $e) { $tahun = []; $terkunci = []; }
        $status = [
            'Laravel' => app()->version(),
            'PHP' => PHP_VERSION,
            'Lingkungan' => config('app.env'),
            'Zona waktu' => config('app.timezone'),
            'Pengguna' => $jml['users'] ?? 0,
            'Ringkasan IPO' => $jml['ipo_summary'] ?? 0,
            'Sampah' => $this->hitung('sampah'),
            'Riwayat masuk' => $this->hitung('login_logs'),
            'Ruang kosong DB' => $this->ruangKosong(),
        ];
        return view('sistem.index', ['size' => $size, 'jml' => $jml, 'tahun' => $tahun, 'terkunci' => $terkunci, 'status' => $status]);
    }

    private function hitung(string $t): int
    {
        try { return \Illuminate\Support\Facades\DB::table($t)->count(); }
        catch (\Throwable $e) { return 0; }
    }

    private function ruangKosong(): string
    {
        try {
            $db = \Illuminate\Support\Facades\DB::connection()->getPdo();
            $total = (int) $db->query('PRAGMA page_count')->fetchColumn();
            $bebas = (int) $db->query('PRAGMA freelist_count')->fetchColumn();
            if ($total <= 0) return '—';
            return number_format($bebas / $total * 100, 1, ',', '.') . '%';
        } catch (\Throwable $e) { return '—'; }
    }

    public function vakum(Request $request)
    {
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo()->exec('VACUUM');
        } catch (\Throwable $e) {
            return redirect('/sistem')->with('toast', ['type' => 'error', 'text' => __('Vakum gagal: basis data sedang dipakai.')]);
        }
        Audit::catat($request->user(), 'vakum', 'database', null, []);
        return redirect('/sistem')->with('toast', ['type' => 'success', 'text' => __('Basis data dirapikan.')]);
    }

    public function backup()
    {
        $db = config('database.connections.sqlite.database');
        abort_if(!is_file($db), 404, 'Basis data tidak ditemukan');
        return response()->download($db, 'ipo-backup-' . date('Ymd-His') . '.sqlite');
    }

    public function kunci(Request $request, int $year)
    {
        DB::table('tahun_terkunci')->updateOrInsert(['year' => $year]);
        Audit::catat($request->user(), 'kunci', 'tahun_terkunci', $year, []);
        return redirect('/sistem')->with('toast', ['type' => 'success', 'text' => "Tahun {$year} dikunci."]);
    }

    public function buka(Request $request, int $year)
    {
        DB::table('tahun_terkunci')->where('year', $year)->delete();
        Audit::catat($request->user(), 'buka', 'tahun_terkunci', $year, []);
        return redirect('/sistem')->with('toast', ['type' => 'success', 'text' => "Tahun {$year} dibuka."]);
    }
}