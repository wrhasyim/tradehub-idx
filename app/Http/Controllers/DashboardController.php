<?php

namespace App\Http\Controllers;

use App\Models\Watchlist;
use Illuminate\Http\Request;
use App\Models\Ipo;

class DashboardController extends Controller
{
    public function index()
    {
        // Menarik 5 data watchlist AI terbaru dari database
        $watchlists = Watchlist::latest()->take(5)->get();
        $ipos = Ipo::latest()->get();
        // Melempar data tersebut ke file tampilan dashboard.blade.php
        return view('dashboard', compact('watchlists', 'ipos'));
    }
}