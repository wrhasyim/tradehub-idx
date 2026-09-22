<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    // Method index untuk menampilkan halaman penawaran (opsional jika ingin punya halaman terpisah)
    public function index()
    {
        return view('upgrade'); 
    }

    public function process(Request $request)
    {
        $user = auth()->user();
        $user->role = 'vip';
        $user->save();

        return redirect()->route('dashboard')->with('vip_success', 'Selamat! Akses VIP Anda telah aktif. Selamat mendominasi pasar!');
    }
}