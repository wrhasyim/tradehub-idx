<nav x-data="{ open: false }" class="bg-zinc-950 border-b border-zinc-800">
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

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-6 sm:flex">
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out {{ request()->routeIs('dashboard') ? 'border-[#ff9900] text-white' : 'border-transparent text-gray-400 hover:text-white hover:border-zinc-700' }}">
                        {{ __('Dashboard') }}
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown (Custom Dark Mode) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                

                <!-- Alpine Dropdown Kustom -->
                <div x-data="{ dropdownOpen: false }" class="relative">
                    <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center px-3 py-2 border border-zinc-800 text-sm leading-4 font-medium rounded-xl text-gray-300 bg-zinc-900 hover:text-white focus:outline-none transition ease-in-out duration-150">
                        <div>{{ Auth::user()->name }}</div>
                        <div class="ms-1">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>

                    <!-- Box Dropdown Gelap -->
                    <div x-show="dropdownOpen" 
                         @click.away="dropdownOpen = false" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         style="display: none;" 
                         class="absolute right-0 z-50 mt-2 w-48 rounded-xl bg-zinc-900 border border-zinc-800 shadow-2xl py-1">
                        
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-gray-300 hover:bg-zinc-800 hover:text-white transition-colors">
                            Profile
                        </a>

                        @if(auth()->user()->role === 'superadmin')
                        <a href="{{ route('admin.index') }}" class="block px-4 py-2.5 text-sm text-[#ff9900] font-bold hover:bg-zinc-800 transition-colors">
                            Command Center
                        </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left block px-4 py-2.5 text-sm text-red-400 hover:bg-zinc-800 hover:text-red-300 border-t border-zinc-800 mt-1 pt-1 transition-colors">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>