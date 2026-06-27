<x-layouts.admin title="Edit Kalender Libur">
    <x-flash-message />

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Edit Kalender Libur</h1>
                <p class="text-sm text-slate-500">Ubah informasi master kalender libur.</p>
            </div>
            <a href="{{ route('admin.holidays.index') }}" class="flex items-center gap-2 rounded-2xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>

        <div class="max-w-xl">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <form action="{{ route('admin.holidays.update', $holiday) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Libur</label>
                        <input type="text" name="name" value="{{ old('name', $holiday->name) }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#1a2744] focus:ring-[#1a2744]">
                        @error('name') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                        <input type="date" name="date" value="{{ old('date', $holiday->date ? $holiday->date->format('Y-m-d') : '') }}" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#1a2744] focus:ring-[#1a2744]">
                        @error('date') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Sampai Tanggal <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date', $holiday->end_date ? $holiday->end_date->format('Y-m-d') : '') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-[#1a2744] focus:ring-[#1a2744]">
                        <p class="mt-1 text-[10px] text-slate-500 italic">Kosongkan jika libur hanya 1 hari.</p>
                        @error('end_date') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    @if(auth()->user()->isAdminPusat())
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Unit (Opsional)</label>
                        <!-- Admin Pusat hanya mengubah libur Yayasan, maka select ini di-disable agar tidak mengubah jadi Unit lain (berdasarkan rule) -->
                        <select name="unit_id" disabled class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-500 cursor-not-allowed">
                            <option value="">Semua Unit (Libur Yayasan)</option>
                        </select>
                        <p class="mt-1 text-[10px] text-slate-500 italic">Level unit tidak dapat diubah setelah dibuat.</p>
                    </div>
                    @endif

                    <div class="pt-4">
                        <button type="submit" class="w-full rounded-2xl bg-[#1a2744] py-3 text-sm font-bold text-white shadow-lg transition-transform hover:scale-[1.02]">Update Kalender Libur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
