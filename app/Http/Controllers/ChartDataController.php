<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChartDataController extends Controller
{
    public function getOhlcvData(Request $request, $code)
    {
        $code = strtoupper($code);
        
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

        // 2. Tarik Data Historis EOD Utama dari PostgreSQL (Hasil Scraper idx-bei)
        $historicalData = DB::table('stock_prices')
            ->where('stock_code', $code)
            ->orderBy('trade_date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'time' => strtotime($item->trade_date) * 1000, 
                    'open' => (float)$item->open,
                    'high' => (float)$item->high,
                    'low' => (float)$item->low,
                    'close' => (float)$item->close,
                    'volume' => (int)$item->volume
                ];
            })->toArray();

        // Jika database lokal kosong (belum di-scrape), coba ambil dari Invezgo Historis
        // Jika API Key Invezgo belum diset di .env, fungsi ini akan mengembalikan array kosong dengan aman.
        if (empty($historicalData)) {
            $historicalData = $this->fetchHistoricalInvezgo($code);
        }

        // 3. Khusus Member VIP: Kombo dengan Data Live Real-Time dari Invezgo
        if ($isVip && !empty($historicalData)) {
            $realtimeCandle = $this->fetchRealtimeInvezgo($code);
            
            if ($realtimeCandle) {
                $lastIndex = count($historicalData) - 1;
                $todayDate = date('Y-m-d', $realtimeCandle['time'] / 1000);
                $dbLastDate = date('Y-m-d', $historicalData[$lastIndex]['time'] / 1000);

                // Jika tanggal hari ini sudah ada di database lokal, update dengan harga live terbaru
                if ($todayDate === $dbLastDate) {
                    $historicalData[$lastIndex] = $realtimeCandle;
                } else {
                    // Jika belum ada, tambahkan sebagai candle live hari ini
                    $historicalData[] = $realtimeCandle;
                }
            }
        }

        // Tentukan status sumber data untuk indikator di frontend
        $dataSourceStatus = 'LOCAL_DB_ONLY';
        if ($isVip && env('INVEZGO_API_KEY')) {
            $dataSourceStatus = 'LOCAL_DB_PLUS_INVEZGO_LIVE';
        } elseif (empty($historicalData)) {
            $dataSourceStatus = 'NO_DATA_AVAILABLE';
        }

        return response()->json([
            'status' => 'success',
            'data_source' => $dataSourceStatus,
            'data' => array_values($historicalData)
        ]);
    }

    /**
     * Mengambil Data Real-Time VIP dari Invezgo API
     */
    private function fetchRealtimeInvezgo($code)
    {
        $ticker = str_replace('.JK', '', strtoupper($code));
        if ($ticker === 'IHSG') $ticker = 'COMPOSITE';

        $apiKey = env('INVEZGO_API_KEY');
        
        // Return null jika API Key belum ada (Invezgo belum dibeli)
        if (!$apiKey) return null;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey
            ])->get("https://api.invezgo.com/v1/analysis/intraday-data/{$ticker}?market=RG");

            if ($response->successful() && $response->status() !== 204) {
                $result = $response->json();
                $timestamp = strtotime(date('Y-m-d')) * 1000;

                return [
                    'time'   => $timestamp,
                    'open'   => (float)($result['open'] ?? 0),
                    'high'   => (float)($result['high'] ?? 0),
                    'low'    => (float)($result['low'] ?? 0),
                    'close'  => (float)($result['close'] ?? 0),
                    'volume' => (int)($result['volume'] ?? 0)
                ];
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Mengambil Data Historis dari Invezgo API (Hanya jalan jika DB Lokal kosong)
     */
    private function fetchHistoricalInvezgo($code)
    {
        $ticker = str_replace('.JK', '', strtoupper($code));
        if ($ticker === 'IHSG') $ticker = 'COMPOSITE';

        $apiKey = env('INVEZGO_API_KEY');
        
        // Return array kosong jika API Key belum ada (Invezgo belum dibeli)
        if (!$apiKey) return []; 

        try {
            $fromDate = now()->subYear()->format('Y-m-d');
            $toDate = now()->format('Y-m-d');

            $endpoint = $ticker === 'COMPOSITE' 
                ? "https://api.invezgo.com/v1/analysis/chart/index/{$ticker}?from={$fromDate}&to={$toDate}"
                : "https://api.invezgo.com/v1/analysis/chart/stock/{$ticker}?from={$fromDate}&to={$toDate}";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey
            ])->get($endpoint);

            if ($response->successful() && $response->status() !== 204) {
                $result = $response->json();
                $data = [];
                foreach ($result as $item) {
                    $data[] = [
                        'time'   => strtotime($item['date']) * 1000,
                        'open'   => (float)$item['open'],
                        'high'   => (float)$item['high'],
                        'low'    => (float)$item['low'],
                        'close'  => (float)$item['close'],
                        'volume' => (int)$item['volume']
                    ];
                }
                return $data;
            }
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }
}