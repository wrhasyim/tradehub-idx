/**
 * ============================================================================
 * SMC PRO - INSTITUTIONAL SUITE (With Volatility & S&R Clustering Filters)
 * ============================================================================
 */

let currentSmcPlugin = null;

function calculateMarketStructureAndMarkers(data) {
    let markers = [];
    if (data.length < 20) return markers;

    let swings = [];
    for (let i = 3; i < data.length - 3; i++) {
        let curr = data[i];
        // Filter Volatilitas: Abaikan candle yang flat / tidak punya rentang harga (High == Low)
        if (curr.high === curr.low) continue;

        let isHigh = true, isLow = true;
        for (let j = 1; j <= 3; j++) {
            if (data[i - j].high >= curr.high || data[i + j].high >= curr.high) isHigh = false;
            if (data[i - j].low <= curr.low || data[i + j].low <= curr.low) isLow = false;
        }
        if (isHigh) swings.push({ time: curr.time, price: curr.high, type: 'high', close: curr.close });
        if (isLow) swings.push({ time: curr.time, price: curr.low, type: 'low', close: curr.close });
    }

    let lastHigh = null;
    let lastLow = null;

    swings.forEach(sw => {
        if (sw.type === 'high') {
            if (lastHigh) {
                if (sw.price > lastHigh.price) {
                    markers.push({ time: sw.time, position: 'aboveBar', color: '#10b981', shape: 'arrowDown', text: 'HH' });
                } else {
                    markers.push({ time: sw.time, position: 'aboveBar', color: '#ef4444', shape: 'arrowDown', text: 'LH' });
                }
            } else {
                markers.push({ time: sw.time, position: 'aboveBar', color: '#94a3b8', shape: 'arrowDown', text: 'H' });
            }
            lastHigh = sw;
        } else if (sw.type === 'low') {
            if (lastLow) {
                if (sw.price > lastLow.price) {
                    markers.push({ time: sw.time, position: 'belowBar', color: '#10b981', shape: 'arrowUp', text: 'HL' });
                } else {
                    markers.push({ time: sw.time, position: 'belowBar', color: '#f59e0b', shape: 'arrowUp', text: 'LL' });
                }
            } else {
                markers.push({ time: sw.time, position: 'belowBar', color: '#94a3b8', shape: 'arrowUp', text: 'L' });
            }
            lastLow = sw;
        }
    });

    return markers;
}

function clearSmcZones(series) {
    if (currentSmcPlugin && series && typeof series.detachPrimitive === 'function') {
        try { series.detachPrimitive(currentSmcPlugin); } catch(e) {}
        currentSmcPlugin = null;
    }
}

