<x-app-layout>
    <div class="py-12 bg-zinc-950 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
            
            <!-- HEADER PROFIL & KARTU 3D DIGITAL ULTRA-LUXURY -->
            <div class="flex flex-col lg:flex-row gap-8 items-center justify-between bg-zinc-900 p-8 sm:p-10 rounded-3xl border border-zinc-800 shadow-2xl relative overflow-hidden">
                <!-- Efek Glow Latar Belakang Berdasarkan Role -->
                <div class="absolute top-0 right-0 w-96 h-96 blur-[130px] rounded-full pointer-events-none
                    @if(auth()->user()->role === 'superadmin') bg-amber-500/35
                    @elseif(auth()->user()->role === 'vip') bg-orange-500/35
                    @else bg-zinc-700/10 @endif"></div>

                <div class="z-10 max-w-xl">
                    <h2 class="text-3xl font-extrabold text-white mb-2">Trade<span class="bg-[#ff9900] text-black px-2 py-0.5 rounded-lg ml-1">hub</span> Identity</h2>
                    <p class="text-gray-400 leading-relaxed">Kelola informasi akun dan identitas akses institusional Anda. Desain kartu digital di samping otomatis menyesuaikan tingkat otorisasi dan kasta Anda dalam ekosistem Tradehub.</p>
                </div>

                <!-- KARTU 3D DIGITAL HIGH-END FINTECH -->
                <div class="z-10 perspective-1000">
                    <div x-data="{
                            rotateX: 0,
                            rotateY: 0,
                            handleMousemove(e) {
                                const rect = $el.getBoundingClientRect();
                                const x = e.clientX - rect.left;
                                const y = e.clientY - rect.top;
                                const centerX = rect.width / 2;
                                const centerY = rect.height / 2;
                                this.rotateY = ((x - centerX) / centerX) * 22;
                                this.rotateX = ((centerY - y) / centerY) * 22;
                            },
                            handleMouseleave() {
                                this.rotateX = 0;
                                this.rotateY = 0;
                            }
                        }"
                        @mousemove="handleMousemove"
                        @mouseleave="handleMouseleave"
                        :style="`transform: rotateX(${rotateX}deg) rotateY(${rotateY}deg); transition: transform 0.1s ease-out;`"
                        class="w-[420px] h-[250px] rounded-2xl p-7 flex flex-col justify-between relative overflow-hidden cursor-crosshair select-none transition-all
                        
                        {{-- KONDISI DESIGN BERDASARKAN KASTA TIER --}}
                        @if(auth()->user()->role === 'superadmin') 
                            bg-gradient-to-tr from-black via-zinc-950 to-zinc-900 
                            border-2 border-amber-400/90 
                            shadow-[0_0_90px_rgba(245,158,11,0.55)]
                        @elseif(auth()->user()->role === 'vip') 
                            bg-gradient-to-tr from-zinc-950 via-orange-950/80 to-black 
                            border-2 border-orange-500/90 
                            shadow-[0_0_80px_rgba(249,115,22,0.45)]
                        @else 
                            bg-gradient-to-tr from-zinc-900 via-zinc-950 to-zinc-900 
                            border border-zinc-700/80 
                            shadow-[0_20px_50px_rgba(0,0,0,0.6)] 
                        @endif">
                        
                        <!-- Watermark Latar Belakang: Pola Candlestick Pasar Asli -->
                        <div class="absolute inset-0 opacity-30 pointer-events-none flex items-end justify-between px-6 pb-6">
                            <svg class="w-full h-40" viewBox="0 0 350 150" fill="none" preserveAspectRatio="none">
                                <line x1="30" y1="100" x2="30" y2="130" stroke="#10b981" stroke-width="2"/>
                                <rect x="25" y="105" width="10" height="18" rx="2" fill="#10b981"/>
                                <line x1="75" y1="75" x2="75" y2="115" stroke="#10b981" stroke-width="2"/>
                                <rect x="70" y="80" width="10" height="25" rx="2" fill="#10b981"/>
                                <line x1="120" y1="65" x2="120" y2="105" stroke="#ef4444" stroke-width="2"/>
                                <rect x="115" y="70" width="10" height="20" rx="2" fill="#ef4444"/>
                                <line x1="165" y1="70" x2="165" y2="95" stroke="#10b981" stroke-width="2"/>
                                <rect x="160" y="75" width="10" height="15" rx="2" fill="#10b981"/>
                                <line x1="210" y1="60" x2="210" y2="90" stroke="#ef4444" stroke-width="2"/>
                                <rect x="205" y="65" width="10" height="15" rx="2" fill="#ef4444"/>
                                <line x1="255" y1="30" x2="255" y2="75" stroke="#10b981" stroke-width="2"/>
                                <rect x="250" y="35" width="10" height="30" rx="2" fill="#10b981"/>
                                <line x1="300" y1="10" x2="300" y2="50" stroke="#10b981" stroke-width="2"/>
                                <rect x="295" y="15" width="10" height="28" rx="2" fill="#10b981"/>
                            </svg>
                        </div>

                        <!-- Efek Kilau Sinar Logam Diagonal (Holographic Sheen) -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/15 to-transparent pointer-events-none transform -skew-x-12 translate-x-[-20%]"></div>

                        <!-- Header Kartu: Logo Tradehub Dipertegas -->
                        <div class="flex justify-between items-center relative z-10">
                            <!-- Logo Super Tegas -->
                            <div class="flex items-center tracking-tight">
                                <span class="text-white text-2xl font-black tracking-tight">Trade</span>
                                <span class="bg-gradient-to-r from-[#ff9900] to-amber-400 text-black px-2.5 py-1 rounded-lg text-sm ml-1.5 font-black shadow-[0_0_20px_rgba(255,153,0,0.7)]">hub</span>
                            </div>

                            <!-- Indikator Node Status (Semua Berkedip Aktif) -->
                            <div class="flex items-center gap-2 bg-black/90 backdrop-blur-md px-4 py-1.5 rounded-full border shadow-xl
                                @if(auth()->user()->role === 'superadmin') border-amber-400 text-amber-300 shadow-amber-500/20
                                @elseif(auth()->user()->role === 'vip') border-orange-400 text-orange-300 shadow-orange-500/20
                                @else border-zinc-700 text-zinc-400 @endif">
                                <!-- Titik Kedip Dinamis untuk SEMUA role -->
                                <span class="w-2.5 h-2.5 rounded-full 
                                    @if(auth()->user()->role === 'superadmin') bg-amber-400 animate-ping
                                    @elseif(auth()->user()->role === 'vip') bg-orange-400 animate-ping
                                    @else bg-zinc-500 @endif absolute"></span>
                                <span class="w-2.5 h-2.5 rounded-full 
                                    @if(auth()->user()->role === 'superadmin') bg-amber-400
                                    @elseif(auth()->user()->role === 'vip') bg-orange-400
                                    @else bg-zinc-500 @endif relative"></span>
                                <span class="text-[10px] font-mono tracking-widest font-black">
                                    @if(auth()->user()->role === 'superadmin') FOUNDER NODE
                                    @elseif(auth()->user()->role === 'vip') VIP NODE
                                    @else STANDARD NODE @endif
                                </span>
                            </div>
                        </div>

                        <!-- Bagian Tengah: Nomor ID -->
                        <div class="relative z-10 my-auto">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-[9px] font-mono tracking-widest font-extrabold
                                    @if(auth()->user()->role === 'superadmin') text-amber-400
                                    @elseif(auth()->user()->role === 'vip') text-orange-400
                                    @else text-zinc-400 @endif">ACCESS CREDENTIAL</span>
                                <div class="h-[1.5px] flex-grow bg-gradient-to-r 
                                    @if(auth()->user()->role === 'superadmin') from-amber-400 to-transparent
                                    @elseif(auth()->user()->role === 'vip') from-orange-400 to-transparent
                                    @else from-zinc-700 to-transparent @endif"></div>
                            </div>
                            <p class="text-2xl font-mono font-black tracking-widest text-white drop-shadow-[0_2px_10px_rgba(255,255,255,0.3)]">
                                {{ auth()->user()->tradehub_id ?? 'TH-2026-0001' }}
                            </p>
                        </div>

                        <!-- Footer: Nama & Badge Super Mewah & Nendang -->
                        <div class="flex justify-between items-end relative z-10 border-t border-white/20 pt-3.5">
                            <div>
                                <p class="text-[9px] font-mono tracking-widest text-zinc-400 uppercase">HOLDER</p>
                                <p class="text-base font-bold text-white tracking-wide truncate max-w-[190px] drop-shadow">{{ auth()->user()->name }}</p>
                            </div>
                            <div class="text-right">
                                <!-- BADGE TIER ULTRA-MEWAH DENGAN GLOW KUAT -->
                                <span class="px-4 py-2 rounded-xl text-xs font-black tracking-wider uppercase inline-flex items-center gap-2 transition-transform hover:scale-105
                                    @if(auth()->user()->role === 'superadmin') 
                                        bg-gradient-to-r from-amber-200 via-yellow-400 to-amber-500 text-black 
                                        shadow-[0_0_40px_rgba(251,191,36,0.95)] ring-4 ring-amber-300/80
                                    @elseif(auth()->user()->role === 'vip') 
                                        bg-gradient-to-r from-orange-400 via-[#ff9900] to-yellow-300 text-black 
                                        shadow-[0_0_35px_rgba(249,115,22,0.9)] ring-4 ring-orange-300/80
                                    @else 
                                        bg-zinc-800 text-zinc-200 border border-zinc-600 shadow-lg px-4 py-2
                                    @endif">
                                    @if(auth()->user()->role === 'superadmin')
                                        <!-- Ikon Mahkota Vektor Tajam -->
                                        <svg class="w-4 h-4 fill-black flex-shrink-0 animate-bounce" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                                        FOUNDER
                                    @elseif(auth()->user()->role === 'vip')
                                        <!-- Ikon Petir Vektor Tajam -->
                                        <svg class="w-4 h-4 fill-black flex-shrink-0 animate-pulse" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                        VIP MEMBER
                                    @else 
                                        REGULAR 
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM BAWAAN BREEZE (Dark Mode) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="p-6 sm:p-8 bg-zinc-900 shadow-xl border border-zinc-800 sm:rounded-2xl [&_h2]:text-white [&_p]:text-gray-400 [&_label]:text-gray-300 [&_input]:bg-zinc-950 [&_input]:border-zinc-700 [&_input]:text-white [&_button]:bg-[#ff9900] [&_button]:text-black hover:[&_button]:bg-orange-500">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="p-6 sm:p-8 bg-zinc-900 shadow-xl border border-zinc-800 sm:rounded-2xl [&_h2]:text-white [&_p]:text-gray-400 [&_label]:text-gray-300 [&_input]:bg-zinc-950 [&_input]:border-zinc-700 [&_input]:text-white [&_button]:bg-[#ff9900] [&_button]:text-black hover:[&_button]:bg-orange-500">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>
            
            <div class="p-6 sm:p-8 bg-zinc-900 shadow-xl border border-red-900/30 sm:rounded-2xl [&_h2]:text-red-500 [&_p]:text-gray-400">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>