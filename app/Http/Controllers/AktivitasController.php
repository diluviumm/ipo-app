<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AktivitasController extends Controller
{
    public function index(Request $request)
    {
        $logs = DB::table('audit_logs')
            ->when($request->query('aksi'), fn($q, $a) => $q->where('aksi', $a))
            ->when($request->query('q'), fn($q, $s) => $q->where('username', 'like', "%{$s}%"))
            ->when($request->query('dari'), fn($q, $d) => $q->where('created_at', '>=', $d . ' 00:00:00'))
            ->when($request->query('sampai'), fn($q, $d) => $q->where('created_at', '<=', $d . ' 23:59:59'))
            ->orderByDesc('id')->paginate(20)->withQueryString();
        if ($request->expectsJson()) return response()->json($logs);
        $masuk = DB::table('login_logs')->orderByDesc('id')->limit(30)->get();
        return view('aktivitas.index', ['logs' => $logs, 'masuk' => $masuk]);
    }

    public function ekspor(Request $request)
    {
        $rows = DB::table('audit_logs')
            ->when($request->query('aksi'), fn($q, $a) => $q->where('aksi', $a))
            ->when($request->query('q'), fn($q, $s) => $q->where('username', 'like', "%{$s}%"))
            ->when($request->query('dari'), fn($q, $d) => $q->where('created_at', '>=', $d . ' 00:00:00'))
            ->when($request->query('sampai'), fn($q, $d) => $q->where('created_at', '<=', $d . ' 23:59:59'))
            ->orderByDesc('id')->limit(5000)->get();
        $csv = "\xEF\xBB\xBFwaktu;pengguna;aksi;tabel;baris_id;detail\n";
        foreach ($rows as $l) {
            $csv .= implode(';', \App\Support\Csv::baris([
                $l->created_at, $l->username ?? '', $l->aksi, $l->tabel, $l->row_id ?? '',
                '"' . str_replace('"', '""', substr((string) ($l->detail ?? ''), 0, 500)) . '"',
            ])) . "\n";
        }
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="aktivitas-' . date('Ymd') . '.csv"',
        ]);
    }

    public function bersih(Request $request)
    {
        $n = DB::table('audit_logs')
            ->where('created_at', '<', date('Y-m-d H:i:s', time() - 90 * 86400))
            ->delete();
        $m = DB::table('notifikasi')
            ->where('created_at', '<', date('Y-m-d H:i:s', time() - 90 * 86400))
            ->delete();
        Audit::catat($request->user(), 'bersih', 'audit_logs', null, ['dihapus' => $n, 'notifikasi' => $m]);
        if ($request->expectsJson()) return response()->json(['dihapus' => $n, 'notifikasi' => $m]);
        $teks = ($n + $m) > 0 ? __('Log lama dibersihkan:') . " {$n} audit + {$m} " . __('notifikasi.') : __('Tidak ada log lebih tua dari 90 hari.');
        return redirect('/aktivitas')->with('toast', ['type' => 'success', 'text' => $teks]);
    }
}