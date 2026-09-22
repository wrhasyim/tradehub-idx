<?php

namespace App\Http\Controllers;

use App\Models\Watchlist;
use Illuminate\Http\Request;
use App\Models\Ipo;

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
    // Di dalam class AdminController, tambahkan method ini:
public function storeIpo(Request $request)
{
    $request->validate([
        'code' => 'required|string|max:10',
        'company_name' => 'required|string|max:255',
        'status' => 'required|string',
        'offering_date' => 'nullable|string',
    ]);

    Ipo::create($request->all());

    return redirect()->route('admin.index')->with('success', 'Jadwal E-IPO berhasil ditambahkan!');
}

public function destroyIpo($id)
{
    Ipo::findOrFail($id)->delete();
    return redirect()->route('admin.index')->with('success', 'Jadwal E-IPO berhasil dihapus!');
}
}