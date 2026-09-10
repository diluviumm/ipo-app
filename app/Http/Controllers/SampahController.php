<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use App\Support\Sampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SampahController extends Controller
{
    public function index(Request $request)
    {
        $rows = DB::table('sampah')->orderByDesc('id')->paginate(20);
        if ($request->expectsJson()) return response()->json($rows);
        return view('sampah.index', ['rows' => $rows]);
    }

    public function pulih(Request $request, int $id)
    {
        if (!Sampah::pulihkan($request->user(), $id)) {
            return back()->with('toast', ['type' => 'error', 'text' => __('Data sampah tidak ditemukan.')]);
        }
        return redirect('/sampah')->with('toast', ['type' => 'success', 'text' => __('Data dipulihkan.')]);
    }

    public function hapus(Request $request, int $id)
    {
        $s = DB::table('sampah')->where('id', $id)->first();
        if ($s) {
            DB::table('sampah')->where('id', $id)->delete();
            Audit::catat($request->user(), 'hapus-permanen', $s->tabel, $s->row_id, ['dim' => $s->dim]);
        }
        return redirect('/sampah')->with('toast', ['type' => 'success', 'text' => __('Data dihapus permanen.')]);
    }

    public function kosongkan(Request $request)
    {
        $n = DB::table('sampah')
            ->where('created_at', '<', date('Y-m-d H:i:s', time() - 30 * 86400))
            ->delete();
        Audit::catat($request->user(), 'bersih', 'sampah', null, ['dihapus' => $n]);
        $teks = $n > 0 ? "{$n} sampah lama dibuang permanen." : 'Tidak ada sampah lebih tua dari 30 hari.';
        return redirect('/sampah')->with('toast', ['type' => 'success', 'text' => $teks]);
    }
}
