<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetSesiAktif
{
    private const BATAS = ['admin' => 86400, 'operator' => 28800, 'user' => 28800];

    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        if ($u) {
            $mulai = (int) $request->session()->get('sesi_mulai', 0);
            if ($mulai <= 0) {
                $request->session()->put('sesi_mulai', time());
            } else {
                $maks = self::BATAS[$u->role] ?? 28800;
                if (time() - $mulai > $maks) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    if ($request->expectsJson()) return response()->json(['error' => 'Sesi kedaluwarsa, masuk lagi'], 401);
                    return redirect('/login')->withErrors(['username' => 'Sesi kedaluwarsa (batas peran), masuk lagi.']);
                }
            }
            $request->session()->put('aktif', time());
        }
        return $next($request);
    }
}
