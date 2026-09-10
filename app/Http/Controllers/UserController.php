<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $boleh = ['username', 'full_name', 'role'];
        $sort = in_array($request->query('sort'), $boleh, true) ? $request->query('sort') : 'id';
        $dir = strtolower((string) $request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $users = DB::table('users as u')->leftJoin('provinces as p', 'p.id', '=', 'u.province_id')
            ->select('u.id', 'u.username', 'u.full_name', 'u.role', 'u.province_id', 'p.name as province_name')
            ->when($q !== '', fn($qq) => $qq->where(fn($w) => $w->where('u.username', 'like', "%{$q}%")->orWhere('u.full_name', 'like', "%{$q}%")))
            ->orderBy("u.{$sort}", $dir)->paginate(20)->withQueryString();
        if ($request->expectsJson()) return response()->json(['users' => $users->items(), 'total' => $users->total()]);
        return view('users.index', ['users' => $users, 'q' => $q, 'sort' => $sort, 'dir' => $dir, 'tutup' => \App\Support\Pengaturan::registerTutup()]);
    }

    public function toggleRegister(Request $request)
    {
        $baru = \App\Support\Pengaturan::registerTutup() ? '0' : '1';
        \App\Support\Pengaturan::set('register_tutup', $baru);
        Audit::catat($request->user(), 'ubah', 'pengaturan', null, ['register_tutup' => $baru]);
        $teks = $baru === '1' ? 'Pendaftaran ditutup.' : 'Pendaftaran dibuka.';
        return redirect('/users')->with('toast', ['type' => 'success', 'text' => $teks]);
    }

    public function ekspor(Request $request)
    {
        $csv = "\xEF\xBB\xBFusername;nama;peran;provinsi\n";
        $rows = DB::table('users as u')->leftJoin('provinces as p', 'p.id', '=', 'u.province_id')
            ->select('u.username', 'u.full_name', 'u.role', 'p.name as province_name')
            ->orderBy('u.username')->limit(5000)->get();
        foreach ($rows as $r) {
            $csv .= implode(';', \App\Support\Csv::baris([$r->username, '"' . str_replace('"', '""', $r->full_name) . '"', $r->role, $r->province_name ?? '-'])) . "\n";
        }
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="users-' . date('Ymd') . '.csv"',
        ]);
    }

    public function create()
    {
        return view('users.form', ['row' => null, 'provinces' => DB::table('provinces')->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            ['username' => 'required|min:3|max:50|unique:users,username', 'password' => 'required|min:6', 'full_name' => 'required|max:100', 'role' => 'nullable|in:admin,operator,user', 'province_id' => 'nullable|integer|exists:provinces,id'],
            ['username.required' => 'Username wajib diisi', 'username.unique' => 'Username sudah digunakan', 'password.required' => 'Password wajib diisi', 'full_name.required' => 'Nama lengkap wajib diisi']
        );
        if ($tolak = \App\Support\PasswordKuat::cek($data['password'])) {
            if ($request->expectsJson()) return response()->json(['error' => $tolak], 422);
            return back()->withErrors(['password' => $tolak])->withInput();
        }
        $user = User::create([
            'username' => $data['username'], 'password_hash' => Hash::make($data['password']),
            'full_name' => $data['full_name'], 'role' => $data['role'] ?? 'user', 'province_id' => $data['province_id'] ?? null,
        ]);
        if ($request->expectsJson()) return response()->json(['id' => $user->id, 'message' => __('User berhasil dibuat')]);
        Audit::catat($request->user(), 'tambah', 'users', $user->id, ['username' => $user->username, 'role' => $user->role]);
        return redirect('/users')->with('toast', ['type' => 'success', 'text' => __('User berhasil dibuat.')]);
    }

    public function edit(int $id)
    {
        $row = DB::table('users')->where('id', $id)->first();
        abort_if(!$row, 404, 'User tidak ditemukan');
        return view('users.form', ['row' => (array) $row, 'provinces' => DB::table('provinces')->orderBy('name')->get()]);
    }

    public function update(Request $request, int $id)
    {
        $existing = DB::table('users')->where('id', $id)->first();
        if (!$existing) {
            if ($request->expectsJson()) return response()->json(['error' => __('User tidak ditemukan')], 404);
            abort(404, __('User tidak ditemukan'));
        }
        $data = $request->validate(
            ['full_name' => 'required|max:100', 'role' => 'required|in:admin,operator,user', 'province_id' => 'nullable|integer|exists:provinces,id', 'password' => 'nullable|min:6'],
            ['full_name.required' => 'Nama lengkap wajib diisi']
        );
        $upd = ['full_name' => $data['full_name'], 'role' => $data['role'], 'province_id' => $data['province_id'] ?? null];
        if (!empty($data['password'])) {
            if ($tolak = \App\Support\PasswordKuat::cek($data['password'])) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => $tolak], 422);
                }
                return back()->withErrors(['password' => $tolak])->withInput();
            }
            $upd['password_hash'] = Hash::make($data['password']);
        }
        DB::table('users')->where('id', $id)->update($upd);
        Audit::catat($request->user(), 'ubah', 'users', $id, ['lama' => (array) $existing, 'baru' => $upd]);
        if ($request->expectsJson()) return response()->json(['message' => __('User berhasil diperbarui')]);
        return redirect('/users')->with('toast', ['type' => 'success', 'text' => __('User berhasil diperbarui.')]);
    }

    public function destroy(Request $request, int $id)
    {
        if ((int) $id === (int) $request->user()->id) {
            if ($request->expectsJson()) return response()->json(['error' => __('Tidak dapat menghapus akun sendiri')], 400);
            return back()->with('toast', ['type' => 'error', 'text' => __('Tidak dapat menghapus akun sendiri.')]);
        }
        $target = DB::table('users')->where('id', $id)->first();
        $n = DB::table('users')->where('id', $id)->delete();
        Audit::catat($request->user(), 'hapus', 'users', $id, ['username' => $target->username ?? null]);
        if (!$n) {
            if ($request->expectsJson()) return response()->json(['error' => __('User tidak ditemukan')], 404);
            abort(404, __('User tidak ditemukan'));
        }
        if ($request->expectsJson()) return response()->json(['message' => __('User berhasil dihapus')]);
        return redirect('/users')->with('toast', ['type' => 'success', 'text' => __('User berhasil dihapus.')]);
    }

    public function tokenReset(Request $request, int $id)
    {
        $token = bin2hex(random_bytes(8));
        DB::table('reset_tokens')->insert([
            'user_id' => $id, 'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
        ]);
        Audit::catat($request->user(), 'reset', 'users', $id, []);
        return redirect('/users')->with('toast', ['type' => 'success', 'text' => __('Token reset (1 jam):') . " {$token}"]);
    }
}
