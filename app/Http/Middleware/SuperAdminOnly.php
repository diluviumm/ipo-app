<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// Hanya superadmin (role admin + province_id null).
class SuperAdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        if (!$u || $u->role !== 'admin' || $u->province_id !== null) {
            if ($request->expectsJson()) return response()->json(['error' => 'Hanya Superadmin yang dapat mengelola user'], 403);
            abort(403, 'Hanya Superadmin yang dapat mengelola user.');
        }
        return $next($request);
    }
}
