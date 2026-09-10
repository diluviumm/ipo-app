<?php

namespace App\Http\Controllers;

use App\Support\Dimensions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CariController extends Controller
{
    public function cari(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $hasil = ['provinsi' => [], 'user' => [], 'menu' => []];
        if (mb_strlen($q) >= 2) {
            $u = $request->user();
            $hasil['provinsi'] = DB::table('provinces')->where('name', 'like', "%{$q}%")
                ->orderBy('name')->limit(10)->get();
            $menus = [
                ['sdm', 'SDM Olahraga'], ['ruang-terbuka', 'Fasilitas Olahraga'],
                ['literasi-fisik', 'Literasi Fisik'], ['partisipasi', 'Partisipasi Masyarakat'],
                ['kebugaran', 'Kebugaran Jasmani'], ['kesehatan', 'Kesehatan'],
                ['perkembangan-personal', 'Perkembangan Personal'], ['ekonomi', 'Ekonomi'],
                ['performa', 'Performa Olahraga'], ['responden', 'Responden Survei'],
            ];
            foreach ($menus as [$key, $label]) {
                if (mb_stripos($label, $q) !== false) $hasil['menu'][] = ['key' => $key, 'label' => $label];
            }
            if ($u->role === 'admin' && $u->province_id === null) {
                $hasil['user'] = DB::table('users')->where('username', 'like', "%{$q}%")
                    ->orWhere('full_name', 'like', "%{$q}%")->orderBy('username')->limit(10)->get();
            }
        }
        if ($request->expectsJson()) return response()->json($hasil);
        return view('cari.index', ['q' => $q, 'hasil' => $hasil]);
    }
}
