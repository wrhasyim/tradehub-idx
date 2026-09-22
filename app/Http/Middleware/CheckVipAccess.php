<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVipAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Pastikan user sudah login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // 2. Cek apakah user punya akses VIP aktif (menggunakan fungsi yang kita buat di Model)
        if (!auth()->user()->hasActiveVip()) {
            // Jika bukan VIP atau masa aktif habis, lempar ke halaman upgrade
            // (Nanti kita bisa buatkan halaman khusus untuk rute 'upgrade.index' ini)
            return redirect('/upgrade')->with('error', 'Akses ditolak! Fitur ini eksklusif untuk TradeHub VIP Member. Silakan upgrade kartu akses Anda.');
        }

        // 3. Jika VIP aktif atau Superadmin, silakan lewat!
        return $next($request);
    }
}