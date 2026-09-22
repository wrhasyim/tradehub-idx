<x-app-layout>
    <div x-data="{ showAddModal: false }" class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Alert Sukses -->
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
            @endif

            <!-- Panel Manajemen Data -->
            <div class="bg-white p-6 shadow sm:rounded-lg border-t-4 border-[#ff9900]">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Manajemen Watchlist & Sinyal</h3>
                    <button @click="showAddModal = true" class="bg-[#ff9900] text-black px-4 py-2 rounded-lg text-sm font-bold hover:bg-orange-500 shadow-md">
                        + Tambah Sinyal Manual
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-50 text-sm">
                                <th class="p-3">Saham</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Plan (Entry/TP/SL)</th>
                                <th class="p-3">Catatan Analisis</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @foreach($watchlists as $item)
                            <tr class="border-b hover:bg-gray-50 transition-colors">
                                <td class="p-3 font-bold text-gray-900">{{ $item->stock_code }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-1 {{ strtolower($item->status) === 'triggered' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }} rounded-full text-xs font-bold">
                                        {{ strtoupper($item->status) }}
                                    </span>
                                </td>
                                <td class="p-3 text-gray-600">
                                    {{ $item->entry_price ?? '-' }} / {{ $item->target_price ?? '-' }} / {{ $item->stop_loss ?? '-' }}
                                </td>
                                <td class="p-3 italic text-gray-500">{{ Str::limit($item->ai_analysis_notes, 50) }}</td>
                                <td class="p-3 text-right">
                                    <form action="{{ route('admin.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus sinyal ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-bold hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- POPUP MODAL TAMBAH DATA -->
        <div x-show="showAddModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div x-show="showAddModal" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-75 transition-opacity"></div>

            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
                <div x-show="showAddModal" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     @click.away="showAddModal = false"
                     class="inline-block align-bottom bg-zinc-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-zinc-700">
                    
                    <div class="px-6 py-4 border-b border-zinc-800 flex justify-between items-center bg-zinc-950">
                        <h3 class="text-lg font-bold text-white">Buat Sinyal Baru</h3>
                        <button @click="showAddModal = false" class="text-gray-400 hover:text-white">&times;</button>
                    </div>

                    <form action="{{ route('admin.store') }}" method="POST" class="p-6">
                        @csrf
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Kode Saham</label>
                                <input type="text" name="stock_code" placeholder="Misal: BBCA" required class="w-full bg-zinc-950 border border-zinc-800 text-white rounded-lg focus:ring-[#ff9900] focus:border-[#ff9900] uppercase">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Status</label>
                                <select name="status" class="w-full bg-zinc-950 border border-zinc-800 text-white rounded-lg focus:ring-[#ff9900] focus:border-[#ff9900]">
                                    <option value="Watching">Watching</option>
                                    <option value="Triggered">Triggered</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Entry Price</label>
                                <input type="number" step="0.01" name="entry_price" class="w-full bg-zinc-950 border border-zinc-800 text-white rounded-lg focus:ring-[#ff9900] focus:border-[#ff9900]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Target Price</label>
                                <input type="number" step="0.01" name="target_price" class="w-full bg-zinc-950 border border-zinc-800 text-white rounded-lg focus:ring-[#ff9900] focus:border-[#ff9900]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-1">Stop Loss</label>
                                <input type="number" step="0.01" name="stop_loss" class="w-full bg-zinc-950 border border-zinc-800 text-white rounded-lg focus:ring-[#ff9900] focus:border-[#ff9900]">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-400 mb-1">Catatan Analisis (AI Notes)</label>
                            <textarea name="ai_analysis_notes" rows="3" required placeholder="Masukkan narasi analisis..." class="w-full bg-zinc-950 border border-zinc-800 text-white rounded-lg focus:ring-[#ff9900] focus:border-[#ff9900]"></textarea>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showAddModal = false" class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-lg font-bold">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-[#ff9900] hover:bg-orange-500 text-black rounded-lg font-bold">Terbitkan Sinyal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>