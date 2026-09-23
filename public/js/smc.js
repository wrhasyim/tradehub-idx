/**
 * ============================================================================
 * SMC PRO - ULTIMATE INSTITUTIONAL SUITE (Added BOS/CHoCH Horizontal Lines)
 * ============================================================================
 */

let currentSmcPlugin = null;

function applySmartMoneyConcepts(data, chart, series) {
    if (data.length < 20) return;

    // 1. BERSIHKAN PLUGIN & MARKER LAMA
    if (currentSmcPlugin && typeof series.detachPrimitive === 'function') {
        try { series.detachPrimitive(currentSmcPlugin); } catch(e) {}
    }
    series.setMarkers([]);

    let markers = [];
    let fvgs = [];
    let obs = [];
    let srLevels = [];
    let swings = [];
    let structureLines = []; // Array baru untuk menyimpan garis BOS/CHoCH

    // 2. DETEKSI SWING HIGH & LOW
    let swingLength = 3;
    for (let i = swingLength; i < data.length - swingLength; i++) {
        let isHigh = true, isLow = true;
        for (let j = 1; j <= swingLength; j++) {
            if (data[i - j].high >= data[i].high || data[i + j].high >= data[i].high) isHigh = false;
            if (data[i - j].low <= data[i].low || data[i + j].low <= data[i].low) isLow = false;
        }
        if (isHigh) swings.push({ time: data[i].time, price: data[i].high, type: 'high' });
        if (isLow) swings.push({ time: data[i].time, price: data[i].low, type: 'low' });
    }

    // 3. LOGIKA BOS, CHoCH, DAN GARIS STRUKTUR
    let trend = 0; // 1 = Uptrend, -1 = Downtrend
    let lastHigh = null, lastLow = null;

    swings.forEach(sw => {
        srLevels.push({
            type: sw.type === 'high' ? 'resistance' : 'support',
            price: sw.price, time: sw.time, active: true, retests: 0
        });

        if (sw.type === 'high') {
            if (lastHigh) {
                if (sw.price > lastHigh.price) {
                    let isChoch = trend === -1;
                    if (isChoch) trend = 1;

                    // Buat Garis Horizontal BOS/CHoCH Bullish
                    structureLines.push({
                        type: isChoch ? 'CHoCH' : 'BOS',
                        price: lastHigh.price,      // Diambil dari harga pucuk sebelumnya
                        startTime: lastHigh.time,   // Ditarik dari waktu pucuk sebelumnya
                        endTime: sw.time,           // Sampai waktu pucuk yang baru
                        isBullish: true
                    });

                    markers.push({ time: sw.time, position: 'aboveBar', color: isChoch ? '#3b82f6' : '#22c55e', shape: 'arrowDown', text: isChoch ? 'CHoCH' : 'BOS' });
                } else {
                    markers.push({ time: sw.time, position: 'aboveBar', color: '#ef4444', shape: 'arrowDown', text: 'LH' });
                }
            }
            lastHigh = sw;
        } else if (sw.type === 'low') {
            if (lastLow) {
                if (sw.price < lastLow.price) {
                    let isChoch = trend === 1;
                    if (isChoch) trend = -1;

                    // Buat Garis Horizontal BOS/CHoCH Bearish
                    structureLines.push({
                        type: isChoch ? 'CHoCH' : 'BOS',
                        price: lastLow.price,       // Diambil dari harga lembah sebelumnya
                        startTime: lastLow.time,    // Ditarik dari waktu lembah sebelumnya
                        endTime: sw.time,           // Sampai waktu lembah yang baru
                        isBullish: false
                    });

                    markers.push({ time: sw.time, position: 'belowBar', color: isChoch ? '#f97316' : '#ef4444', shape: 'arrowUp', text: isChoch ? 'CHoCH' : 'BOS' });
                } else {
                    markers.push({ time: sw.time, position: 'belowBar', color: '#22c55e', shape: 'arrowUp', text: 'HL' });
                }
            }
            lastLow = sw;
        }
    });

    series.setMarkers(markers);

    // 4. DETEKSI ORDER BLOCK & FVG (DENGAN IMBALANCE)
    for (let i = 2; i < data.length; i++) {
        let c1 = data[i - 2], c2 = data[i - 1], c3 = data[i];
        let bodySize = Math.abs(c2.close - c2.open);
        let totalSize = c2.high - c2.low;
        let isImbalance = bodySize > (totalSize * 0.6); 

        if (c3.low > c1.high && isImbalance) {
            fvgs.push({ type: 'bull', time: c1.time, top: c3.low, bottom: c1.high, active: true });
            for (let j = i - 2; j >= Math.max(0, i - 10); j--) {
                if (data[j].close < data[j].open) { 
                    obs.push({ type: 'bull', time: data[j].time, top: data[j].high, bottom: data[j].low, active: true });
                    break;
                }
            }
        }
        else if (c3.high < c1.low && isImbalance) {
            fvgs.push({ type: 'bear', time: c1.time, top: c1.low, bottom: c3.high, active: true });
            for (let j = i - 2; j >= Math.max(0, i - 10); j--) {
                if (data[j].close > data[j].open) { 
                    obs.push({ type: 'bear', time: data[j].time, top: data[j].high, bottom: data[j].low, active: true });
                    break;
                }
            }
        }

        // MITIGASI FVG & OB
        fvgs.forEach(fvg => {
            if (!fvg.active) return;
            if (fvg.type === 'bull' && c3.low <= fvg.bottom) fvg.active = false;
            if (fvg.type === 'bear' && c3.high >= fvg.top) fvg.active = false;
        });

        obs.forEach(ob => {
            if (!ob.active) return;
            if (ob.type === 'bull' && c3.close < ob.bottom) ob.active = false;
            if (ob.type === 'bear' && c3.close > ob.top) ob.active = false;
        });

        srLevels.forEach(sr => {
            if (!sr.active) return;
            let tolerance = sr.price * 0.001;
            if (sr.type === 'resistance') {
                if (c3.close > sr.price + tolerance) sr.active = false; 
                else if (c3.high >= sr.price - tolerance && c3.close < sr.price) sr.retests++;
            } else {
                if (c3.close < sr.price - tolerance) sr.active = false; 
                else if (c3.low <= sr.price + tolerance && c3.close > sr.price) sr.retests++;
            }
        });
    }

    // 5. FILTERING
    let activeFVGs = fvgs.filter(z => z.active).slice(-4); 
    let activeOBs = obs.filter(z => z.active).slice(-3);   
    let activeLines = structureLines.slice(-10); // Batasi hanya menampilkan 10 garis BOS/CHoCH terakhir
    let currentPrice = data[data.length - 1].close;
    let activeSR = srLevels.filter(z => z.active)
                           .sort((a, b) => Math.abs(a.price - currentPrice) - Math.abs(b.price - currentPrice))
                           .slice(0, 4); 

    const lastTime = data[data.length - 1].time; 

    // 6. RENDERER CANVAS
    class SMCComplexRenderer {
        constructor(obs, fvgs, sr, lines) {
            this._obs = obs;
            this._fvgs = fvgs;
            this._sr = sr;
            this._lines = lines;
        }
        renderer() {
            return {
                draw: (target) => {
                    target.useBitmapCoordinateSpace(scope => {
                        const ctx = scope.context;
                        const timeScale = chart.timeScale();
                        
                        let endX = timeScale.timeToCoordinate(lastTime);
                        if (endX === null) return;
                        endX *= scope.horizontalPixelRatio;

                        // 1. Gambar Garis Struktur BOS / CHoCH
                        this._lines.forEach(line => {
                            const startX = timeScale.timeToCoordinate(line.startTime);
                            const breakX = timeScale.timeToCoordinate(line.endTime);
                            const y = series.priceToCoordinate(line.price);

                            if (startX !== null && breakX !== null && y !== null) {
                                const x1 = startX * scope.horizontalPixelRatio;
                                const x2 = breakX * scope.horizontalPixelRatio;
                                const py = y * scope.verticalPixelRatio;

                                ctx.beginPath();
                                ctx.lineWidth = 1 * scope.horizontalPixelRatio;
                                
                                // Tentukan warna berdasarkan tipe dan tren
                                if (line.type === 'CHoCH') {
                                    ctx.strokeStyle = line.isBullish ? '#3b82f6' : '#f97316'; // Biru (Bull), Oranye (Bear)
                                } else {
                                    ctx.strokeStyle = line.isBullish ? '#22c55e' : '#ef4444'; // Hijau (Bull), Merah (Bear)
                                }
                                
                                ctx.setLineDash([4, 4]); // Garis putus-putus khas SMC
                                ctx.moveTo(x1, py);
                                ctx.lineTo(x2, py);
                                ctx.stroke();
                                ctx.setLineDash([]);

                                // Label teks di tengah-tengah garis
                                ctx.fillStyle = ctx.strokeStyle;
                                ctx.font = '10px Arial';
                                const textMidX = x1 + (x2 - x1) / 2;
                                ctx.fillText(line.type, textMidX - 12, py - 5);
                            }
                        });

                        // 2. Gambar OB
                        this._obs.forEach(ob => {
                            const startX = timeScale.timeToCoordinate(ob.time);
                            const topY = series.priceToCoordinate(ob.top);
                            const bottomY = series.priceToCoordinate(ob.bottom);
                            if (startX !== null && topY !== null && bottomY !== null) {
                                const x = startX * scope.horizontalPixelRatio;
                                const y1 = topY * scope.verticalPixelRatio;
                                const y2 = bottomY * scope.verticalPixelRatio;
                                const w = endX - x;
                                const h = Math.abs(y2 - y1);
                                const rectTop = Math.min(y1, y2);

                                ctx.fillStyle = ob.type === 'bull' ? 'rgba(34, 197, 94, 0.18)' : 'rgba(239, 68, 68, 0.18)';
                                ctx.fillRect(x, rectTop, Math.max(w, 2), Math.max(h, 2));
                                ctx.strokeStyle = ob.type === 'bull' ? '#22c55e' : '#ef4444';
                                ctx.lineWidth = 1;
                                ctx.strokeRect(x, rectTop, Math.max(w, 2), Math.max(h, 2));
                                ctx.fillStyle = ob.type === 'bull' ? '#22c55e' : '#ef4444';
                                ctx.font = 'bold 11px Arial';
                                ctx.fillText(`OB ${ob.type.toUpperCase()}`, x + 5, rectTop + 14);
                            }
                        });

                        // 3. Gambar FVG
                        this._fvgs.forEach(fvg => {
                            const startX = timeScale.timeToCoordinate(fvg.time);
                            const topY = series.priceToCoordinate(fvg.top);
                            const bottomY = series.priceToCoordinate(fvg.bottom);
                            if (startX !== null && topY !== null && bottomY !== null) {
                                const x = startX * scope.horizontalPixelRatio;
                                const y1 = topY * scope.verticalPixelRatio;
                                const y2 = bottomY * scope.verticalPixelRatio;
                                const w = endX - x;
                                const h = Math.abs(y2 - y1);
                                const rectTop = Math.min(y1, y2);

                                ctx.fillStyle = fvg.type === 'bull' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(244, 63, 94, 0.1)';
                                ctx.fillRect(x, rectTop, Math.max(w, 2), Math.max(h, 2));
                                ctx.fillStyle = fvg.type === 'bull' ? '#10b981' : '#f43f5e';
                                ctx.font = '10px Arial';
                                ctx.fillText('FVG', x + 5, rectTop + 12);
                            }
