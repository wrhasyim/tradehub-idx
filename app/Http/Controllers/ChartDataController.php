<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChartDataController extends Controller
{
    public function getOhlcvData(Request $request, $code)
    {
        $code = strtoupper($code);
        $timeframe = $request->query('timeframe', 'D');
        
        // 1. Cek Status VIP User berdasarkan tabel users di database
        $isVip = false;
        if (Auth::check()) {
            $userRole = strtolower(Auth::user()->role ?? 'regular');
            if ($userRole === 'vip' || $userRole === 'superadmin') {
                if ($userRole === 'superadmin' || (Auth::user()->vip_valid_until && now()->lt(Auth::user()->vip_valid_until))) {
                    $isVip = true;
                }
            }
        }

        // 2. Tarik Data Historis EOD dari PostgreSQL (Hasil Scraper idx-bei)
        $historicalData = DB::table('stock_prices')
            ->where('stock_code', $code)
            ->orderBy('trade_date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'time' => strtotime($item->trade_date) * 1000, // Konversi ke milidetik untuk Lightweight Charts
                    'open' => (float)$item->open,
                    'high' => (float)$item->high,
                    'low' => (float)$item->low,
                    'close' => (float)$item->close,
                    'volume' => (int)$item->volume
                ];
            })->toArray();

        // Fallback darurat jika database lokal belum di-scrape untuk emiten tersebut
        if (empty($historicalData)) {
            $historicalData = $this->fetchFallbackExternal($code);
        }

        // 3. Khusus Member VIP: Kombo dengan Data Live Real-Time dari Invezgo
        if ($isVip && !empty($historicalData)) {
            $realtimeCandle = $this->fetchRealtimeInvezgo($code);
            if ($realtimeCandle) {
                $lastIndex = count($historicalData) - 1;
                $todayDate = date('Y-m-d', $realtimeCandle['time'] / 1000);
                $dbLastDate = date('Y-m-d', $historicalData[$lastIndex]['time'] / 1000);

                // Jika tanggal hari ini sudah ada di database, timpa dengan harga live terbaru
                if ($todayDate === $dbLastDate) {
                    $historicalData[$lastIndex] = $realtimeCandle;
                } else {
                    // Jika belum ada, tambahkan sebagai candle baru hari berjalan
                    $historicalData[] = $realtimeCandle;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data_source' => $isVip ? 'HYBRID_VIP_DB_PLUS_LIVE' : 'REGULAR_EOD_DATABASE',
            'data' => $historicalData
        ]);
    }

    /**
     * Jalur Real-Time VIP (Invezgo API / Proxy)
     */
    private function fetchRealtimeInvezgo($code)
    {
        $ticker = str_ends_with($code, '.JK') ? $code : $code . '.JK';
        if ($code === 'IHSG') $ticker = '^JKSE';

        try {
            // TODO: Ganti URL ini ke endpoint resmi Invezgo API saat sudah siap
            // Contoh: Http::withToken('API_KEY')->get("https://api.invezgo.com/v1/realtime/{$ticker}")
            
            // Sementara menggunakan Yahoo Finance sebagai jembatan live data hari ini
            $response = Http::withUserAgent('Mozilla/5.0')->get("https://query1.finance.yahoo.com/v8/finance/chart/{$ticker}?interval=1d&range=1d");
            $result = $response->json();
            
            $timestamp = $result['chart']['result'][0]['timestamp'][0] ?? null;
            $quote = $result['chart']['result'][0]['indicators']['quote'][0] ?? [];

            if (!$timestamp) return null;

            return [
                'time' => $timestamp * 1000,
                'open' => round((float)($quote['open'][0] ?? 0), 2),
                'high' => round((float)($quote['high'][0] ?? 0), 2),
                'low' => round((float)($quote['low'][0] ?? 0), 2),
                'close' => round((float)($quote['close'][0] ?? 0), 2),
                'volume' => (int)($quote['volume'][0] ?? 0)
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Fallback Eksternal jika tabel stock_prices kosong
     */
    private function fetchFallbackExternal($code)
    {
        $ticker = str_ends_with($code, '.JK') ? $code : $code . '.JK';
        if ($code === 'IHSG') $ticker = '^JKSE';

        try {
            $response = Http::withUserAgent('Mozilla/5.0')->get("https://query1.finance.yahoo.com/v8/finance/chart/{$ticker}?interval=1d&range=1y");
            $result = $response->json();
            $timestamps = $result['chart']['result'][0]['timestamp'] ?? [];
            $quote = $result['chart']['result'][0]['indicators']['quote'][0] ?? [];
            
            $data = [];
            for ($i = 0; $i < count($timestamps); $i++) {
                if (isset($quote['close'][$i]) && $quote['close'][$i] !== null) {
                    $data[] = [
                        'time' => $timestamps[$i] * 1000,
                        'open' => round((float)($quote['open'][$i] ?? 0), 2),
                        'high' => round((float)($quote['high'][$i] ?? 0), 2),
                        'low' => round((float)($quote['low'][$i] ?? 0), 2),
                        'close' => round((float)($quote['close'][$i] ?? 0), 2),
                        'volume' => (int)($quote['volume'][$i] ?? 0)
                    ];
                }
            }
            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }
}