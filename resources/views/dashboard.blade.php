<x-app-layout>
    <!-- Alpine.js x-data mengontrol state showUpgradeModal -->
    <div x-data="{ showUpgradeModal: false }" class="py-8 bg-zinc-950 min-h-screen relative">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Notifikasi Sukses Upgrade VIP -->
            @if(session('vip_success'))
            <div class="bg-orange-900/40 border border-[#ff9900] text-[#ff9900] px-6 py-4 rounded-xl relative shadow-[0_0_20px_rgba(255,153,0,0.2)] mb-6 flex items-center gap-3" role="alert">
                <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                <span class="block sm:inline font-bold">{{ session('vip_success') }}</span>
            </div>
            @endif

            <!-- DASHBOARD GRID LAYOUT -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- KOLOM KIRI: Blackchip Signals -->
                <div class="col-span-1 bg-zinc-900 rounded-2xl p-6 border border-zinc-800 shadow-2xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-[#ff9900]/10 blur-3xl rounded-full"></div>
                    
                    <div class="flex items-center justify-between mb-6 relative z-10">
                        <h3 class="font-bold text-[#ff9900] tracking-wider flex items-center gap-2">
                            <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            BLACKCHIP
                        </h3>
                        <span class="px-2 py-1 bg-orange-900/30 text-[#ff9900] text-xs rounded border border-orange-700/30">LIVE</span>
                    </div>

                    <div class="space-y-3 relative z-10">
                        <div class="bg-black/60 p-4 rounded-xl border border-zinc-800">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-white text-lg">STRK</span>
                                <span class="text-xs font-bold text-green-500 bg-green-500/10 px-2 py-1 rounded">BUY SIGNAL</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Entry: 120 - 122</span>
                                <span class="text-red-400">SL: < 115</span>
                            </div>
                        </div>
                    </div>

                    <!-- OVERLAY SENSOR KIRI -->
                    @if(auth()->user()->role === 'regular')
                    <div @click="showUpgradeModal = true" class="absolute inset-0 z-20 backdrop-blur-md bg-zinc-950/70 flex flex-col items-center justify-center cursor-pointer transition-all hover:bg-zinc-950/80">
                        <svg class="w-12 h-12 text-[#ff9900] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <span class="text-[#ff9900] font-bold tracking-wider">VIP EXCLUSIVE</span>
                        <span class="text-gray-400 text-xs mt-1">Click to unlock signals</span>
                    </div>
                    @endif
                </div>

                <!-- KOLOM KANAN: Watchlist & Disclosure -->
                <div class="col-span-1 lg:col-span-2 space-y-6">
                    
                    <!-- Panel Atas: Watchlist AI -->
                    <div class="bg-zinc-900 rounded-2xl p-6 border border-zinc-800 shadow-xl relative overflow-hidden">
                        <div class="flex justify-between items-center mb-6 relative z-10">
                            <h3 class="font-bold text-gray-200 text-lg flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#ff9900]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                AI Watchlist
                            </h3>
                        </div>
                        
                        <div class="overflow-x-auto relative z-10">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-zinc-800 text-sm text-gray-400">
                                        <th class="pb-3 font-medium">Stock</th>
                                        <th class="pb-3 font-medium">Status</th>
                                        <th class="pb-3 font-medium">Target Plan</th>
                                        <th class="pb-3 font-medium">AI Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    @forelse($watchlists ?? [] as $stock)
                                    <tr class="border-b border-zinc-800/50 hover:bg-zinc-800/30">
                                        <td class="py-4 font-bold text-gray-200">{{ $stock->stock_code }}</td>
                                        <td class="py-4">
                                            <span class="px-2 py-1 {{ strtolower($stock->status) === 'triggered' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }} rounded-full text-xs font-medium">
                                                {{ ucfirst($stock->status) }}
                                            </span>
                                        </td>
                                        <td class="py-4 text-gray-400">
                                            @if(auth()->user()->role === 'regular')
                                                <span class="bg-zinc-800 text-zinc-600 select-none rounded px-2 py-1 blur-[2px]">TP: *** | SL: ***</span>
                                            @else
                                                @if(strtolower($stock->status) === 'watching')
                                                    Buy on Breakout {{ $stock->entry_price }}
                                                @else
                                                    TP: {{ $stock->target_price }} | SL: {{ $stock->stop_loss }}
                                                @endif
                                            @endif
                                        </td>
                                        <td class="py-4 text-gray-500 italic">
                                            @if(auth()->user()->role === 'regular')
                                                <span class="bg-zinc-800 text-zinc-600 select-none rounded px-2 py-1 blur-[2px]">Analisis AI dikunci.</span>
                                            @else
                                                {{ $stock->ai_analysis_notes }}
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="py-4 text-center text-gray-500">Belum ada sinyal AI hari ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- OVERLAY SENSOR KANAN -->
                        @if(auth()->user()->role === 'regular')
                        <div @click="showUpgradeModal = true" class="absolute inset-0 top-16 z-20 backdrop-blur-md bg-zinc-950/60 flex flex-col items-center justify-center cursor-pointer transition-all hover:bg-zinc-950/80 rounded-b-2xl">
                            <svg class="w-12 h-12 text-[#ff9900] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            <span class="text-[#ff9900] font-bold tracking-wider">VIP EXCLUSIVE</span>
                            <span class="text-gray-400 text-xs mt-1">Click to unlock signals</span>
                        </div>
                        @endif
                    </div>

                    <!-- Panel Bawah: Listed Company Disclosure -->
                    <div class="bg-zinc-900 rounded-2xl p-6 border border-zinc-800 shadow-xl">
                        <h3 class="font-bold text-gray-200 text-lg mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#ff9900]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                            Listed Company Disclosure & E-IPO
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border-l-4 border-[#ff9900] pl-4">
                                <span class="text-xs font-bold text-[#ff9900] bg-orange-900/30 border border-orange-700/30 px-2 py-1 rounded">UPCOMING IPO</span>
                                <h4 class="font-bold text-gray-300 mt-2">PT Global Sukses Makmur Tbk (GSM)</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- POPUP MODAL VIP -->
        <div x-show="showUpgradeModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Background overlay -->
            <div x-show="showUpgradeModal" x-transition.opacity class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity"></div>

            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
                <!-- Modal panel -->
                <div x-show="showUpgradeModal" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     @click.away="showUpgradeModal = false"
                     class="inline-block align-bottom bg-zinc-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-zinc-700 relative">
                    
                    <!-- Tombol Close -->
                    <div class="absolute top-0 right-0 pt-4 pr-4 z-20">
                        <button @click="showUpgradeModal = false" type="button" class="bg-zinc-800 rounded-full p-1 text-gray-400 hover:text-white hover:bg-zinc-700 focus:outline-none transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <!-- Konten Modal -->
                    <div class="p-8 relative overflow-hidden">
                        <!-- Efek cahaya di dalam modal -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-[#ff9900]/20 blur-3xl rounded-full pointer-events-none"></div>
                        
                        <div class="text-center relative z-10">
                            <h3 class="text-xl font-bold text-[#ff9900] mb-2 tracking-widest uppercase">VIP Access</h3>
                            <p class="text-sm text-gray-400 mb-6">Elevate your trading edge with institutional-grade insights.</p>
                            
                            <div class="text-5xl font-extrabold text-white mb-6">
                                Rp 350K<span class="text-lg text-gray-500 font-medium">/bulan</span>
                            </div>

                            <ul class="text-left space-y-4 mb-8 text-gray-300 max-w-sm mx-auto">
                                <li class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-[#ff9900] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Buka sensor Real-time AI Watchlist
                                </li>
                                <li class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-[#ff9900] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Akses Penuh Blackchip Signals
                                </li>
                                <li class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-[#ff9900] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Kartu Akses 3D VIP Eksklusif
                                </li>
                            </ul>

                            <form action="{{ route('upgrade.process') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-[#ff9900] hover:bg-orange-500 text-black font-extrabold py-3 px-4 rounded-xl transition-all hover:scale-105 shadow-lg shadow-orange-900/50">
                                    Simulasi Bayar Sekarang
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>