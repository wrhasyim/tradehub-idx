<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TradeHub | AI Stock Screener & Signals</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-zinc-950 text-white selection:bg-[#ff9900] selection:text-black font-sans">
    
    <!-- Navbar -->
    <nav class="border-b border-zinc-800/50 bg-zinc-950/80 backdrop-blur-lg fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                
                <!-- LOGO TRADEHUB -->
                <div class="flex items-center cursor-pointer select-none">
                    <span class="font-bold text-3xl tracking-tight flex items-center">
                        <span class="text-white">Trade</span>
                        <span class="bg-[#ff9900] text-black px-2 py-0.5 rounded-lg ml-1">hub</span>
                    </span>
                </div>

                <div>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm font-bold text-black px-6 py-2 bg-[#ff9900] hover:bg-orange-500 rounded-lg transition-all">Masuk Dashboard &rarr;</a>
                        @else
                            @if (Route::has('register'))
                                <!-- SATU-SATUNYA PINTU MASUK (MENGARAH KE REGISTER) -->
                                <a href="{{ route('register') }}" class="bg-[#ff9900] hover:bg-orange-500 text-black px-6 py-2.5 rounded-lg text-sm font-bold transition-all shadow-lg shadow-orange-900/20">Masuk / Daftar</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative flex flex-col items-center justify-center min-h-screen overflow-hidden">
        
        <!-- Efek Cahaya Latar -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-orange-600/10 blur-[120px] rounded-full pointer-events-none"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-orange-600/10 blur-[120px] rounded-full pointer-events-none"></div>
        
        <!-- Grid Pattern Overlay -->
        <div class="absolute inset-0 bg-[url('https://laravel.com/assets/img/welcome/background.svg')] opacity-10 bg-center [mask-image:linear-gradient(180deg,white,rgba(255,255,255,0))]"></div>

        <div class="relative z-10 text-center px-4 max-w-5xl mx-auto mt-20">
            <!-- Status Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1 mb-8 rounded-full border border-zinc-800 bg-zinc-900/50 text-xs text-[#ff9900] font-mono tracking-widest">
                <span class="w-2 h-2 rounded-full bg-[#ff9900] animate-pulse"></span>
                SISTEM V72.0 ONLINE
            </div>
            
            <!-- Headline Utama -->
            <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight mb-6 leading-tight">
                Trading Lebih Tenang dengan <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 via-[#ff9900] to-yellow-500">
                    Bantuan AI Analyst
                </span>
            </h1>
            
            <!-- Subtitle -->
            <p class="text-lg md:text-xl text-gray-400 mb-10 max-w-2xl mx-auto leading-relaxed">
                Platform *screener* saham yang menyaring ribuan data teknikal dan volume menjadi sinyal <span class="text-gray-200 font-bold">Watchlist</span> dan <span class="text-gray-200 font-bold">Blackchip</span> siap pakai.
            </p>
            
            <!-- CTA Buttons (Hanya Scroll ke Fitur) -->
            <div class="flex justify-center">
                <a href="#features" class="bg-zinc-900/80 hover:bg-zinc-800 border border-zinc-700 px-8 py-4 rounded-xl font-bold text-lg transition-all text-white backdrop-blur-sm flex items-center justify-center gap-2 shadow-lg">
                    Lihat Fitur Tradehub &darr;
                </a>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="bg-black py-24 border-t border-zinc-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center mb-16">
            <h2 class="text-3xl font-bold text-white mb-4">Fitur Lengkap untuk Analisis Anda</h2>
            <p class="text-gray-400 max-w-2xl mx-auto">Semua *tools* yang Anda butuhkan untuk memantau pergerakan harga dan membuat keputusan *trading* yang lebih objektif.</p>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-left">
                
                <div class="p-6 rounded-2xl bg-zinc-900/50 border border-zinc-800 hover:border-[#ff9900]/50 transition-colors group">
                    <svg class="w-8 h-8 text-[#ff9900] mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <h3 class="text-xl font-bold text-white mb-2">AI Watchlist & Planner</h3>
                    <p class="text-gray-400 text-sm">Target harga dan *stop loss* otomatis dikalkulasi oleh AI berdasarkan pola teknikal dan rata-rata pergerakan MA/EMA.</p>
                </div>

                <div class="p-6 rounded-2xl bg-zinc-900/50 border border-zinc-800 hover:border-[#ff9900]/50 transition-colors group">
                    <svg class="w-8 h-8 text-[#ff9900] mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <h3 class="text-xl font-bold text-white mb-2">Blackchip Signals</h3>
                    <p class="text-gray-400 text-sm">Notifikasi eksklusif VIP untuk mendeteksi lonjakan volume dan pola akumulasi sebelum momentum harga terjadi.</p>
                </div>

                <div class="p-6 rounded-2xl bg-zinc-900/50 border border-zinc-800 hover:border-[#ff9900]/50 transition-colors group">
                    <svg class="w-8 h-8 text-[#ff9900] mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                    <h3 class="text-xl font-bold text-white mb-2">Keterbukaan Info & E-IPO</h3>
                    <p class="text-gray-400 text-sm">Pantau berita emiten terbaru, perubahan kepemilikan saham, hingga jadwal saham IPO secara terpusat.</p>
                </div>

                <div class="p-6 rounded-2xl bg-zinc-900/50 border border-zinc-800 hover:border-[#ff9900]/50 transition-colors group">
                    <svg class="w-8 h-8 text-[#ff9900] mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                    <h3 class="text-xl font-bold text-white mb-2">Kartu Keanggotaan 3D</h3>
                    <p class="text-gray-400 text-sm">Setiap pengguna mendapatkan identitas digital 3D interaktif yang membedakan status akses (Regular atau VIP) di platform.</p>
                </div>

                <div class="p-6 rounded-2xl bg-zinc-900/50 border border-zinc-800 hover:border-[#ff9900]/50 transition-colors group">
                    <svg class="w-8 h-8 text-[#ff9900] mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <h3 class="text-xl font-bold text-white mb-2">Jurnal PnL Otomatis</h3>
                    <p class="text-gray-400 text-sm">Catat setiap posisi beli dan jual untuk mengevaluasi *win rate* dan menjaga kedisiplinan *trading plan* Anda.</p>
                </div>

                <div class="p-6 rounded-2xl bg-zinc-900/50 border border-zinc-800 hover:border-[#ff9900]/50 transition-colors group">
                    <svg class="w-8 h-8 text-[#ff9900] mb-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                    <h3 class="text-xl font-bold text-white mb-2">Komunitas VIP</h3>
                    <p class="text-gray-400 text-sm">Bergabunglah dalam grup diskusi privat. Bahas analisis teknikal bersama *trader* lain untuk memvalidasi ide *trading* Anda.</p>
                </div>

            </div>
        </div>
    </div>
</body>
</html>