function drawSmartMoneyZones(data, chart, series) {
    clearSmcZones(series);
    if (data.length < 20) return;

    let boxes = [];
    let zigzagPoints = [];
    let structLines = [];
    let srLines = [];
    const lastTime = data[data.length - 1].time;

    let swings = [];
    for (let i = 3; i < data.length - 3; i++) {
        let curr = data[i];
        if (curr.high === curr.low) continue; // Abaikan candle flat

        let isHigh = true, isLow = true;
        for (let j = 1; j <= 3; j++) {
            if (data[i - j].high >= curr.high || data[i + j].high >= curr.high) isHigh = false;
            if (data[i - j].low <= curr.low || data[i + j].low <= curr.low) isLow = false;
        }
        if (isHigh) swings.push({ index: i, time: curr.time, price: curr.high, type: 'high', close: curr.close });
        if (isLow) swings.push({ index: i, time: curr.time, price: curr.low, type: 'low', close: curr.close });
    }

    if (swings.length === 0) return;

    // 1. Dealing Range Makro
    let recentSwings = swings.slice(-10);
    if (recentSwings.length < 2) recentSwings = swings;

    let macroHigh = recentSwings.reduce((max, s) => s.price > max.price ? s : max, recentSwings[0]);
    let macroLow = recentSwings.reduce((min, s) => s.price < min.price ? s : min, recentSwings[0]);

    let rangeStart = Math.min(macroHigh.time, macroLow.time);
    let topPrice = macroHigh.price;
    let bottomPrice = macroLow.price;
    let midPrice = (topPrice + bottomPrice) / 2;

    boxes.push({
        startTime: rangeStart, endTime: lastTime,
        top: topPrice, bottom: midPrice,
        color: 'rgba(239, 68, 68, 0.03)', borderColor: 'rgba(239, 68, 68, 0.2)', label: 'Swing Range High (Premium)'
    });
    boxes.push({
        startTime: rangeStart, endTime: lastTime,
        top: midPrice, bottom: bottomPrice,
        color: 'rgba(16, 185, 129, 0.03)', borderColor: 'rgba(16, 185, 129, 0.2)', label: 'Swing Range Low (Discount)'
    });

    // 2. Struktur BOS & ChoCh
    let currentTrend = 'neutral';
    let lastValidHigh = null;
    let lastValidLow = null;

    swings.forEach((sw) => {
        if (sw.type === 'high') {
            if (lastValidHigh && sw.close > lastValidHigh.price) {
                let isChoCh = (currentTrend === 'bearish');
                currentTrend = 'bullish';
                structLines.push({
                    startTime: lastValidHigh.time, endTime: lastTime,
                    price: lastValidHigh.price,
                    color: '#3b82f6', label: isChoCh ? 'ChoCh' : 'BOS'
                });
            }
            lastValidHigh = sw;
        } else if (sw.type === 'low') {
            if (lastValidLow && sw.close < lastValidLow.price) {
                let isChoCh = (currentTrend === 'bullish');
                currentTrend = 'bearish';
                structLines.push({
                    startTime: lastValidLow.time, endTime: lastTime,
                    price: lastValidLow.price,
                    color: '#ef4444', label: isChoCh ? 'ChoCh' : 'BOS'
                });
            }
            lastValidLow = sw;
        }
        zigzagPoints.push({ time: sw.time, price: sw.price });
    });

    // 3. AUTO SUPPORT & RESISTANCE DENGAN CLUSTERING FILTER (Mencegah garis menumpuk)
    let rawSR = [];
    let majorSwings = swings.slice(-8); 

    majorSwings.forEach(sw => {
        let levelPrice = sw.price;
        let retests = 0;
        let tolerance = levelPrice * 0.008; // Toleransi kedekatan 0.8%

        for (let j = sw.index + 1; j < data.length; j++) {
            let candle = data[j];
            if (Math.abs(candle.high - levelPrice) <= tolerance || Math.abs(candle.low - levelPrice) <= tolerance) {
                retests++;
            }
        }

        let isRes = sw.type === 'high';
        rawSR.push({
            startTime: sw.time,
            endTime: lastTime,
            price: levelPrice,
            color: isRes ? '#ef4444' : '#10b981',
            label: `${isRes ? 'Res' : 'Sup'} (Retests: ${retests})`
        });
    });

    // Filter Clustering: Hapus garis S&R yang harganya terlalu berdekatan (jarak < 1.5%) agar tidak numpuk
    rawSR.sort((a, b) => b.price - a.price);
    rawSR.forEach(sr => {
        let isTooClose = srLines.some(existing => Math.abs(existing.price - sr.price) / sr.price < 0.015);
        if (!isTooClose) {
            srLines.push(sr);
        }
    });

    // Canvas Renderer
    class SMCRenderer {
        constructor(boxList, zzPoints, sLines, srList, midPriceVal) {
            this._boxes = boxList;
            this._zzPoints = zzPoints;
            this._sLines = sLines;
            this._srList = srList;
            this._midPriceVal = midPriceVal;
        }
        update() {}
        renderer() {
            const boxes = this._boxes;
            const zzPoints = this._zzPoints;
            const sLines = this._sLines;
            const srList = this._srList;
            const midPriceVal = this._midPriceVal;
            return {
                draw: (target) => {
                    target.useBitmapCoordinateSpace(scope => {
                        const ctx = scope.context;
                        const timeScale = chart.timeScale();

                        // A. Render Kotak Dealing Range
                        boxes.forEach(box => {
                            const x1 = timeScale.timeToCoordinate(box.startTime);
                            const x2 = timeScale.timeToCoordinate(box.endTime);
                            const y1 = series.priceToCoordinate(box.top);
                            const y2 = series.priceToCoordinate(box.bottom);

                            if (x1 !== null && x2 !== null && y1 !== null && y2 !== null) {
                                const left = Math.min(x1, x2) * scope.horizontalPixelRatio;
                                const top = Math.min(y1, y2) * scope.verticalPixelRatio;
                                const width = Math.max(2, Math.abs(x2 - x1)) * scope.horizontalPixelRatio;
                                const height = Math.max(2, Math.abs(y2 - y1)) * scope.verticalPixelRatio;

                                ctx.fillStyle = box.color;
                                ctx.fillRect(left, top, width, height);

                                ctx.strokeStyle = box.borderColor;
                                ctx.lineWidth = 1 * scope.horizontalPixelRatio;
                                ctx.strokeRect(left, top, width, height);

                                if (box.label && height > 14 * scope.verticalPixelRatio) {
                                    ctx.fillStyle = box.borderColor;
                                    ctx.font = 'bold 9px sans-serif';
                                    ctx.fillText(box.label, left + (6 * scope.horizontalPixelRatio), top + (13 * scope.verticalPixelRatio));
                                }
                            }
                        });

                        // B. Garis Equilibrium 50%
                        const midY = series.priceToCoordinate(midPriceVal);
                        const startX = timeScale.timeToCoordinate(rangeStart);
                        const endX = timeScale.timeToCoordinate(lastTime);
                        if (midY !== null && startX !== null && endX !== null) {
                            ctx.beginPath();
                            ctx.strokeStyle = 'rgba(161, 161, 170, 0.4)';
                            ctx.lineWidth = 1 * scope.horizontalPixelRatio;
                            ctx.setLineDash([4, 4]);
                            ctx.moveTo(startX * scope.horizontalPixelRatio, midY * scope.verticalPixelRatio);
                            ctx.lineTo(endX * scope.horizontalPixelRatio, midY * scope.verticalPixelRatio);
                            ctx.stroke();
                            ctx.setLineDash([]);
                        }

                        // C. Garis Struktur BOS & ChoCh
                        sLines.forEach(line => {
                            const lx1 = timeScale.timeToCoordinate(line.startTime);
                            const lx2 = timeScale.timeToCoordinate(line.endTime);
                            const ly = series.priceToCoordinate(line.price);
                            if (lx1 !== null && lx2 !== null && ly !== null) {
                                ctx.beginPath();
                                ctx.strokeStyle = line.color;
                                ctx.lineWidth = 1.5 * scope.horizontalPixelRatio;
                                ctx.moveTo(lx1 * scope.horizontalPixelRatio, ly * scope.verticalPixelRatio);
                                ctx.lineTo(lx2 * scope.horizontalPixelRatio, ly * scope.verticalPixelRatio);
                                ctx.stroke();

                                ctx.fillStyle = line.color;
                                ctx.font = 'bold 10px sans-serif';
                                ctx.fillText(line.label, lx1 * scope.horizontalPixelRatio + 5, ly * scope.verticalPixelRatio - 4);
                            }
                        });

                        // D. Garis Support & Resistance Terfilter (Tanpa Tumpukan)
                        srList.forEach(sr => {
                            const sx1 = timeScale.timeToCoordinate(sr.startTime);
                            const sx2 = timeScale.timeToCoordinate(sr.endTime);
                            const sy = series.priceToCoordinate(sr.price);
                            if (sx1 !== null && sx2 !== null && sy !== null) {
                                ctx.beginPath();
                                ctx.strokeStyle = sr.color;
                                ctx.lineWidth = 1.2 * scope.horizontalPixelRatio;
                                ctx.moveTo(sx1 * scope.horizontalPixelRatio, sy * scope.verticalPixelRatio);
                                ctx.lineTo(sx2 * scope.horizontalPixelRatio, sy * scope.verticalPixelRatio);
                                ctx.stroke();

                                ctx.fillStyle = sr.color;
                                ctx.font = 'bold 9px sans-serif';
                                ctx.fillText(sr.label, sx1 * scope.horizontalPixelRatio + 6, sy * scope.verticalPixelRatio - 3);
                            }
                        });

                        // E. Garis Zigzag Tren
                        if (zzPoints.length > 1) {
                            ctx.beginPath();
                            ctx.strokeStyle = 'rgba(245, 158, 11, 0.5)';
                            ctx.lineWidth = 1.5 * scope.horizontalPixelRatio;
                            let first = true;
                            zzPoints.forEach(pt => {
                                const x = timeScale.timeToCoordinate(pt.time);
                                const y = series.priceToCoordinate(pt.price);
                                if (x !== null && y !== null) {
                                    const cx = x * scope.horizontalPixelRatio;
                                    const cy = y * scope.verticalPixelRatio;
                                    if (first) { ctx.moveTo(cx, cy); first = false; }
                                    else { ctx.lineTo(cx, cy); }
                                }
                            });
                            ctx.stroke();
                        }
                    });
                }
            };
        }
    }

    class SMCPlugin {
        constructor(boxList, zzPoints, sLines, srList, midPriceVal) {
            this._paneView = { renderer: () => new SMCRenderer(boxList, zzPoints, sLines, srList, midPriceVal).renderer() };
        }
        paneViews() { return [this._paneView]; }
    }

    currentSmcPlugin = new SMCPlugin(boxes, zigzagPoints, structLines, srLines, midPrice);
    if (typeof series.attachPrimitive === 'function') {
        series.attachPrimitive(currentSmcPlugin);
    }
}