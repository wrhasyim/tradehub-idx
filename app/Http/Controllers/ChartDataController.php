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
        // 1. Tangkap parameter TF, default 'D' (Daily) jika kosong
        $tf = $request->query('tf', 'D'); 
        
        // 2. Cek Status VIP User
        $isVip = false;
        if (Auth::check()) {
            $userRole = strtolower(Auth::user()->role ?? 'regular');
            if ($userRole === 'vip' || $userRole === 'superadmin') {
                if ($userRole === 'superadmin' || (Auth::user()->vip_valid_until && now()->lt(Auth::user()->vip_valid_until))) {
                    $isVip = true;
                }
            }
        }

        // =========================================================
        // ROUTING A: INTRADAY (1, 5, 15, 60, 240) -> VIA API INVEZGO
        // =========================================================
        if (in_array($tf, ['1', '5', '15', '60', '240'])) {
            // Panggil fungsi Invezgo Intraday (Pastikan endpoint sesuai dok. Invezgo)
            $intradayData = $this->fetchIntradayInvezgo($code, $tf);
            
            return response()->json([
                'status' => 'success',
                'data_source' => 'INVEZGO_INTRADAY',
                'data' => array_values($intradayData)
            ]);
        }

        // =========================================================
        // ROUTING B: EOD (D, W, M) -> VIA POSTGRESQL LOKAL + AGREGASI
        // =========================================================

        // Tarik Data Historis EOD Utama dari PostgreSQL
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

        // Jika database lokal kosong, coba ambil dari Invezgo Historis
        if (empty($historicalData)) {
            $historicalData = $this->fetchHistoricalInvezgo($code);
        }

        // Khusus Member VIP: Kombo Data Live Real-Time (Hanya diaktifkan saat TF Daily)
        if ($isVip && !empty($historicalData) && $tf === 'D') {
            $realtimeCandle = $this->fetchRealtimeInvezgo($code);
            
            if ($realtimeCandle) {
                $lastIndex = count($historicalData) - 1;
                $todayDate = date('Y-m-d', $realtimeCandle['time'] / 1000);
                $dbLastDate = date('Y-m-d', $historicalData[$lastIndex]['time'] / 1000);

                if ($todayDate === $dbLastDate) {
                    $historicalData[$lastIndex] = $realtimeCandle;
                } else {
                    $historicalData[] = $realtimeCandle;
                }
            }
        }

        // =========================================================
        // AGREGASI OHLCV (Mengubah Daily menjadi Weekly atau Monthly)
        // =========================================================
        if ($tf === 'W' || $tf === 'M') {
            $grouped = collect($historicalData)->groupBy(function ($item) use ($tf) {
                $date = \Carbon\Carbon::createFromTimestamp($item['time'] / 1000);
                // Jika TF Weekly, kelompokkan per Tahun-Minggu (ex: 2026-W40)
                // Jika TF Monthly, kelompokkan per Tahun-Bulan (ex: 2026-10)
                return $tf === 'W' ? $date->format('o-W') : $date->format('Y-m');
            });

            $aggregatedData = [];
            foreach ($grouped as $period => $candles) {
                $aggregatedData[] = [
                    'time'   => $candles->first()['time'], // Waktu candle pertama di minggu/bulan tsb
                    'open'   => $candles->first()['open'], // Harga Open awal periode
                    'high'   => $candles->max('high'),     // Harga Tertinggi selama periode
                    'low'    => $candles->min('low'),      // Harga Terendah selama periode
                    'close'  => $candles->last()['close'], // Harga Close di akhir periode
                    'volume' => $candles->sum('volume')    // Total volume periode tsb
                ];
            }
            $historicalData = $aggregatedData;
        }

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
     * Mengambil Data Historis dari Invezgo API
     */
    private function fetchHistoricalInvezgo($code)
    {
        $ticker = str_replace('.JK', '', strtoupper($code));
        if ($ticker === 'IHSG') $ticker = 'COMPOSITE';

        $apiKey = env('INVEZGO_API_KEY');
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

    /**
     * MENGAMBIL DATA INTRADAY MENITAN DARI INVEZGO API
     * (Pastikan URL Endpoint disesuaikan dengan dokumentasi API Invezgo)
     */
    private function fetchIntradayInvezgo($code, $tf)
    {
        $ticker = str_replace('.JK', '', strtoupper($code));
        if ($ticker === 'IHSG') $ticker = 'COMPOSITE';

        $apiKey = env('INVEZGO_API_KEY');
        if (!$apiKey) return []; 

        // Mapping parameter TF untuk Invezgo (misal: 1m, 5m, 15m, 60m)
        $interval = $tf . 'm'; 

        try {
            // PERHATIAN: Sesuaikan URL Endpoint ini dengan dokumentasi resmi Invezgo Intraday Historical
            $endpoint = "https://api.invezgo.com/v1/analysis/intraday-history/{$ticker}?interval={$interval}";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey
            ])->get($endpoint);

            if ($response->successful() && $response->status() !== 204) {
                $result = $response->json();
                $data = [];
                foreach ($result as $item) {
                    $data[] = [
                        'time'   => strtotime($item['date']) * 1000, // Asumsi $item['date'] memiliki timestamp lengkap seperti "2026-09-23 10:15:00"
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