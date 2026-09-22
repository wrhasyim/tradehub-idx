<?php

namespace App\Http\Controllers;

use App\Models\Watchlist;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $watchlists = Watchlist::latest()->get();
        return view('admin.index', compact('watchlists'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'stock_code' => 'required|string|max:10',
            'status' => 'required|in:Watching,Triggered',
            'entry_price' => 'nullable|numeric',
            'target_price' => 'nullable|numeric',
            'stop_loss' => 'nullable|numeric',
            'ai_analysis_notes' => 'required|string',
        ]);

        Watchlist::create($request->all());

        return redirect()->route('admin.index')->with('success', 'Sinyal berhasil ditambahkan ke pasar!');
    }

    public function destroy($id)
    {
        Watchlist::findOrFail($id)->delete();
        return redirect()->route('admin.index')->with('success', 'Sinyal berhasil dihapus.');
    }
}