<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChartDataController extends Controller
{
    public function getOhlcvData(Request $request, $code)
    {
        $code = strtoupper($code);
        
        // 1. Tangkap parameter TF dari frontend, default 'D' (Daily) jika kosong
        $tf = $request->query('tf', 'D'); 
        
        // 2. Cek Status VIP User berdasarkan tabel users di database
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
            // Panggil fungsi Invezgo Intraday
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

        // Tarik Data Historis EOD Utama dari PostgreSQL (Hasil Scraper idx-bei)
        $historicalData = DB::table('stock_prices')
            ->where('stock_code', $code)
            ->orderBy('trade_date', 'asc')
            ->get()
            ->map(function ($item) {
                // Parsing Data Broksum JSON (Sesuaikan dengan nama kolom DB Om)
                $broksumRaw = isset($item->broksum_data) && !empty($item->broksum_data) 
                    ? json_decode($item->broksum_data, true) 
                    : null;

                return [
    'time'         => strtotime($item->trade_date) * 1000, 
    'open'         => (float)$item->open,
    'high'         => (float)$item->high,
    'low'          => (float)$item->low,
    'close'        => (float)$item->close,
    'volume'       => (int)$item->volume,
    // TAMBAHKAN INI SESUAI DB OM
    'foreign_buy'  => (float)$item->foreign_buy,
    'foreign_sell' => (float)$item->foreign_sell,
    'broksum'      => null // Biarkan null karena tidak ada di DB, tabel broksum akan tertulis "API Tidak Tersedia"
];
            })->toArray();

        // Jika database lokal kosong, coba ambil dari Invezgo Historis
        if (empty($historicalData)) {
            $historicalData = $this->fetchHistoricalInvezgo($code);
        }

        // Khusus Member VIP: Kombo Data Live Real-Time (Hanya aktif saat TF Daily)
        if ($isVip && !empty($historicalData) && $tf === 'D') {
            $realtimeCandle = $this->fetchRealtimeInvezgo($code);
            
            if ($realtimeCandle) {
                $lastIndex = count($historicalData) - 1;
                $todayDate = date('Y-m-d', $realtimeCandle['time'] / 1000);
                $dbLastDate = date('Y-m-d', $historicalData[$lastIndex]['time'] / 1000);

                // Jika tanggal hari ini sudah ada di database lokal, update dengan harga live terbaru
                if ($todayDate === $dbLastDate) {
                    // Amankan data broksum & bandar_vol lokal agar tidak tertimpa null dari Invezgo Realtime
                    $realtimeCandle['broksum'] = $historicalData[$lastIndex]['broksum'] ?? null;
                    $realtimeCandle['bandar_vol'] = $historicalData[$lastIndex]['bandar_vol'] ?? 0;
                    $historicalData[$lastIndex] = $realtimeCandle;
                } else {
                    // Jika belum ada, tambahkan sebagai candle live hari ini
                    $historicalData[] = $realtimeCandle;
                }
            }
        }

        // =========================================================
        // AGREGASI OHLCV (Mengubah Daily menjadi Weekly atau Monthly)
        // =========================================================
        if ($tf === 'W' || $tf === 'M') {
            $grouped = collect($historicalData)->groupBy(function ($item) use ($tf) {
                $date = Carbon::createFromTimestamp($item['time'] / 1000);
                // W: Kelompokkan per Tahun-Minggu (ex: 2026-W40)
                // M: Kelompokkan per Tahun-Bulan (ex: 2026-09)
                return $tf === 'W' ? $date->format('o-W') : $date->format('Y-m');
            });

            $aggregatedData = [];
            foreach ($grouped as $period => $candles) {
                $aggregatedData[] = [
                    'time'       => $candles->first()['time'],
                    'open'       => $candles->first()['open'],
                    'high'       => $candles->max('high'),
                    'low'        => $candles->min('low'),
                    'close'      => $candles->last()['close'],
                    'volume'     => $candles->sum('volume'),
                    // Agregasi Bandarmologi & Broksum
                    'bandar_vol' => $candles->sum('bandar_vol'), // Akumulasi net volume bandar sepekan/sebulan
                    'broksum'    => $candles->last()['broksum']  // Menampilkan komposisi broker hari terakhir sebagai representasi (karena struktur JSON sulit disummary manual)
                ];
            }
            $historicalData = $aggregatedData;
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
        if (!$apiKey) return null;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey
            ])->get("https://api.invezgo.com/v1/analysis/intraday-data/{$ticker}?market=RG");

            if ($response->successful() && $response->status() !== 204) {
                $result = $response->json();
                $timestamp = strtotime(date('Y-m-d')) * 1000;

                return [
                    'time'       => $timestamp,
                    'open'       => (float)($result['open'] ?? 0),
                    'high'       => (float)($result['high'] ?? 0),
                    'low'        => (float)($result['low'] ?? 0),
                    'close'      => (float)($result['close'] ?? 0),
                    'volume'     => (int)($result['volume'] ?? 0),
                    'bandar_vol' => 0,
                    'broksum'    => null
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
                        'time'       => strtotime($item['date']) * 1000,
                        'open'       => (float)$item['open'],
                        'high'       => (float)$item['high'],
                        'low'        => (float)$item['low'],
                        'close'      => (float)$item['close'],
                        'volume'     => (int)$item['volume'],
                        'bandar_vol' => 0, 
                        'broksum'    => null
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
     */
    private function fetchIntradayInvezgo($code, $tf)
    {
        $ticker = str_replace('.JK', '', strtoupper($code));
        if ($ticker === 'IHSG') $ticker = 'COMPOSITE';

        $apiKey = env('INVEZGO_API_KEY');
        if (!$apiKey) return []; 

        // Parameter Interval Invezgo: 1m, 5m, 15m, 60m (Berdasarkan dokumen API)
        $interval = $tf . 'm'; 

        try {
            // Asumsi Endpoint Invezgo untuk Intraday Historis
            $endpoint = "https://api.invezgo.com/v1/analysis/intraday-history/{$ticker}?interval={$interval}";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey
            ])->get($endpoint);

            if ($response->successful() && $response->status() !== 204) {
                $result = $response->json();
                $data = [];
                foreach ($result as $item) {
                    $data[] = [
                        'time'       => strtotime($item['date']) * 1000,
                        'open'       => (float)$item['open'],
                        'high'       => (float)$item['high'],
                        'low'        => (float)$item['low'],
                        'close'      => (float)$item['close'],
                        'volume'     => (int)$item['volume'],
                        // Kosongkan broksum/bandar_vol untuk timeframe menit karena biasanya API tidak menyediakannya per menit
                        'bandar_vol' => 0, 
                        'broksum'    => null
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