<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile & Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- KARTU AKSES 3D (Posisi Paling Atas) -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg flex justify-center bg-gray-50/50">
                <div class="relative group w-80 h-48 [perspective:1000px]">
                    <div class="w-full h-full transition-all duration-500 [transform-style:preserve-3d] group-hover:[transform:rotateY(180deg)] shadow-xl rounded-xl">
                        
                        <!-- Bagian Depan Kartu -->
                        @if(auth()->user()->role === 'superadmin')
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-br from-black via-gray-900 to-yellow-600 rounded-xl p-6 text-white [backface-visibility:hidden] border border-yellow-500/50 shadow-yellow-900/50 shadow-2xl">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="font-bold tracking-widest text-yellow-500 text-sm">FOUNDER'S EDITION</span>
                                    <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                </div>
                        @elseif(auth()->user()->role === 'vip')
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-blue-900 to-black rounded-xl p-6 text-white [backface-visibility:hidden] border border-blue-500/30">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="font-bold tracking-widest text-blue-400 text-sm">VIP ACCESS</span>
                                </div>
                        @else
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-gray-700 to-gray-900 rounded-xl p-6 text-white [backface-visibility:hidden]">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="font-bold tracking-widest text-gray-400 text-sm">REGULAR</span>
                                </div>
                        @endif
                            
                            <div class="mt-4">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Cardholder</p>
                                <p class="text-lg font-bold uppercase tracking-wide">{{ auth()->user()->name }}</p>
                                <p class="text-sm text-gray-300 mt-1 font-mono">{{ auth()->user()->tradehub_id ?? 'TH-PENDING' }}</p>
                            </div>
                        </div>

                        <!-- Bagian Belakang Kartu -->
                        <div class="absolute inset-0 w-full h-full bg-zinc-900 rounded-xl p-6 text-white [transform:rotateY(180deg)] [backface-visibility:hidden] flex flex-col justify-center items-center text-center border border-gray-700">
                            <div class="bg-white p-2 rounded mb-2">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data={{ auth()->user()->tradehub_id }}" alt="QR Code">
                            </div>
                            <p class="text-[10px] text-gray-400">Scan to view investor profile</p>
                            @if(auth()->user()->role === 'vip')
                                <p class="text-xs text-blue-400 mt-2 font-mono">Valid Thru: {{ auth()->user()->vip_valid_until->format('d M Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulir Bawaan Breeze -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>