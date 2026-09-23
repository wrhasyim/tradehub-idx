<x-app-layout>
    <!-- Load Library -->
    <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
    <script src="https://unpkg.com/lightweight-charts@4.1.1/dist/lightweight-charts.standalone.production.js"></script>

    @php
        $userRole = strtolower(auth()->user()->role ?? 'regular');
        $isPremium = auth()->check() && in_array($userRole, ['vip', 'superadmin']);
    @endphp

    <div class="py-4 bg-zinc-950 min-h-screen text-gray-100">
        <div class="max-w-[98%] xl:max-w-[95%] mx-auto space-y-4">
            
            <!-- Header Panel -->
            <div class="flex items-center justify-between bg-zinc-900 border border-zinc-800 p-2.5 rounded-xl shadow-lg flex-wrap gap-3">
                
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2 px-2 shrink-0">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                        <h2 class="text-lg font-black text-white tracking-widest">TERMINAL<span class="text-amber-500">PRO</span></h2>
                    </div>
                    
                    <!-- Input Auto-Search -->
                    <div class="relative w-44 shrink-0">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" id="stockCode" value="BREN" 
                            class="bg-zinc-950 border border-zinc-800 text-white text-sm rounded-md focus:ring-1 focus:ring-amber-500 block w-full pl-9 p-2 uppercase font-bold tracking-wider placeholder-zinc-600 outline-none" 
                            placeholder="KODE..." autocomplete="off">
                    </div>
                </div>

                <!-- Panel Kontrol Mode & Fitur -->
                <div class="flex items-center gap-2 flex-wrap">
                    
                    <!-- Toggle Mode Chart -->
                    <div class="bg-zinc-950 p-1 rounded-lg border border-zinc-800 flex items-center">
                        <button id="modeTv" class="px-3 py-1 text-xs font-bold rounded bg-amber-500 text-zinc-950 transition-all">
                            TradingView
                        </button>
                        <button id="modeCustom" class="px-3 py-1 text-xs font-bold rounded text-zinc-400 hover:text-white transition-all">
                            Tradehub Chart
                        </button>
                    </div>

                    <!-- Toggle SMC -->
                    <div id="smcWrapper" class="hidden">
                        <button id="toggleSMC" class="flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold bg-indigo-900/40 text-indigo-400 border border-indigo-700/50 hover:bg-indigo-800/50 transition-colors">
                            SMC PRO : OFF
                        </button>
                    </div>

                    <!-- Tombol Potret Chart -->
                    <button id="btnSnapshot" class="hidden p-2 bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 rounded-lg text-amber-500 transition-colors shadow-sm" title="Potret Chart">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </button>

                </div>
            </div>

            <!-- Kontainer Chart -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl shadow-2xl p-1 relative">
                <div id="toast" class="absolute top-4 left-1/2 -translate-x-1/2 z-50 hidden px-4 py-2 bg-zinc-800 border border-zinc-700 text-white rounded shadow-lg text-xs font-bold"></div>
                <div id="tvchart" style="width: 100%; height: 85vh; min-height: 650px;" class="rounded-lg overflow-hidden"></div>
            </div>

        </div>
    </div>

    <script>
        const isPremiumUser = @json($isPremium);
        let currentMode = 'tv'; 
        let tvWidget = null;
        let customChart = null, candleSeries = null, volumeSeries = null;
        let customRawData = [];
        let smcActive = false;
        let typingTimer;
        const doneTypingInterval = 800;

        function showToast(msg, type = 'success') {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = `absolute top-4 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded shadow-lg text-xs font-bold ${type === 'error' ? 'bg-red-600 text-white' : 'bg-emerald-600 text-zinc-950'}`;
            t.classList.remove('hidden');
            setTimeout(() => t.classList.add('hidden'), 3000);
        }

        function renderTvWidget(symbolCode) {
            document.getElementById('tvchart').innerHTML = '';
            document.getElementById('smcWrapper').classList.add('hidden'); 
            document.getElementById('btnSnapshot').classList.add('hidden');

            tvWidget = new TradingView.widget({
                "autosize": true,
                "symbol": "IDX:" + symbolCode, 
                "interval": "D", 
                "timezone": "Asia/Jakarta",
                "theme": "dark",
                "style": "1", 
                "locale": "id",
                "enable_publishing": false,
                "backgroundColor": "#09090b",
                "gridColor": "#27272a", 
                "hide_top_toolbar": false,
                "hide_legend": false,
                "save_image": true,
                "hide_side_toolbar": false, 
                "allow_symbol_change": false, 
                "details": true,
                "container_id": "tvchart",
                "toolbar_bg": "#18181b",
                "disabled_features": ["header_symbol_search"]
            });
        }

        function renderCustomChart(symbolCode) {
            document.getElementById('tvchart').innerHTML = '';
            
            if (isPremiumUser) {
                document.getElementById('smcWrapper').classList.remove('hidden');
            } else {
                document.getElementById('smcWrapper').classList.add('hidden');
            }
            
            document.getElementById('btnSnapshot').classList.remove('hidden');

            customChart = LightweightCharts.createChart(document.getElementById('tvchart'), {
                layout: { background: { type: 'solid', color: '#09090b' }, textColor: '#a1a1aa' },
                grid: { vertLines: { color: '#27272a' }, horzLines: { color: '#27272a' } },
                crosshair: { mode: LightweightCharts.CrosshairMode.Normal },
                rightPriceScale: { 
                    borderColor: '#27272a',
                    scaleMargins: { top: 0.1, bottom: 0.2 } 
                },
                timeScale: { borderColor: '#27272a', timeVisible: true }
            });

            candleSeries = customChart.addCandlestickSeries({
                upColor: '#10b981', downColor: '#ef4444', 
                borderUpColor: '#10b981', borderDownColor: '#ef4444',
                wickUpColor: '#10b981', wickDownColor: '#ef4444'
            });

            volumeSeries = customChart.addHistogramSeries({
                color: '#26a69a',
                priceFormat: { type: 'volume' },
                priceScaleId: 'volume',
            });

            customChart.priceScale('volume').applyOptions({
                scaleMargins: { top: 0.8, bottom: 0 },
            });

            fetchDataFromController(symbolCode);
        }

        async function fetchDataFromController(code) {
            showToast("Memuat data pasar...", "success");
            try {
                const response = await fetch(`/api/chart-data/${code}`);
                
                // Cek apakah server mengembalikan respons JSON yang valid (mencegah SyntaxError 404 HTML)
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("Endpoint API tidak ditemukan (404) atau server error.");
                }

                const result = await response.json();
                
                if (result.status !== 'success' || !Array.isArray(result.data) || result.data.length === 0) {
                    showToast("Data saham tidak tersedia di server.", "error");
                    return;
                }

                // Konversi milidetik ke detik untuk Lightweight Charts
                const formattedData = result.data.map(item => ({
                    time: Math.floor(item.time / 1000),
                    open: item.open,
                    high: item.high,
                    low: item.low,
                    close: item.close,
                    volume: item.volume
                }));

                customRawData = formattedData;
                candleSeries.setData(formattedData);
                volumeSeries.setData(formattedData.map(d => ({
                    time: d.time, 
                    value: d.volume, 
                    color: d.close > d.open ? 'rgba(16, 185, 129, 0.4)' : 'rgba(239, 68, 68, 0.4)' 
                })));

                // BATASAN TAMPILAN AWAL: Hanya tampilkan 50 candle terakhir agar rapi dan tidak kecil
                const visibleBars = 50;
                const fromIdx = Math.max(0, formattedData.length - visibleBars);
                customChart.timeScale().setVisibleLogicalRange({
                    from: fromIdx,
                    to: formattedData.length - 1
                });

                applySmcState();
            } catch (err) {
                console.error(err);
                showToast(err.message || "Gagal terhubung ke server data.", "error");
            }
        }

        function calculateSmartMoneyConcepts(data) {
            let markers = [];
            if (data.length < 10) return markers;

            let lastSwingHigh = -1;
            let lastSwingLow = -1;
            let trend = 'neutral';

            for (let i = 2; i < data.length - 2; i++) {
                const curr = data[i];
                const prev1 = data[i-1], prev2 = data[i-2];
                const next1 = data[i+1], next2 = data[i+2];

                if (curr.high > prev1.high && curr.high > prev2.high && curr.high > next1.high && curr.high > next2.high) {
                    if (lastSwingHigh !== -1 && curr.close > data[lastSwingHigh].high) {
                        if (trend === 'bullish' || trend === 'neutral') {
                            markers.push({ time: curr.time, position: 'aboveBar', color: '#3b82f6', shape: 'arrowDown', text: 'BOS' });
                            trend = 'bullish';
                        }
                    } else if (lastSwingHigh !== -1 && curr.close < data[lastSwingHigh].high && trend === 'bullish') {
                        markers.push({ time: curr.time, position: 'aboveBar', color: '#ef4444', shape: 'arrowDown', text: 'CHoCH' });
                        trend = 'bearish';
                    }
                    lastSwingHigh = i;
                }

                if (curr.low < prev1.low && curr.low < prev2.low && curr.low < next1.low && curr.low < next2.low) {
                    if (lastSwingLow !== -1 && curr.close < data[lastSwingLow].low) {
                        if (trend === 'bearish' || trend === 'neutral') {
                            markers.push({ time: curr.time, position: 'belowBar', color: '#f59e0b', shape: 'arrowUp', text: 'BOS' });
                            trend = 'bearish';
                        }
                    } else if (lastSwingLow !== -1 && curr.close > data[lastSwingLow].low && trend === 'bearish') {
                        markers.push({ time: curr.time, position: 'belowBar', color: '#10b981', shape: 'arrowUp', text: 'CHoCH' });
                        trend = 'bullish';
                    }
                    lastSwingLow = i;
                }
            }
            return markers;
        }

        function applySmcState() {
            const btnSMC = document.getElementById('toggleSMC');
            if (smcActive && customRawData.length > 0) {
                const smcMarkers = calculateSmartMoneyConcepts(customRawData);
                candleSeries.setMarkers(smcMarkers);
                
                btnSMC.textContent = "SMC PRO : ON";
                btnSMC.className = "flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold bg-indigo-600 text-white border border-indigo-500 shadow";
            } else {
                candleSeries.setMarkers([]);
                btnSMC.textContent = "SMC PRO : OFF";
                btnSMC.className = "flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold bg-indigo-900/40 text-indigo-400 border border-indigo-700/50 hover:bg-indigo-800/50 transition-colors";
            }
        }

        function executeLoad(symbol) {
            if (currentMode === 'tv') {
                renderTvWidget(symbol);
            } else {
                renderCustomChart(symbol);
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            const stockInput = document.getElementById('stockCode');
            
            renderTvWidget(stockInput.value);

            document.getElementById('modeTv').addEventListener('click', function() {
                currentMode = 'tv';
                this.className = "px-3 py-1 text-xs font-bold rounded bg-amber-500 text-zinc-950 transition-all";
                document.getElementById('modeCustom').className = "px-3 py-1 text-xs font-bold rounded text-zinc-400 hover:text-white transition-all";
                executeLoad(stockInput.value.toUpperCase().trim());
            });

            document.getElementById('modeCustom').addEventListener('click', function() {
                if (!isPremiumUser) {
                    showToast("Tradehub Chart & SMC khusus akun VIP/Superadmin!", "error");
                    return;
                }
                currentMode = 'custom';
                this.className = "px-3 py-1 text-xs font-bold rounded bg-amber-500 text-zinc-950 transition-all";
                document.getElementById('modeTv').className = "px-3 py-1 text-xs font-bold rounded text-zinc-400 hover:text-white transition-all";
                executeLoad(stockInput.value.toUpperCase().trim());
            });

            stockInput.addEventListener('input', function () {
                clearTimeout(typingTimer);
                const code = this.value.toUpperCase().trim();
                if (code.length >= 4 || code === 'IHSG') {
                    typingTimer = setTimeout(() => { executeLoad(code); }, doneTypingInterval);
                }
            });

            stockInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    clearTimeout(typingTimer);
                    const code = this.value.toUpperCase().trim();
                    if (code) executeLoad(code);
                }
            });

            document.getElementById('toggleSMC' ).addEventListener('click', () => {
                smcActive = !smcActive;
                applySmcState();
            });

            document.getElementById('btnSnapshot').addEventListener('click', () => {
                const canvas = document.querySelector('#tvchart canvas');
                if (canvas) {
                    const link = document.createElement('a');
                    link.download = `Tradehub-${stockInput.value.toUpperCase()}.png`;
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                    showToast("Snapshot chart berhasil diunduh!", "success");
                } else {
                    showToast("Gagal mengambil gambar chart.", "error");
                }
            });
        });
    </script>
</x-app-layout>