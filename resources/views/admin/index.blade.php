<x-app-layout>
    <div class="py-10 bg-zinc-950 min-h-screen text-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Alert Notifikasi -->
            @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl text-sm">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-sm">
                {{ session('error') }}
            </div>
            @endif

            <!-- Grid 3 Kartu Utama (Total User, API Invezgo, API Midtrans) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- 1. Total Users -->
                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-xl">
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">Total Pengguna</p>
                    <h3 class="text-3xl font-black text-white mt-2">{{ $totalUsers }}</h3>
                    <p class="text-xs text-[#ff9900] mt-1 font-semibold">{{ $totalVipUsers }} Active VIP Members</p>
                </div>

                <!-- 2. Status API Invezgo (Data Saham) -->
                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-xl">
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">API Invezgo (Data Saham)</p>
                    <div class="flex items-center gap-2 mt-2">
                        <h3 class="text-xl font-black {{ $invezgoStatus === 'Operational' ? 'text-emerald-400' : 'text-amber-400' }}">{{ $invezgoStatus }}</h3>
                    </div>
                    <p class="text-xs text-zinc-400 mt-1">Latency: <span class="text-white font-mono">{{ $invezgoLatency }}</span></p>
                </div>

                <!-- 3. Status API Midtrans (Payment Gateway) -->
                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-xl">
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-500">API Midtrans (Payment Gateway)</p>
                    <div class="flex items-center gap-2 mt-2">
                        <h3 class="text-xl font-black {{ $midtransStatus === 'Operational' ? 'text-emerald-400' : 'text-amber-400' }}">{{ $midtransStatus }}</h3>
                    </div>
                    <p class="text-xs text-zinc-400 mt-1">Latency: <span class="text-white font-mono">{{ $midtransLatency }}</span></p>
                </div>
            </div>

            <!-- Grid Tabel: User Governance & Access Control -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-zinc-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-white">User Governance & Access Control</h3>
                        <p class="text-xs text-zinc-400">Kelola tingkat keanggotaan VIP member (masa aktif otomatis 1 bulan) dan hak akses platform.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-zinc-950/50 text-zinc-400 text-xs uppercase tracking-wider border-b border-zinc-800">
                                <th class="px-6 py-4 font-bold">Nama & Email</th>
                                <th class="px-6 py-4 font-bold">Role</th>
                                <th class="px-6 py-4 font-bold">Status VIP</th>
                                <th class="px-6 py-4 font-bold">Masa Berlaku VIP (1 Bulan)</th>
                                <th class="px-6 py-4 font-bold text-right">Aksi Kontrol</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800 text-sm">
                            @foreach($users as $u)
                            <tr class="hover:bg-zinc-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-white">{{ $u->name }}</div>
                                    <div class="text-xs text-zinc-500">{{ $u->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold {{ $u->role === 'superadmin' ? 'bg-[#ff9900]/20 text-[#ff9900]' : 'bg-zinc-800 text-zinc-300' }}">
                                        {{ strtoupper($u->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($u->role === 'vip')
                                        <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">VIP MEMBER</span>
                                    @elseif($u->role === 'superadmin')
                                        <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-[#ff9900]/20 text-[#ff9900]">SUPERADMIN</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-zinc-800 text-zinc-500">FREE</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-zinc-300">
                                    @if($u->vip_valid_until)
                                        {{ \Carbon\Carbon::parse($u->vip_valid_until)->format('d M Y, H:i') }}
                                        @if(now()->lt($u->vip_valid_until))
                                            <span class="block text-[10px] text-emerald-400 font-sans mt-0.5">Aktif</span>
                                        @else
                                            <span class="block text-[10px] text-red-400 font-sans mt-0.5">Kadaluarsa</span>
                                        @endif
                                    @else
                                        <span class="text-zinc-600">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($u->role !== 'superadmin')
                                    <form action="{{ route('admin.user.vip', $u->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-zinc-800 hover:bg-zinc-700 text-amber-400 transition-colors border border-zinc-700">
                                            {{ $u->role === 'vip' ? 'Revoke VIP' : 'Upgrade to VIP (1 Mo)' }}
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-xs text-zinc-600 italic">Protected</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-zinc-800">
                    {{ $users->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>