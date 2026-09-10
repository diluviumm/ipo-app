<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// Para admin & operator boleh lewat; user biasa ditolak 403.
class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        if (!$u || !in_array($u->role, ['admin', 'operator'], true)) {
            if ($request->expectsJson()) return response()->json(['error' => 'Akses ditolak'], 403);
            abort(403, 'Akses ditolak. Akun User hanya dapat melihat data.');
        }
        return $next($request);
    }
}
