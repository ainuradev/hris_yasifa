<x-layouts.admin title="Master Kalender Libur">
    <x-flash-message />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Master Kalender Libur</h1>
                <p class="text-sm text-slate-500">Tentukan hari libur nasional atau internal unit.</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Form Tambah --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm h-fit">
                <h2 class="mb-4 font-bold text-slate-800">Tambah Hari Libur</h2>
                <form action="{{ route('admin.holidays.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Libur</label>
                        <input type="text" name="name" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#1a2744] focus:ring-[#1a2744]" placeholder="Misal: Idul Fitri, Libur Semester...">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal</label>
                        <input type="date" name="date" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#1a2744] focus:ring-[#1a2744]">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Unit (Opsional)</label>
                        <select name="unit_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#1a2744] focus:ring-[#1a2744]">
                            <option value="">Semua Unit (Libur Yayasan)</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-[10px] text-slate-500 italic">Pilih unit jika libur hanya berlaku untuk MI/MA saja.</p>
                    </div>
                    <button type="submit" class="w-full rounded-2xl bg-[#1a2744] py-3 text-sm font-bold text-white shadow-lg transition-transform hover:scale-[1.02]">Simpan Libur</button>
                </form>
            </div>

            {{-- List Libur --}}
            <div class="lg:col-span-2">
                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Tanggal</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama Libur</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Unit</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($holidays as $holiday)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-700">
                                        {{ \Carbon\Carbon::parse($holiday->date)->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600 font-semibold">
                                        {{ $holiday->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($holiday->unit)
                                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600">{{ $holiday->unit->name }}</span>
                                        @else
                                            <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-teal-600">Seluruh Yayasan</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium">
                                        <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" onsubmit="return confirm('Hapus hari libur ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 transition-colors">
                                                <svg class="h-5 w-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <p class="mt-2 text-sm text-slate-400">Belum ada hari libur yang terdaftar.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
