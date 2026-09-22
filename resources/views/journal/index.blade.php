<x-app-layout>
    <div class="py-8 bg-zinc-950 min-h-screen relative">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- HEADER JURNAL -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="z-10">
                    <h2 class="text-3xl font-black text-white flex items-center gap-3 tracking-tight">
                        <svg class="w-8 h-8 text-[#ff9900]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        Trading Journal
                    </h2>
                    <p class="text-gray-400 mt-1">Evaluasi performa portofolio dan akurasi eksekusi teknikal Anda secara berkala.</p>
                </div>
            </div>

            <!-- NOTIFIKASI SUKSES -->
            @if(session('success'))
            <div class="bg-emerald-900/40 border border-emerald-500/50 text-emerald-400 px-6 py-4 rounded-xl flex items-center gap-3 shadow-[0_0_20px_rgba(16,185,129,0.15)]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
            @endif

            <!-- METRIK ANALISIS KINERJA -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Eksekusi -->
                <div class="bg-zinc-900 p-6 rounded-2xl border border-zinc-800 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/10 blur-2xl rounded-full"></div>
                    <p class="text-zinc-400 text-sm font-bold tracking-widest uppercase mb-1">Total Trades</p>
                    <p class="text-3xl font-black text-white">{{ $totalTrades }} <span class="text-base font-medium text-zinc-500">posisi ditutup</span></p>
                </div>

                <!-- Win Rate -->
                <div class="bg-zinc-900 p-6 rounded-2xl border border-zinc-800 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/10 blur-2xl rounded-full"></div>
                    <p class="text-zinc-400 text-sm font-bold tracking-widest uppercase mb-1">Win Rate</p>
                    <div class="flex items-end gap-3">
                        <p class="text-3xl font-black {{ $winRate >= 50 ? 'text-emerald-400' : 'text-red-400' }}">{{ $winRate }}%</p>
                    </div>
                </div>

                <!-- Total PnL -->
                <div class="bg-zinc-900 p-6 rounded-2xl border border-zinc-800 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-[#ff9900]/10 blur-2xl rounded-full"></div>
                    <p class="text-zinc-400 text-sm font-bold tracking-widest uppercase mb-1">Total PnL</p>
                    <p class="text-3xl font-black {{ $totalPnl >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        Rp {{ number_format($totalPnl, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <!-- LAYOUT UTAMA: FORM & TABEL -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- KOLOM KIRI: FORM INPUT JURNAL -->
                <div class="col-span-1 bg-zinc-900 rounded-2xl border border-zinc-800 shadow-2xl p-6 h-fit">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2 border-b border-zinc-800 pb-4">
                        <svg class="w-5 h-5 text-[#ff9900]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Catat Transaksi
                    </h3>
                    
                    <form action="{{ route('journal.store') }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Kode Saham -->
                            <div>
                                <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-1">Ticker</label>
                                <input type="text" name="stock_code" required class="w-full bg-zinc-950 border border-zinc-700 text-white rounded-lg focus:border-[#ff9900] focus:ring focus:ring-orange-900/50 uppercase" placeholder="GSM">
                            </div>
                            
                            <!-- Posisi -->
                            <div>
                                <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-1">Posisi</label>
                                <select name="position" class="w-full bg-zinc-950 border border-zinc-700 text-white rounded-lg focus:border-[#ff9900] focus:ring focus:ring-orange-900/50">
                                    <option value="Long">Long (Buy)</option>
                                    <option value="Short">Short (Sell)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Harga Beli -->
                            <div>
                                <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-1">Harga Entry</label>
                                <input type="number" name="buy_price" required min="1" class="w-full bg-zinc-950 border border-zinc-700 text-white rounded-lg focus:border-[#ff9900] focus:ring focus:ring-orange-900/50" placeholder="1000">
                            </div>
                            
                            <!-- Harga Jual -->
                            <div>
                                <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-1">Harga Exit</label>
                                <input type="number" name="sell_price" min="1" class="w-full bg-zinc-950 border border-zinc-700 text-white rounded-lg focus:border-[#ff9900] focus:ring focus:ring-orange-900/50" placeholder="(Opsional)">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Jumlah Lot -->
                            <div>
                                <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-1">Jumlah Lot</label>
                                <input type="number" name="lots" required min="1" class="w-full bg-zinc-950 border border-zinc-700 text-white rounded-lg focus:border-[#ff9900] focus:ring focus:ring-orange-900/50" placeholder="100">
                            </div>
                            
                            <!-- Tanggal -->
                            <div>
                                <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-1">Tanggal</label>
                                <input type="date" name="trade_date" required value="{{ date('Y-m-d') }}" class="w-full bg-zinc-950 border border-zinc-700 text-white rounded-lg focus:border-[#ff9900] focus:ring focus:ring-orange-900/50 [color-scheme:dark]">
                            </div>
                        </div>

                        <!-- Catatan / Evaluasi -->
                        <div>
                            <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-1">Evaluasi / Setup Notes</label>
                            <textarea name="notes" rows="3" class="w-full bg-zinc-950 border border-zinc-700 text-white rounded-lg focus:border-[#ff9900] focus:ring focus:ring-orange-900/50" placeholder="Contoh: Breakout resisten minor, volume spike..."></textarea>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-[#ff9900] to-amber-500 text-black font-black py-3 rounded-lg hover:scale-[1.02] transition-transform shadow-[0_0_15px_rgba(255,153,0,0.4)] mt-2">
                            SIMPAN KE JURNAL
                        </button>
                    </form>
                </div>

                <!-- KOLOM KANAN: TABEL RIWAYAT TRANSAKSI -->
                <div class="col-span-1 lg:col-span-2 bg-zinc-900 rounded-2xl border border-zinc-800 shadow-2xl overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-zinc-800 bg-zinc-900/50">
                        <h3 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            Log Transaksi
                        </h3>
                    </div>
                    
                    <div class="overflow-x-auto p-0">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-zinc-950 text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                    <th class="p-4 border-b border-zinc-800">Tanggal</th>
                                    <th class="p-4 border-b border-zinc-800">Ticker</th>
                                    <th class="p-4 border-b border-zinc-800">Entry & Exit</th>
                                    <th class="p-4 border-b border-zinc-800">PnL</th>
                                    <th class="p-4 border-b border-zinc-800 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                @forelse($journals as $j)
                                <tr class="border-b border-zinc-800/50 hover:bg-zinc-800/30 transition-colors">
                                    <td class="p-4 text-zinc-300">{{ \Carbon\Carbon::parse($j->trade_date)->format('d M Y') }}</td>
                                    
                                    <td class="p-4">
                                        <div class="font-black text-white text-lg">{{ $j->stock_code }}</div>
                                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded {{ $j->position === 'Long' ? 'bg-blue-900/40 text-blue-400 border border-blue-700/50' : 'bg-red-900/40 text-red-400 border border-red-700/50' }}">
                                            {{ $j->position }} ({{ $j->lots }} Lot)
                                        </span>
                                    </td>
                                    
                                    <td class="p-4">
                                        <div class="text-zinc-300">In: <span class="font-bold">{{ number_format($j->buy_price, 0, ',', '.') }}</span></div>
                                        <div class="text-zinc-500">Out: <span class="font-bold text-zinc-300">{{ $j->sell_price ? number_format($j->sell_price, 0, ',', '.') : 'Open Position' }}</span></div>
                                    </td>
                                    
                                    <td class="p-4">
                                        @if($j->sell_price)
                                            <div class="font-black {{ $j->pnl_amount >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                                {{ $j->pnl_amount >= 0 ? '+' : '' }}Rp {{ number_format($j->pnl_amount, 0, ',', '.') }}
                                            </div>
                                            <div class="text-xs font-bold {{ $j->pnl_percentage >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                                                {{ $j->pnl_percentage >= 0 ? '▲' : '▼' }} {{ number_format($j->pnl_percentage, 2) }}%
                                            </div>
                                        @else
                                            <span class="text-zinc-500 italic text-xs">Waiting Exit</span>
                                        @endif
                                    </td>
                                    
                                    <td class="p-4 text-right">
                                        <form action="{{ route('journal.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Hapus catatan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-zinc-500 hover:text-red-500 transition-colors p-2 rounded hover:bg-red-500/10">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @if($j->notes)
                                <tr class="border-b border-zinc-800/80 bg-zinc-950/20">
                                    <td colspan="5" class="px-4 py-3 text-xs text-zinc-500">
                                        <span class="font-bold text-zinc-400">Notes:</span> {{ $j->notes }}
                                    </td>
                                </tr>
                                @endif
                                @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-zinc-500">
                                        <svg class="w-12 h-12 mx-auto mb-3 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="font-medium text-lg">Belum ada jurnal trading.</p>
                                        <p class="text-sm mt-1">Gunakan form di sebelah kiri untuk mencatat transaksi pertama Anda.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>