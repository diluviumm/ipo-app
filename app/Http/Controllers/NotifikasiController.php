<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        $q = DB::table('notifikasi as n')->join('provinces as p', 'p.id', '=', 'n.province_id')
            ->select('n.*', 'p.name as province_name');
        if ($u->province_id) $q->where('n.province_id', $u->province_id);
        $rows = $q->orderByDesc('n.id')->limit(30)->get();
        if ($request->expectsJson()) return response()->json(['data' => $rows]);
        return view('notifikasi.index', ['rows' => $rows]);
    }

    public function baca(Request $request, int $id)
    {
        $q = DB::table('notifikasi')->where('id', $id);
        if ($request->user()->province_id) $q->where('province_id', $request->user()->province_id);
        $q->update(['dibaca' => true]);
        return redirect('/notifikasi');
    }

    public function hapus(Request $request, int $id)
    {
        $q = DB::table('notifikasi')->where('id', $id);
        if ($request->user()->province_id) $q->where('province_id', $request->user()->province_id);
        $q->delete();
        return redirect('/notifikasi')->with('toast', ['type' => 'success', 'text' => __('Notifikasi dihapus.')]);
    }
}