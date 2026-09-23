<nav x-data="{ open: false }" class="bg-zinc-950 border-b border-zinc-800 relative z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-6">
                <!-- Logo Tradehub -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center cursor-pointer select-none">
                        <span class="font-bold text-2xl tracking-tight flex items-center">
                            <span class="text-white">Trade</span>
                            <span class="bg-[#ff9900] text-black px-2 py-0.5 rounded-md ml-1 text-lg">hub</span>
                        </span>
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-6 sm:flex">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('dashboard') ? 'border-[#ff9900] text-white' : 'border-transparent text-gray-400 hover:text-white hover:border-zinc-700' }}">
                        {{ __('Dashboard') }}
                    </a>
                    
                    <a href="{{ route('journal.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('journal.*') ? 'border-[#ff9900] text-white' : 'border-transparent text-gray-400 hover:text-white hover:border-zinc-700' }}">
                        {{ __('Trading Journal') }}
                    </a>

                    <!-- MENU TERMINAL PRO (DESKTOP) -->
                    <a href="{{ route('terminal.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('terminal.*') ? 'border-[#ff9900] text-white' : 'border-transparent text-gray-400 hover:text-white hover:border-zinc-700' }}">
                        {{ __('Terminal Pro') }}
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown (Desktop) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div x-data="{ dropdownOpen: false }" class="relative">
                    <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center px-3 py-2 border border-zinc-800 text-sm leading-4 font-medium rounded-xl text-gray-300 bg-zinc-900 hover:text-white focus:outline-none transition ease-in-out duration-150">
                        <div>{{ Auth::user()->name }}</div>
                        <div class="ms-1">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </div>
                    </button>

                    <div x-show="dropdownOpen" @click.away="dropdownOpen = false" style="display: none;" class="absolute right-0 z-50 mt-2 w-48 rounded-xl bg-zinc-900 border border-zinc-800 shadow-2xl py-1">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-gray-300 hover:bg-zinc-800 hover:text-white transition-colors">Profile</a>
                        @if(auth()->user()->role === 'superadmin')
                        <a href="{{ route('admin.index') }}" class="block px-4 py-2.5 text-sm text-[#ff9900] font-bold hover:bg-zinc-800 transition-colors">Command Center</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left block px-4 py-2.5 text-sm text-red-400 hover:bg-zinc-800 hover:text-red-300 border-t border-zinc-800 mt-1 pt-1 transition-colors">Log Out</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Hamburger Button (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-zinc-900 focus:outline-none focus:bg-zinc-900 focus:text-white transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile View - Solid) -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="sm:hidden absolute left-0 top-16 w-full bg-zinc-950 border-b border-zinc-800 shadow-[0_20px_25px_-5px_rgba(0,0,0,0.8)] z-50" 
         style="display: none;">
        
        <div class="p-4 space-y-6 max-h-[85vh] overflow-y-auto">
            
            <!-- Section: Menu Utama -->
            <div>
                <p class="px-2 text-[10px] font-black text-zinc-500 uppercase tracking-widest mb-2">Menu Utama</p>
                <div class="space-y-1 bg-zinc-900 rounded-2xl p-2 border border-zinc-800">
                    <a href="{{ route('dashboard') }}" class="block w-full px-4 py-3 rounded-xl text-sm font-medium transition duration-150 ease-in-out {{ request()->routeIs('dashboard') ? 'bg-[#ff9900]/10 text-[#ff9900]' : 'text-zinc-400 hover:text-white hover:bg-zinc-800' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('journal.index') }}" class="block w-full px-4 py-3 rounded-xl text-sm font-medium transition duration-150 ease-in-out {{ request()->routeIs('journal.*') ? 'bg-[#ff9900]/10 text-[#ff9900]' : 'text-zinc-400 hover:text-white hover:bg-zinc-800' }}">
                        Trading Journal
                    </a>
                    <!-- MENU TERMINAL PRO (MOBILE) -->
                    <a href="{{ route('terminal.index') }}" class="block w-full px-4 py-3 rounded-xl text-sm font-medium transition duration-150 ease-in-out {{ request()->routeIs('terminal.*') ? 'bg-[#ff9900]/10 text-[#ff9900]' : 'text-zinc-400 hover:text-white hover:bg-zinc-800' }}">
                        Terminal Pro
                    </a>
                </div>
            </div>

            <!-- Section: Akun & Pengaturan -->
            <div>
                <p class="px-2 text-[10px] font-black text-zinc-500 uppercase tracking-widest mb-2">Akun Saya</p>
                <div class="bg-zinc-900 rounded-2xl border border-zinc-800 overflow-hidden">
                    
                    <!-- User Info Card -->
                    <div class="px-4 py-4 bg-zinc-800/50 border-b border-zinc-800 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#ff9900] to-amber-600 flex items-center justify-center text-black font-black text-xl shadow-lg">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-base text-white">{{ Auth::user()->name }}</div>
                            <div class="font-medium text-xs text-zinc-400">{{ Auth::user()->email }}</div>
                        </div>
                    </div>

                    <!-- Action Links -->
                    <div class="p-2 space-y-1">
                        <a href="{{ route('profile.edit') }}" class="block w-full px-4 py-3 rounded-xl text-sm font-medium text-zinc-400 hover:text-white hover:bg-zinc-800 transition duration-150 ease-in-out flex items-center justify-between">
                            Profil & Pengaturan
                            <svg class="w-4 h-4 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                        
                        @if(auth()->user()->role === 'superadmin')
                        <a href="{{ route('admin.index') }}" class="block w-full px-4 py-3 rounded-xl text-sm font-bold text-[#ff9900] hover:text-[#ffb84d] hover:bg-zinc-800 transition duration-150 ease-in-out flex items-center justify-between">
                            Command Center
                            <svg class="w-4 h-4 text-[#ff9900]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}" class="pt-1 mt-1 border-t border-zinc-800">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-3 text-left rounded-xl text-sm font-bold text-red-400 hover:text-red-300 hover:bg-red-500/10 transition duration-150 ease-in-out">
                                Keluar Aplikasi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</nav>