let currentTimeframe = '1d';

function setTimeframe(tf) {
    currentTimeframe = tf;
    
    // Update styling tombol timeframe aktif
    document.querySelectorAll('.tf-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.background = 'transparent';
        btn.style.color = 'var(--text-secondary)';
        btn.style.fontWeight = 'normal';
    });
    
    const activeBtn = document.querySelector(`.tf-btn[data-tf="${tf}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
        activeBtn.style.background = 'var(--accent-blue)';
        activeBtn.style.color = '#000'; // Menyesuaikan dengan tema hitam Tradehub
        activeBtn.style.fontWeight = 'bold';
    }

    // Persiapan hook untuk data Intraday / Ivezgo
    if (['1m', '5m', '15m', '1h'].includes(tf)) {
        console.log(`[Ivezgo Ready] Meminta data intraday untuk timeframe: ${tf}`);
    }

    // Panggil ulang chart setiap kali timeframe diganti
    loadStockChart();
}

async function loadStockChart() {
    const container = document.getElementById('tvChartContainer');
    if (!container || typeof LightweightCharts === 'undefined') return;

    const tickerInput = document.getElementById('chartTickerInput');
    const ticker = (tickerInput ? tickerInput.value.trim().toUpperCase() : 'BBCA') || 'BBCA';

    let comp = null;
    if (typeof dashboardData !== 'undefined' && dashboardData.companies) {
        comp = dashboardData.companies.find(c => c.code === ticker);
    }

    if (comp) {
        const nameEl = document.getElementById('chartStockName');
        if (nameEl) nameEl.innerText = comp.name || ticker;

        // Render Tato / Notasi Khusus BEI di sebelah nama saham
        const annoEl = document.getElementById('chartAnnotations');
        if (annoEl) {
            annoEl.innerHTML = '';
            const notations = comp.notasi || (ticker === 'GOTO' || ticker === 'BREN' ? ['X', 'FCA'] : []);
            
            notations.forEach(note => {
                const span = document.createElement('span');
                span.innerText = note;
                span.style.background = 'rgba(239, 68, 68, 0.15)';
                span.style.color = 'var(--accent-red)';
                span.style.border = '1px solid rgba(239, 68, 68, 0.4)';
                span.style.padding = '0.1rem 0.35rem';
                span.style.borderRadius = '4px';
                span.style.fontSize = '0.7rem';
                span.style.fontWeight = '800';
                span.style.letterSpacing = '1px';
                annoEl.appendChild(span);
            });
        }
    }

    if (window.activeChartInstance !== undefined && window.activeChartInstance !== null) {
        try { window.activeChartInstance.remove(); } catch (e) {}
    }
    container.innerHTML = '';

    const tvChart = LightweightCharts.createChart(container, {
        width: container.clientWidth || 800,
        height: 480,
        layout: { background: { color: '#070a13' }, textColor: '#9ca3af' },
        grid: { vertLines: { color: 'rgba(255, 255, 255, 0.05)' }, horzLines: { color: 'rgba(255, 255, 255, 0.05)' } },
        crosshair: { mode: 0 },
        rightPriceScale: { borderColor: 'rgba(255, 255, 255, 0.1)' },
        timeScale: { borderColor: 'rgba(255, 255, 255, 0.1)' },
    });

    window.activeChartInstance = tvChart;

    const candleSeries = tvChart.addCandlestickSeries({
        upColor: '#10b981', downColor: '#ef4444',
        borderUpColor: '#10b981', borderDownColor: '#ef4444',
        wickUpColor: '#10b981', wickDownColor: '#ef4444',
    });

    const volumeSeries = tvChart.addHistogramSeries({
        color: '#3b82f6', priceFormat: { type: 'volume' },
        priceScaleId: '', scaleMargins: { top: 0.8, bottom: 0 },
    });

    window.activeCandleSeries = candleSeries;

    try {
        container.style.opacity = '0.5';

        // ==============================================================
        // LOGIKA PENARIKAN DATA BERDASARKAN TIMEFRAME & BYPASS LIMIT
        // ==============================================================
        let fetchUrl = `/api/stock/${ticker}`;
        
        if (['1m', '5m', '15m', '1h'].includes(currentTimeframe)) {
            // Jika masuk timeframe intraday
            fetchUrl = `/api/stock/${ticker}?limit=390`; 
        } else if (currentTimeframe === '1d') {
            // JIKA DAILY (1D): Gunakan 99999 agar Python tidak menganggapnya kosong (falsy)
            fetchUrl = `/api/stock/${ticker}?limit=99999`;
        } else {
            // JIKA WEEKLY/MONTHLY: Tarik semua data
            fetchUrl = `/api/stock/${ticker}?limit=99999&tf=${currentTimeframe}`;
        }

        // Fetch data menggunakan URL yang sudah dimodifikasi
        const response = await fetch(fetchUrl);
        if (!response.ok) throw new Error(`Data tidak ditemukan (Status: ${response.status})`);
        const dbData = await response.json();

        if (dbData.records && dbData.records.length > 0) {
            const candleData = dbData.records.map(r => ({
                time: r.time, open: r.open, high: r.high, low: r.low, close: r.close
            }));
            const volData = dbData.records.map(r => ({
                time: r.time, value: r.volume || 0,
                color: r.close >= r.open ? 'rgba(16, 185, 129, 0.5)' : 'rgba(239, 68, 68, 0.5)'
            }));

            candleSeries.setData(candleData);
            volumeSeries.setData(volData);

            // SINKRONISASI WIDGET TECHNICAL INDICATORS
            const closes = dbData.records.map(r => r.close);
            const volumes = dbData.records.map(r => r.volume || 0);
            
            const calcSMA = (arr, period) => {
                if (arr.length < period) return arr[arr.length - 1] || 0;
                return arr.slice(-period).reduce((a, b) => a + b, 0) / period;
            };

            const latest = dbData.latest || {};
            const rsi = latest.RSI || 50; 
            const ema20 = latest.EMA_20 || calcSMA(closes, 20);
            const ema50 = latest.EMA_50 || calcSMA(closes, 50);
            const trend = ema20 > ema50 ? 'BULLISH' : 'BEARISH';
            
            const vol20 = calcSMA(volumes, 20);
            const currentVol = volumes[volumes.length - 1] || 0;
            const volRatio = vol20 > 0 ? (currentVol / vol20) : 1.0;

            const rsiEl = document.getElementById('techRsiVal');
            if (rsiEl) rsiEl.innerText = `${rsi.toFixed(1)} (${rsi > 70 ? 'Overbought' : rsi < 30 ? 'Oversold' : 'Neutral'})`;
            
            const trendEl = document.getElementById('techTrendVal');
            if (trendEl) {
                trendEl.innerText = trend;
                trendEl.style.color = trend === 'BULLISH' ? 'var(--accent-green)' : 'var(--accent-red)';
            }
            
            const emaEl = document.getElementById('techEmaVal');
            if (emaEl) emaEl.innerText = `${Math.round(ema20).toLocaleString()} / ${Math.round(ema50).toLocaleString()}`;
            
            const volRatioEl = document.getElementById('techVolRatio');
            if (volRatioEl) {
                volRatioEl.innerText = `${volRatio.toFixed(2)}x`;
                volRatioEl.style.color = volRatio > 1.5 ? 'var(--accent-orange)' : 'var(--text-secondary)';
            }

            // SINKRONISASI WIDGET BANDARMOLOGY
            try {
                const blockRes = await fetch(`/api/stock/${ticker}/blocks`);
                if (blockRes.ok) {
                    const blockData = await blockRes.json();
                    const smartRatio = blockData.smart_accumulation_ratio || 0;
                    
                    let buyers = {};
                    let sellers = {};
                    
                    if (blockData.blocks && blockData.blocks.length > 0) {
                        blockData.blocks.forEach(b => {
                            if (b.buyer_broker) buyers[b.buyer_broker] = (buyers[b.buyer_broker] || 0) + b.value_rp;
                            if (b.seller_broker) sellers[b.seller_broker] = (sellers[b.seller_broker] || 0) + b.value_rp;
                        });
                    }

                    const getTop = (obj) => Object.entries(obj).sort((a,b)=>b[1]-a[1]).slice(0,3).map(x=>x[0]).join(', ');
                    const topBuyers = getTop(buyers) || 'N/A';
                    const topSellers = getTop(sellers) || 'N/A';
                    
                    const brokerEl = document.getElementById('chartBrokerSummary');
                    if (brokerEl) {
                        brokerEl.innerHTML = `
                            <div style="margin-bottom: 0.5rem;"><strong style="color: #fff;">Top Buyer Brokers:</strong> <span style="color: var(--accent-green);">${topBuyers} (${smartRatio > 50 ? 'Accumulation' : 'Mixed'})</span></div>
                            <div style="margin-bottom: 0.5rem;"><strong style="color: #fff;">Top Seller Brokers:</strong> <span style="color: var(--accent-red);">${topSellers} (${smartRatio < 50 ? 'Distribution' : 'Mixed'})</span></div>
                            <div><strong style="color: #fff;">Smart Money Ratio:</strong> <span style="color: var(--accent-gold); font-weight: 700;">${smartRatio.toFixed(1)}%</span> of Whale Trades</div>
                        `;
                    }
                }
            } catch (blockErr) {}

            const markers = [];
            const sortedDates = candleData.map(c => c.time);
            const lastPrice = candleData[candleData.length - 1].close;
            let dps = (comp && comp.dps) || 0;
            
            if (dps > 0 && sortedDates.length >= 25) {
                let yld = ((dps / lastPrice) * 100).toFixed(1);
                markers.push({
                    time: sortedDates[Math.floor(sortedDates.length * 0.45)],
                    position: 'belowBar', color: '#10b981', shape: 'arrowUp',
                    text: `Cum Date: DPS Rp ${dps.toLocaleString()} (${yld}%)`,
                });
                markers.push({
                    time: sortedDates[Math.floor(sortedDates.length * 0.45) + 1],
                    position: 'aboveBar', color: '#ef4444', shape: 'arrowDown',
                    text: `Ex Date: Theo Drop -Rp ${dps.toLocaleString()}`,
                });
                candleSeries.setMarkers(markers.sort((a, b) => (a.time > b.time ? 1 : -1)));
            }
        }
    } catch (error) {
        console.error("Gagal menarik data dari backend:", error);
    } finally {
        container.style.opacity = '1';
        tvChart.timeScale().fitContent();
    }

    window.removeEventListener('resize', window.chartResizeHandler);
    window.chartResizeHandler = () => {
        if (container && window.activeChartInstance) {
            window.activeChartInstance.applyOptions({ width: container.clientWidth });
        }
    };
    window.addEventListener('resize', window.chartResizeHandler);
}