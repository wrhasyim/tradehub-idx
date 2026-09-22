<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalVipUsers = User::where('role', 'vip')->count();

        // 1. Cek Koneksi API Invezgo (Data Saham)
        $invezgoStatus = 'Operational';
        $invezgoLatency = '35ms';
        try {
            $start = microtime(true);
            $response = Http::timeout(3)->get('https://api.sandbox.midtrans.com'); 
            $latency = round((microtime(true) - $start) * 1000);
            $invezgoLatency = $latency . 'ms';
            if ($response->failed()) {
                $invezgoStatus = 'Degraded';
            }
        } catch (\Exception $e) {
            $invezgoStatus = 'Offline';
            $invezgoLatency = '-';
        }

        // 2. Cek Koneksi API Midtrans (Payment Gateway)
        $midtransStatus = 'Operational';
        $midtransLatency = '42ms';
        try {
            $start = microtime(true);
            $response = Http::timeout(3)->get('https://api.sandbox.midtrans.com');
            $latency = round((microtime(true) - $start) * 1000);
            $midtransLatency = $latency . 'ms';
            if ($response->failed()) {
                $midtransStatus = 'Degraded';
            }
        } catch (\Exception $e) {
            $midtransStatus = 'Offline';
            $midtransLatency = '-';
        }

        $users = User::latest()->paginate(10);

        return view('admin.index', compact(
            'totalUsers', 
            'totalVipUsers', 
            'invezgoStatus', 
            'invezgoLatency', 
            'midtransStatus', 
            'midtransLatency', 
            'users'
        ));
    }

    public function toggleVip(User $user)
    {
        if ($user->role === 'superadmin') {
            return back()->with('error', 'Tidak dapat mengubah role Superadmin.');
        }

        // Logika toggle dinamis beserta pesannya
        if ($user->role === 'vip') {
            $user->role = 'regular';
            $user->vip_valid_until = null;
            $message = 'Status VIP user berhasil dicabut.';
        } else {
            $user->role = 'vip';
            $user->vip_valid_until = now()->addMonth(); // Aktif 1 bulan otomatis
            $message = 'Status VIP berhasil diaktifkan selama 1 bulan.';
        }
        
        $user->save();

        return back()->with('success', $message);
    }
}