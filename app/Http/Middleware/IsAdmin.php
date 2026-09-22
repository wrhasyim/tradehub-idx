<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika belum login atau bukan superadmin, tendang kembali ke dashboard
        if (!auth()->check() || auth()->user()->role !== 'superadmin') {
            abort(403, 'Akses Ditolak. Ruang Khusus Founder.');
        }

        return $next($request);
    }
}