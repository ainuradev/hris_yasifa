<x-layouts.admin title="Edit Komponen Gaji">
    <x-flash-message />
    
    <div class="max-w-3xl mx-auto space-y-6">
        <section class="page-hero">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.salary-components.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-400 shadow-sm border border-slate-200 hover:text-slate-600 hover:bg-slate-50 transition-all">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <span class="app-eyebrow">Master Data</span>
                    <h1>Edit Komponen Gaji</h1>
                    <p class="text-sm text-slate-500">Ubah data jenis tunjangan atau potongan.</p>
                </div>
            </div>
        </section>

        <form action="{{ route('admin.salary-components.update', $salaryComponent) }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 md:p-8 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid gap-6">
                @if(auth()->user()->isAdminPusat())
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Unit (Opsional)</label>
                    <select name="unit_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Global (Berlaku untuk semua unit)</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $salaryComponent->unit_id) == $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Pilih Global jika komponen ini berlaku untuk seluruh unit.</p>
                    @error('unit_id') <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
                </div>
                @endif

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Nama Komponen</label>
                    <input type="text" name="name" value="{{ old('name', $salaryComponent->name) }}" 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500" 
                           required>
                    @error('name') <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Tipe Komponen</label>
                    <select name="type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="tunjangan" @selected(old('type', $salaryComponent->type) == 'tunjangan')>Tunjangan (Menambah Gaji)</option>
                        <option value="potongan" @selected(old('type', $salaryComponent->type) == 'potongan')>Potongan (Mengurangi Gaji)</option>
                    </select>
                    @error('type') <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Nominal Default (Rp)</label>
                    <input type="number" name="default_amount" value="{{ old('default_amount', (int)$salaryComponent->default_amount) }}" 
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500" 
                           required min="0">
                    <p class="mt-1.5 text-xs text-slate-500">Perubahan nominal ini tidak akan mempengaruhi karyawan yang sudah di-assign komponen ini sebelumnya dengan nominal khusus.</p>
                    @error('default_amount') <p class="mt-1 text-xs font-medium text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <a href="{{ route('admin.salary-components.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>
