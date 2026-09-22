<x-app-layout>
    <div class="py-12 bg-zinc-950 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
            
            <!-- HEADER PROFIL & KARTU 3D DIGITAL -->
            <div class="flex flex-col md:flex-row gap-8 items-center justify-between bg-zinc-900 p-8 rounded-3xl border border-zinc-800 shadow-2xl relative overflow-hidden">
                <!-- Efek Glow Latar -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-[#ff9900]/10 blur-[100px] rounded-full pointer-events-none"></div>

                <div class="z-10">
                    <h2 class="text-3xl font-extrabold text-white mb-2">Trade<span class="bg-[#ff9900] text-black px-2 py-0.5 rounded-lg ml-1">hub</span> Identity</h2>
                    <p class="text-gray-400">Kelola informasi akun dan preferensi keamanan Anda.</p>
                </div>

                <!-- Kartu 3D Alpine.js -->
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
                                this.rotateY = ((x - centerX) / centerX) * 15;
                                this.rotateX = ((centerY - y) / centerY) * 15;
                            },
                            handleMouseleave() {
                                this.rotateX = 0;
                                this.rotateY = 0;
                            }
                        }"
                        @mousemove="handleMousemove"
                        @mouseleave="handleMouseleave"
                        :style="`transform: rotateX(${rotateX}deg) rotateY(${rotateY}deg); transition: transform 0.1s ease-out;`"
                        class="w-80 h-48 rounded-2xl p-6 flex flex-col justify-between shadow-[0_20px_50px_rgba(0,0,0,0.5)] border cursor-crosshair
                        @if(auth()->user()->role === 'superadmin') bg-gradient-to-br from-zinc-800 to-black border-[#ff9900]/50
                        @elseif(auth()->user()->role === 'vip') bg-gradient-to-br from-orange-600 to-yellow-600 border-orange-400
                        @else bg-gradient-to-br from-zinc-700 to-zinc-900 border-zinc-600 @endif">
                        
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-mono text-white/70 mb-1">MEMBER ID</p>
                                <p class="text-lg font-bold text-white tracking-widest font-mono">{{ auth()->user()->tradehub_id ?? 'TH-00000' }}</p>
                            </div>
                            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        
                        <div>
                            <p class="text-2xl font-extrabold text-white uppercase">{{ auth()->user()->name }}</p>
                            <p class="text-sm font-bold tracking-widest
                                @if(auth()->user()->role === 'superadmin') text-[#ff9900]
                                @elseif(auth()->user()->role === 'vip') text-yellow-200
                                @else text-zinc-400 @endif">
                                @if(auth()->user()->role === 'superadmin') FOUNDER'S EDITION
                                @elseif(auth()->user()->role === 'vip') VIP ACCESS
                                @else REGULAR ACCESS @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM BAWAAN BREEZE (Disesuaikan jadi Dark Mode) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="p-4 sm:p-8 bg-zinc-900 shadow-xl border border-zinc-800 sm:rounded-2xl [&_h2]:text-white [&_p]:text-gray-400 [&_label]:text-gray-300 [&_input]:bg-zinc-950 [&_input]:border-zinc-700 [&_input]:text-white [&_button]:bg-[#ff9900] [&_button]:text-black hover:[&_button]:bg-orange-500">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-zinc-900 shadow-xl border border-zinc-800 sm:rounded-2xl [&_h2]:text-white [&_p]:text-gray-400 [&_label]:text-gray-300 [&_input]:bg-zinc-950 [&_input]:border-zinc-700 [&_input]:text-white [&_button]:bg-[#ff9900] [&_button]:text-black hover:[&_button]:bg-orange-500">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>
            
            <div class="p-4 sm:p-8 bg-zinc-900 shadow-xl border border-red-900/30 sm:rounded-2xl [&_h2]:text-red-500 [&_p]:text-gray-400">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>