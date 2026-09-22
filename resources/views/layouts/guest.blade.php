<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TradeHub') }} - Access Portal</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-300 antialiased bg-zinc-950 selection:bg-[#ff9900] selection:text-black">
        
        <!-- Efek Cahaya Latar (Konsisten dengan Landing Page) -->
        <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none -z-10">
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-orange-600/10 blur-[120px] rounded-full"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-orange-600/10 blur-[120px] rounded-full"></div>
            <!-- Grid Pattern Overlay -->
            <div class="absolute inset-0 bg-[url('https://laravel.com/assets/img/welcome/background.svg')] opacity-10 bg-center [mask-image:linear-gradient(180deg,white,rgba(255,255,255,0))]"></div>
        </div>

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            
            <!-- LOGO TRADEHUB (Sama seperti Landing Page) -->
            <div class="mb-8">
                <a href="/" class="flex items-center cursor-pointer select-none">
                    <span class="font-bold text-4xl tracking-tight flex items-center">
                        <span class="text-white">Trade</span>
                        <span class="bg-[#ff9900] text-black px-2 py-0.5 rounded-lg ml-1">hub</span>
                    </span>
                </a>
            </div>

            <!-- KOTAK FORM LOGIN/REGISTER -->
            <div class="w-full sm:max-w-md mt-6 px-8 py-10 bg-zinc-900/80 backdrop-blur-md shadow-[0_0_40px_rgba(0,0,0,0.5)] border border-zinc-800 rounded-2xl relative overflow-hidden">
                
                <!-- Aksen Garis Oranye di Atas Form -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-orange-400 via-[#ff9900] to-yellow-500"></div>

                <!-- Ini adalah area di mana form Login/Register akan dimasukkan otomatis oleh Laravel -->
                <div class="text-gray-300 [&_label]:text-gray-400 [&_input]:bg-zinc-950 [&_input]:border-zinc-800 [&_input]:text-white [&_input:focus]:border-[#ff9900] [&_input:focus]:ring-[#ff9900] [&_a]:text-gray-400 [&_a:hover]:text-white [&_.text-indigo-600]:text-[#ff9900] [&_button]:bg-[#ff9900] [&_button]:text-black [&_button:hover]:bg-orange-500 [&_button:focus]:bg-orange-600 [&_button:active]:bg-orange-700">
                    {{ $slot }}
                </div>

            </div>
        </div>
    </body>
</html>