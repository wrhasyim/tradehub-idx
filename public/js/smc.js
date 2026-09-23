/**
 * ============================================================================
 * SMC PRO - INSTITUTIONAL SUITE (Master Version: All Bugs Fixed)
 * Features: Dynamic Dealing Range, Clean OB/FVG, Strong/Weak S&R
 * ============================================================================
 */

let currentSmcPlugin = null;

function calculateMarketStructureAndMarkers(data) {
    let markers = [];
    if (data.length < 20) return markers;

    let swings = [];
    for (let i = 3; i < data.length - 3; i++) {
        let curr = data[i];
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
    let structLines = [];
    let srLines = [];
    const lastTime = data[data.length - 1].time;
    const currentPrice = data[data.length - 1].close;

    let swings = [];
    for (let i = 3; i < data.length - 3; i++) {
        let curr = data[i];
        if (curr.high === curr.low) continue;

        let isHigh = true, isLow = true;
        for (let j = 1; j <= 3; j++) {
            if (data[i - j].high >= curr.high || data[i + j].high >= curr.high) isHigh = false;
            if (data[i - j].low <= curr.low || data[i + j].low <= curr.low) isLow = false;
        }
        if (isHigh) swings.push({ index: i, time: curr.time, price: curr.high, type: 'high', close: curr.close });
        if (isLow) swings.push({ index: i, time: curr.time, price: curr.low, type: 'low', close: curr.close });
    }

    if (swings.length === 0) return;

    // --- DEKLARASI GLOBAL AGAR TIDAK ERROR ---
    let midPrice = currentPrice; 
    let rangeStart = data[0].time;

    // 1. Dealing Range Makro (Premium & Discount Zone) - VALID SMC LOGIC
    let lastHighSwing = null;
    let lastLowSwing = null;

    for (let i = swings.length - 1; i >= 0; i--) {
        if (swings[i].type === 'high' && !lastHighSwing) lastHighSwing = swings[i];
        if (swings[i].type === 'low' && !lastLowSwing) lastLowSwing = swings[i];
        if (lastHighSwing && lastLowSwing) break;
    }

    if (lastHighSwing && lastLowSwing) {
        let topPrice = Math.max(lastHighSwing.price, currentPrice);
        let bottomPrice = Math.min(lastLowSwing.price, currentPrice);
        rangeStart = Math.min(lastHighSwing.time, lastLowSwing.time);
        midPrice = (topPrice + bottomPrice) / 2;

        boxes.push({
            startTime: rangeStart, endTime: lastTime,
            top: topPrice, bottom: midPrice,
            color: 'rgba(239, 68, 68, 0.02)', borderColor: 'rgba(239, 68, 68, 0.15)', label: 'Premium Zone'
        });
        boxes.push({
            startTime: rangeStart, endTime: lastTime,
            top: midPrice, bottom: bottomPrice,
            color: 'rgba(16, 185, 129, 0.02)', borderColor: 'rgba(16, 185, 129, 0.15)', label: 'Discount Zone'
        });
    }

    // 2. Deteksi Order Block (Hanya Menampilkan yang Valid / Belum Terlewati)
    let validOBs = [];

    for (let i = 2; i < data.length - 2; i++) {
        let curr = data[i];
        let next = data[i + 1];

        // Bearish OB
        if (curr.close < curr.open && next.close < next.open && next.close < curr.low) {
            let obTop = curr.high;
            let obBottom = curr.low;
            let isBroken = false;

            for (let k = i + 2; k < data.length; k++) {
                if (data[k].close > obTop) {
                    isBroken = true;
                    break;
                }
            }

            if (!isBroken) {
                validOBs.push({
                    startTime: curr.time, endTime: lastTime,
                    top: obTop, bottom: obBottom,
                    color: 'rgba(239, 68, 68, 0.18)', borderColor: 'rgba(239, 68, 68, 0.6)', label: 'OB BEAR'
                });
            }
        }

        // Bullish OB
        if (curr.close > curr.open && next.close > next.open && next.close > curr.high) {
            let obTop = curr.high;
            let obBottom = curr.low;
            let isBroken = false;

            for (let k = i + 2; k < data.length; k++) {
                if (data[k].close < obBottom) {
                    isBroken = true;
                    break;
                }
            }

            if (!isBroken) {
                validOBs.push({
                    startTime: curr.time, endTime: lastTime,
                    top: obTop, bottom: obBottom,
                    color: 'rgba(16, 185, 129, 0.18)', borderColor: 'rgba(16, 185, 129, 0.6)', label: 'OB BULL'
                });
            }
        }
    }

    if (validOBs.length > 0) boxes.push(...validOBs.slice(-3));

    // 3. Deteksi Fair Value Gap (Hanya FVG yang Belum Tertutup)
    let validFVGs = [];
    for (let i = 1; i < data.length - 1; i++) {
        let prev = data[i - 1];
        let next = data[i + 1];

        // Bullish FVG
        if (prev.high < next.low) {
            let fvgTop = next.low;
            let fvgBottom = prev.high;
            let isFilled = false;

            for (let k = i + 2; k < data.length; k++) {
                if (data[k].low <= fvgBottom) {
                    isFilled = true;
                    break;
                }
            }

            if (!isFilled) {
                validFVGs.push({
                    startTime: prev.time, endTime: lastTime,
                    top: fvgTop, bottom: fvgBottom,
                    color: 'rgba(16, 185, 129, 0.12)', borderColor: 'rgba(16, 185, 129, 0.4)', label: 'FVG BULL'
                });
            }
        }

        // Bearish FVG
        if (prev.low > next.high) {
            let fvgTop = prev.low;
            let fvgBottom = next.high;
            let isFilled = false;

            for (let k = i + 2; k < data.length; k++) {
                if (data[k].high >= fvgTop) {
                    isFilled = true;
                    break;
                }
            }

            if (!isFilled) {
                validFVGs.push({
                    startTime: prev.time, endTime: lastTime,
                    top: fvgTop, bottom: fvgBottom,
                    color: 'rgba(239, 68, 68, 0.12)', borderColor: 'rgba(239, 68, 68, 0.4)', label: 'FVG BEAR'
                });
            }
        }
    }

    if (validFVGs.length > 0) boxes.push(...validFVGs.slice(-3));

    // 4. Struktur BOS Aktif (Dibatasi 2 terakhir)
    let currentTrend = 'neutral';
    let lastValidHigh = null;
    let lastValidLow = null;

    swings.forEach((sw) => {
        if (sw.type === 'high') {
            if (lastValidHigh && sw.close > lastValidHigh.price) {
                currentTrend = 'bullish';
                structLines.push({
                    startTime: lastValidHigh.time, endTime: lastTime,
                    price: lastValidHigh.price,
                    color: '#10b981', label: 'BOS'
                });
            }
            lastValidHigh = sw;
        } else if (sw.type === 'low') {
            if (lastValidLow && sw.close < lastValidLow.price) {
                currentTrend = 'bearish';
                structLines.push({
                    startTime: lastValidLow.time, endTime: lastTime,
                    price: lastValidLow.price,
                    color: '#ef4444', label: 'BOS'
                });
            }
            lastValidLow = sw;
        }
    });

    if (structLines.length > 2) structLines = structLines.slice(-2);

    // 5. LOGIKA WEAK vs STRONG SUPPORT & RESISTANCE (Menghilang Jika Jebol)
    let rawSR = [];
    let majorSwings = swings.slice(-8); 

    majorSwings.forEach(sw => {
        let levelPrice = sw.price;
        let retests = 0;
        let tolerance = levelPrice * 0.008; 
        let isBroken = false;
        let lastRetestIndex = sw.index; 

        for (let j = sw.index + 1; j < data.length; j++) {
            let candle = data[j];
            
            if (sw.type === 'high' && candle.close > levelPrice + tolerance) {
                isBroken = true;
                break;
            } else if (sw.type === 'low' && candle.close < levelPrice - tolerance) {
                isBroken = true;
                break;
            }

            if (Math.abs(candle.high - levelPrice) <= tolerance || Math.abs(candle.low - levelPrice) <= tolerance) {
                if (j - lastRetestIndex > 2) {
                    retests++;
                    lastRetestIndex = j;
                }
            }
        }

        if (!isBroken) {
            let isRes = sw.type === 'high';
            let isStrong = retests >= 3; 
            
            rawSR.push({
                startTime: sw.time,
                endTime: lastTime,
                price: levelPrice,
                color: isRes ? '#ef4444' : '#10b981',
                strength: isStrong, 
                label: `${isStrong ? 'Strong' : 'Weak'} ${isRes ? 'Res' : 'Sup'} (${retests}x)`
            });
        }
    });

    rawSR.sort((a, b) => b.price - a.price);
    rawSR.forEach(sr => {
        let isTooClose = srLines.some(existing => Math.abs(existing.price - sr.price) / sr.price < 0.015);
        if (!isTooClose) srLines.push(sr);
    });

    // Canvas Renderer
    class SMCRenderer {
        constructor(boxList, sLines, srList, midPriceVal, rStart) {
            this._boxes = boxList;
            this._sLines = sLines;
            this._srList = srList;
            this._midPriceVal = midPriceVal;
            this._rStart = rStart;
        }
        update() {}
        renderer() {
            const boxes = this._boxes;
            const sLines = this._sLines;
            const srList = this._srList;
            const midPriceVal = this._midPriceVal;
            const rStart = this._rStart;

            return {
                draw: (target) => {
                    target.useBitmapCoordinateSpace(scope => {
                        const ctx = scope.context;
                        const timeScale = chart.timeScale();

                        // A. Render Kotak Dealing Range, OB, & FVG
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

                                if (box.label && height > 10 * scope.verticalPixelRatio) {
                                    ctx.fillStyle = box.borderColor;
                                    ctx.font = 'bold 9px sans-serif';
                                    ctx.fillText(box.label, left + (6 * scope.horizontalPixelRatio), top + (12 * scope.verticalPixelRatio));
                                }
                            }
                        });

                        // B. Garis Equilibrium 50%
                        const midY = series.priceToCoordinate(midPriceVal);
                        const startX = timeScale.timeToCoordinate(rStart);
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

                        // C. Garis Struktur BOS Aktif
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

                        // D. Garis Support & Resistance (Weak vs Strong)
                        srList.forEach(sr => {
                            const sx1 = timeScale.timeToCoordinate(sr.startTime);
                            const sx2 = timeScale.timeToCoordinate(sr.endTime);
                            const sy = series.priceToCoordinate(sr.price);
                            if (sx1 !== null && sx2 !== null && sy !== null) {
                                ctx.beginPath();
                                ctx.strokeStyle = sr.color;
                                
                                ctx.lineWidth = (sr.strength ? 2 : 1) * scope.horizontalPixelRatio;
                                ctx.setLineDash(sr.strength ? [] : [3, 3]); 
                                
                                ctx.moveTo(sx1 * scope.horizontalPixelRatio, sy * scope.verticalPixelRatio);
                                ctx.lineTo(sx2 * scope.horizontalPixelRatio, sy * scope.verticalPixelRatio);
                                ctx.stroke();
                                ctx.setLineDash([]);

                                ctx.fillStyle = sr.color;
                                ctx.font = sr.strength ? 'bold 10px sans-serif' : '9px sans-serif';
                                ctx.fillText(sr.label, sx1 * scope.horizontalPixelRatio + 6, sy * scope.verticalPixelRatio - 4);
                            }
                        });
                    });
                }
            };
        }
    }

    class SMCPlugin {
        constructor(boxList, sLines, srList, midPriceVal, rStart) {
            this._paneView = { renderer: () => new SMCRenderer(boxList, sLines, srList, midPriceVal, rStart).renderer() };
        }
        paneViews() { return [this._paneView]; }
    }

    currentSmcPlugin = new SMCPlugin(boxes, structLines, srLines, midPrice, rangeStart);
    if (typeof series.attachPrimitive === 'function') {
        series.attachPrimitive(currentSmcPlugin);
    }
}