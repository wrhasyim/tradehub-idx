<x-app-layout>
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- DASHBOARD GRID LAYOUT -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- KOLOM KIRI: Blackchip Signals -->
                <div class="col-span-1 bg-zinc-900 rounded-2xl p-6 border border-red-900/50 shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-red-600/10 blur-3xl rounded-full"></div>
                    
                    <div class="flex items-center justify-between mb-6 relative z-10">
                        <h3 class="font-bold text-red-500 tracking-wider flex items-center gap-2">
                            <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            BLACKCHIP
                        </h3>
                        <span class="px-2 py-1 bg-red-900/50 text-red-400 text-xs rounded border border-red-700/50">LIVE</span>
                    </div>

                    <div class="space-y-3 relative z-10">
                        <div class="bg-black/60 p-4 rounded-xl border border-gray-800 hover:border-red-500/50 transition-colors">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-white text-lg">STRK</span>
                                <span class="text-xs font-bold text-green-500 bg-green-500/10 px-2 py-1 rounded">BUY SIGNAL</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Entry: 120 - 122</span>
                                <span class="text-red-400">SL: < 115</span>
                            </div>
                        </div>
                        <div class="bg-black/60 p-4 rounded-xl border border-gray-800 hover:border-red-500/50 transition-colors">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-white text-lg">CGAS</span>
                                <span class="text-xs font-bold text-red-500 bg-red-500/10 px-2 py-1 rounded">SELL ALERT</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Momentum Drop</span>
                                <span class="text-gray-300">Exit: 185</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: Watchlist & Disclosure -->
                <div class="col-span-1 lg:col-span-2 space-y-6">
                    
                    <!-- Panel Atas: Watchlist AI -->
                    <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                AI Watchlist
                            </h3>
                            <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All &rarr;</button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-100 text-sm text-gray-500">
                                        <th class="pb-3 font-medium">Stock</th>
                                        <th class="pb-3 font-medium">Status</th>
                                        <th class="pb-3 font-medium">Target Plan</th>
                                        <th class="pb-3 font-medium">AI Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                        <td class="py-4 font-bold text-gray-900">BRPT</td>
                                        <td class="py-4"><span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Watching</span></td>
                                        <td class="py-4 text-gray-600">Buy on Breakout 1050</td>
                                        <td class="py-4 text-gray-500 italic">Volume akumulasi meningkat dalam 3 hari terakhir.</td>
                                    </tr>
                                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                        <td class="py-4 font-bold text-gray-900">AMMN</td>
                                        <td class="py-4"><span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Triggered</span></td>
                                        <td class="py-4 text-gray-600">TP: 9200 | SL: 8500</td>
                                        <td class="py-4 text-gray-500 italic">MACD Golden Cross, sentimen harga tembaga global positif.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Panel Bawah: Listed Company Disclosure & E-IPO -->
                    <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                        <h3 class="font-bold text-gray-800 text-lg mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                            Listed Company Disclosure & E-IPO
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border-l-4 border-purple-500 pl-4">
                                <span class="text-xs font-bold text-purple-600 bg-purple-50 px-2 py-1 rounded">UPCOMING IPO</span>
                                <h4 class="font-bold text-gray-900 mt-2">PT Global Sukses Makmur Tbk (GSM)</h4>
                                <p class="text-sm text-gray-600 mt-1">Book Building: 25 - 28 Sep 2026</p>
                                <p class="text-sm text-gray-600">Harga Penawaran: Rp 200 - 250</p>
                            </div>
                            <div class="border-l-4 border-blue-500 pl-4">
                                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">DISCLOSURE</span>
                                <h4 class="font-bold text-gray-900 mt-2">BREN - Free Float Update</h4>
                                <p class="text-sm text-gray-600 mt-1">Laporan porsi saham masyarakat (non-warkat) berada di angka 11.5% per Q3 2026.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>