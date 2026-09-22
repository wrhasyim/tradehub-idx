<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    public function index()
    {
        $journals = Journal::where('user_id', Auth::id())->orderBy('trade_date', 'desc')->get();
        
        // Kalkulasi Win Rate dan Total PnL
        $totalTrades = $journals->whereNotNull('sell_price')->count();
        $winningTrades = $journals->where('pnl_amount', '>', 0)->count();
        
        $winRate = $totalTrades > 0 ? round(($winningTrades / $totalTrades) * 100, 1) : 0;
        $totalPnl = $journals->sum('pnl_amount');

        return view('journal.index', compact('journals', 'winRate', 'totalPnl', 'totalTrades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'stock_code' => 'required|string|max:10',
            'buy_price' => 'required|numeric|min:1',
            'sell_price' => 'nullable|numeric|min:1',
            'lots' => 'required|integer|min:1',
            'trade_date' => 'required|date',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['stock_code'] = strtoupper($data['stock_code']);

        // Kalkulasi PnL otomatis jika ada harga jual
        if (!empty($data['sell_price'])) {
            $totalBuy = $data['buy_price'] * $data['lots'] * 100;
            $totalSell = $data['sell_price'] * $data['lots'] * 100;
            
            $data['pnl_amount'] = $totalSell - $totalBuy;
            $data['pnl_percentage'] = (($data['sell_price'] - $data['buy_price']) / $data['buy_price']) * 100;
        }

        Journal::create($data);

        return back()->with('success', 'Catatan trading berhasil ditambahkan.');
    }

    public function destroy(Journal $journal)
    {
        if ($journal->user_id !== Auth::id()) {
            abort(403);
        }
        $journal->delete();
        return back()->with('success', 'Catatan trading dihapus.');
    }
}