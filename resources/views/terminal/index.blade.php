<x-app-layout>
    <!-- Load Library -->
    <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
    <script src="https://unpkg.com/lightweight-charts@4.1.1/dist/lightweight-charts.standalone.production.js"></script>
    
    <!-- Load Modul SMC PRO Eksternal -->
    <script src="{{ asset('js/smc.js') }}"></script>

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

                    <!-- Timeframe Selector (Disembunyikan via JS saat mode TV) -->
                    <div id="customTfWrapper" class="hidden bg-zinc-950 p-1 rounded-lg border border-zinc-800 items-center gap-0.5 transition-all">
                        <button class="tf-btn px-2 py-1 text-xs font-bold rounded text-zinc-400 hover:text-white transition-all" data-tf="1">1m</button>
                        <button class="tf-btn px-2 py-1 text-xs font-bold rounded text-zinc-400 hover:text-white transition-all" data-tf="5">5m</button>
                        <button class="tf-btn px-2 py-1 text-xs font-bold rounded text-zinc-400 hover:text-white transition-all" data-tf="15">15m</button>
                        <button class="tf-btn px-2 py-1 text-xs font-bold rounded text-zinc-400 hover:text-white transition-all" data-tf="60">1H</button>
                        <button class="tf-btn px-2 py-1 text-xs font-bold rounded text-zinc-400 hover:text-white transition-all" data-tf="240">4H</button>
                        <button class="tf-btn px-2 py-1 text-xs font-bold rounded bg-amber-500 text-zinc-950 transition-all active-tf" data-tf="D">D</button>
                        <button class="tf-btn px-2 py-1 text-xs font-bold rounded text-zinc-400 hover:text-white transition-all" data-tf="W">W</button>
                        <button class="tf-btn px-2 py-1 text-xs font-bold rounded text-zinc-400 hover:text-white transition-all" data-tf="M">M</button>
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

                    <!-- Toggle SMC & Foreign -->
                    <div id="smcWrapper" class="hidden items-center gap-2">
                        <button id="toggleSMC" class="flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold bg-indigo-900/40 text-indigo-400 border border-indigo-700/50 hover:bg-indigo-800/50 transition-colors">
                            SMC PRO : OFF
                        </button>
                        <button id="toggleBroksum" class="flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold bg-emerald-900/40 text-emerald-400 border border-emerald-700/50 hover:bg-emerald-800/50 transition-colors">
                            FOREIGN : OFF
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
                
                <!-- Floating Foreign Panel -->
                <div id="broksumPanel" class="hidden absolute top-4 left-4 z-40 w-64 bg-zinc-900/95 border border-zinc-700 rounded-xl shadow-2xl backdrop-blur-md flex-col overflow-hidden pointer-events-none transition-opacity duration-200">
                    <div class="bg-zinc-800 px-3 py-2 border-b border-zinc-700 flex justify-between items-center">
                        <span class="text-[11px] font-bold text-white tracking-wider">FOREIGN FLOW</span>
                        <span id="bsDate" class="text-[10px] text-amber-500 font-bold">-</span>
                    </div>
                    
                    <div class="px-3 py-3 bg-zinc-800/30">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-[11px] text-zinc-400">Foreign Buy</span>
                            <span id="fBuy" class="text-[11px] font-bold text-emerald-400">0</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-[11px] text-zinc-400">Foreign Sell</span>
                            <span id="fSell" class="text-[11px] font-bold text-red-400">0</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 mt-2 border-t border-zinc-700">
                            <span class="text-xs font-black text-white">NET FOREIGN</span>
                            <span id="fNet" class="text-xs font-black text-zinc-500">0</span>
                        </div>
                    </div>
                </div>
                
                <!-- 1. Wadah TradingView -->
                <div id="tv-container" style="width: 100%; height: 85vh; min-height: 650px;" class="rounded-lg overflow-hidden"></div>
                
                <!-- 2. Wadah Tradehub Chart -->
                <div id="lw-container" style="width: 100%; height: 85vh; min-height: 650px;" class="rounded-lg overflow-hidden hidden"></div>

                <!-- 3. Wadah PAYWALL -->
                <div id="paywall-container" style="width: 100%; height: 85vh; min-height: 650px;" class="hidden flex-col items-center justify-center bg-zinc-950 rounded-lg border-2 border-dashed border-zinc-800">
                    <div class="p-4 bg-amber-500/10 rounded-full mb-6">
                        <svg class="w-16 h-16 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-white mb-2 tracking-wide">FITUR EKSKLUSIF VIP</h3>
                    <p class="text-sm text-zinc-400 mb-8 text-center max-w-md leading-relaxed">
                        Akses penuh ke <strong class="text-white">Tradehub Chart</strong> dengan indikator <em>Smart Money Concepts (SMC)</em>, Foreign Flow, dan fitur pro lainnya.
                    </p>
                    <a href="{{ route('dashboard') }}" class="px-8 py-3 bg-amber-500 hover:bg-amber-600 text-zinc-950 font-bold rounded-lg transition-colors shadow-[0_0_20px_rgba(245,158,11,0.3)]">
                        Upgrade ke VIP Sekarang
                    </a>
                </div>
            </div>

        </div>
    </div>

    <script>
        const isPremiumUser = @json($isPremium);
        let currentMode = 'tv'; 
        let currentInterval = 'D'; 
        let tvWidget = null;
        let customChart = null, candleSeries = null, volumeSeries = null, bandarSeries = null;
        let customRawData = [];
        let smcActive = false;
        let broksumActive = false;
        let typingTimer;
        const doneTypingInterval = 800;

        function formatVal(val) {
            if (!val || isNaN(val)) return '0';
            let absVal = Math.abs(val);
            if (absVal >= 1e9) return (val / 1e9).toFixed(2) + ' M';
            if (absVal >= 1e6) return (val / 1e6).toFixed(2) + ' Jt';
            return val.toLocaleString('id-ID');
        }

        function showToast(msg, type = 'success') {
            const t = document.getElementById('toast');
            if(!t) return;
            t.textContent = msg;
            t.className = `absolute top-4 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded shadow-lg text-xs font-bold ${type === 'error' ? 'bg-red-600 text-white' : 'bg-emerald-600 text-zinc-950'}`;
            t.style.display = 'block';
            setTimeout(() => t.style.display = 'none', 3000);
        }

        // FUNGSI PENGATUR UI (Anti-Error Toggling)
        function setUIMode(mode) {
            const tv = document.getElementById('tv-container');
            const lw = document.getElementById('lw-container');
            const pw = document.getElementById('paywall-container');
            const smcWrap = document.getElementById('smcWrapper');
            const tfWrap = document.getElementById('customTfWrapper');
            const bPanel = document.getElementById('broksumPanel');
            const snapBtn = document.getElementById('btnSnapshot');

            // Reset semua display
            if(tv) tv.style.display = 'none';
            if(lw) lw.style.display = 'none';
            if(pw) { pw.style.display = 'none'; pw.classList.remove('flex'); }
            if(smcWrap) { smcWrap.classList.add('hidden'); smcWrap.classList.remove('flex'); }
            if(tfWrap) { tfWrap.classList.add('hidden'); tfWrap.classList.remove('sm:flex'); }
            if(bPanel) { bPanel.classList.add('hidden'); bPanel.classList.remove('flex'); }
            if(snapBtn) { snapBtn.classList.add('hidden'); snapBtn.classList.remove('block'); }

            if (mode === 'tv') {
                if(tv) tv.style.display = 'block';
            } 
            else if (mode === 'custom') {
                if(lw) lw.style.display = 'block';
                if(smcWrap) { smcWrap.classList.remove('hidden'); smcWrap.classList.add('flex'); }
                if(tfWrap) { tfWrap.classList.remove('hidden'); tfWrap.classList.add('sm:flex'); }
                if(snapBtn) { snapBtn.classList.remove('hidden'); snapBtn.classList.add('block'); }
                if(broksumActive && bPanel) { bPanel.classList.remove('hidden'); bPanel.classList.add('flex'); }
            } 
            else if (mode === 'paywall') {
                if(pw) { pw.style.display = 'flex'; }
            }
        }

        function renderTvWidget(symbolCode) {
            setUIMode('tv');
            const container = document.getElementById('tv-container');
            if(!container) return;
            container.innerHTML = '';

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
                "container_id": "tv-container",
                "toolbar_bg": "#18181b",
                "disabled_features": ["header_symbol_search"]
            });
        }

        function renderCustomChart(symbolCode) {
            if (!isPremiumUser) {
                setUIMode('paywall');
                return;
            }

            setUIMode('custom');

            if (customChart) {
                customChart.remove();
                customChart = null;
            }

            const container = document.getElementById('lw-container');
            if(!container) return;

            customChart = LightweightCharts.createChart(container, {
                autoSize: true,
                layout: { background: { type: 'solid', color: '#09090b' }, textColor: '#a1a1aa' },
                grid: { vertLines: { color: '#27272a' }, horzLines: { color: '#27272a' } },
                crosshair: { mode: LightweightCharts.CrosshairMode.Normal },
                rightPriceScale: { borderColor: '#27272a', scaleMargins: { top: 0.1, bottom: 0.25 } },
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
            
            bandarSeries = customChart.addHistogramSeries({
                color: '#3b82f6',
                priceFormat: { type: 'volume' },
                priceScaleId: 'bandar', 
            });

            customChart.priceScale('volume').applyOptions({ scaleMargins: { top: 0.85, bottom: 0 } });
            customChart.priceScale('bandar').applyOptions({ scaleMargins: { top: 0.85, bottom: 0 } });

            // Crosshair Logic untuk Foreign Flow
            customChart.subscribeCrosshairMove((param) => {
                if (!broksumActive || !param.time || param.point.x < 0 || param.point.y < 0) return;

                const hoveredData = customRawData.find(d => d.time === param.time);
                if (hoveredData) {
                    const dateObj = new Date(hoveredData.time * 1000);
                    document.getElementById('bsDate').textContent = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

                    const fBuy = parseFloat(hoveredData.foreign_buy) || 0;
                    const fSell = parseFloat(hoveredData.foreign_sell) || 0;
                    const fNet = fBuy - fSell;

                    document.getElementById('fBuy').textContent = formatVal(fBuy);
                    document.getElementById('fSell').textContent = formatVal(fSell);
                    
                    const elNet = document.getElementById('fNet');
                    elNet.textContent = (fNet > 0 ? '+' : '') + formatVal(fNet);
                    elNet.className = 'text-xs font-black ' + (fNet > 0 ? 'text-emerald-400' : (fNet < 0 ? 'text-red-400' : 'text-zinc-500'));
                }
            });

            fetchDataFromController(symbolCode);
        }

        async function fetchDataFromController(code) {
            showToast(`Memuat data ${currentInterval}...`, "success");
            try {
                const response = await fetch(`/api/chart/${code}?tf=${currentInterval}`);
                const result = await response.json();
                
                if (result.status !== 'success' || !Array.isArray(result.data) || result.data.length === 0) {
                    showToast("Data saham tidak tersedia.", "error");
                    return;
                }

                const formattedData = result.data.map(item => ({
                    time: Math.floor(item.time / 1000), 
                    open: parseFloat(item.open), high: parseFloat(item.high), 
                    low: parseFloat(item.low), close: parseFloat(item.close), 
                    volume: parseInt(item.volume),
                    foreign_buy: parseFloat(item.foreign_buy || 0),
                    foreign_sell: parseFloat(item.foreign_sell || 0)
                }));

                customRawData = formattedData;
                candleSeries.setData(formattedData);
                volumeSeries.setData(formattedData.map(d => ({
                    time: d.time, value: d.volume, color: d.close > d.open ? 'rgba(16, 185, 129, 0.4)' : 'rgba(239, 68, 68, 0.4)' 
                })));
                
                bandarSeries.setData(formattedData.map(d => {
                    let net = d.foreign_buy - d.foreign_sell;
                    return {
                        time: d.time, value: Math.abs(net), 
                        color: net > 0 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(239, 68, 68, 0.8)' 
                    };
                }));

                const visibleBars = 100;
                const fromIdx = Math.max(0, formattedData.length - visibleBars);
                customChart.timeScale().setVisibleLogicalRange({ from: fromIdx, to: formattedData.length - 1 });

                applySmcState();
            } catch (err) {
                console.error(err);
                showToast("Gagal terhubung ke server.", "error");
            }
        }

        function applySmcState() {
            const btnSMC = document.getElementById('toggleSMC');
            if (!btnSMC) return;

            if (smcActive && customRawData.length > 0) {
                if (typeof drawSmartMoneyZones === "function") drawSmartMoneyZones(customRawData, customChart, candleSeries);
                if (typeof calculateMarketStructureAndMarkers === "function") candleSeries.setMarkers(calculateMarketStructureAndMarkers(customRawData));
                btnSMC.textContent = "SMC PRO : ON";
                btnSMC.className = "flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold bg-indigo-600 text-white border border-indigo-500 shadow";
            } else {
                if (candleSeries) candleSeries.setMarkers([]);
                if (typeof clearSmcZones === "function" && candleSeries) clearSmcZones(candleSeries);
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
            if(stockInput) renderTvWidget(stockInput.value);

            // Toggle Broksum/Foreign
            const btnBroksum = document.getElementById('toggleBroksum');
            if(btnBroksum) {
                btnBroksum.addEventListener('click', function() {
                    broksumActive = !broksumActive;
                    const panel = document.getElementById('broksumPanel');
                    if (broksumActive) {
                        this.textContent = "FOREIGN : ON";
                        this.className = "flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold bg-emerald-600 text-white border border-emerald-500 shadow";
                        if (currentMode === 'custom' && panel) { panel.classList.remove('hidden'); panel.classList.add('flex'); }
                    } else {
                        this.textContent = "FOREIGN : OFF";
                        this.className = "flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-bold bg-emerald-900/40 text-emerald-400 border border-emerald-700/50 hover:bg-emerald-800/50 transition-colors";
                        if(panel) { panel.classList.add('hidden'); panel.classList.remove('flex'); }
                    }
                });
            }

            // Timeframe Buttons
            const tfButtons = document.querySelectorAll('.tf-btn');
            tfButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    tfButtons.forEach(b => {
                        b.classList.remove('bg-amber-500', 'text-zinc-950', 'active-tf');
                        b.classList.add('text-zinc-400');
                    });
                    this.classList.remove('text-zinc-400');
                    this.classList.add('bg-amber-500', 'text-zinc-950', 'active-tf');
                    
                    currentInterval = this.getAttribute('data-tf');
                    const code = stockInput ? stockInput.value.toUpperCase().trim() : '';
                    if (code) executeLoad(code);
                });
            });

            // Mode Switching
            const modeTv = document.getElementById('modeTv');
            const modeCustom = document.getElementById('modeCustom');

            if(modeTv) {
                modeTv.addEventListener('click', function() {
                    currentMode = 'tv';
                    this.className = "px-3 py-1 text-xs font-bold rounded bg-amber-500 text-zinc-950 transition-all";
                    if(modeCustom) modeCustom.className = "px-3 py-1 text-xs font-bold rounded text-zinc-400 hover:text-white transition-all";
                    if(stockInput) executeLoad(stockInput.value.toUpperCase().trim());
                });
            }

            if(modeCustom) {
                modeCustom.addEventListener('click', function() {
                    this.className = "px-3 py-1 text-xs font-bold rounded bg-amber-500 text-zinc-950 transition-all";
                    if(modeTv) modeTv.className = "px-3 py-1 text-xs font-bold rounded text-zinc-400 hover:text-white transition-all";
                    currentMode = 'custom';
                    if(stockInput) executeLoad(stockInput.value.toUpperCase().trim());
                });
            }

            if(stockInput) {
                stockInput.addEventListener('input', function () {
                    clearTimeout(typingTimer);
                    const code = this.value.toUpperCase().trim();
                    if (code.length >= 4 || code === 'IHSG') typingTimer = setTimeout(() => { executeLoad(code); }, doneTypingInterval);
                });

                stockInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        clearTimeout(typingTimer);
                        const code = this.value.toUpperCase().trim();
                        if (code) executeLoad(code);
                    }
                });
            }

            const btnSMC = document.getElementById('toggleSMC');
            if(btnSMC) {
                btnSMC.addEventListener('click', () => {
                    smcActive = !smcActive;
                    applySmcState();
                });
            }

            const btnSnap = document.getElementById('btnSnapshot');
            if(btnSnap) {
                btnSnap.addEventListener('click', () => {
                    const canvas = document.querySelector('#lw-container canvas');
                    if (canvas) {
                        const link = document.createElement('a');
                        link.download = `Tradehub-${stockInput.value.toUpperCase()}-${currentInterval}.png`;
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                        showToast("Snapshot chart berhasil diunduh!", "success");
                    }
                });
            }
        });
    </script>
</x-app-layout>