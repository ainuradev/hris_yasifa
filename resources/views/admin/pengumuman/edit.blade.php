<x-layouts.admin title="Edit Pengumuman">
    <x-flash-message />

    <div class="max-w-3xl mx-auto space-y-8">
        <section class="page-hero">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.pengumuman.index') }}"
                   class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-slate-600 transition-all">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <span class="app-eyebrow">Pengumuman</span>
                    <h1>Edit Pengumuman</h1>
                </div>
            </div>
        </section>

        <form method="POST" action="{{ route('admin.pengumuman.update', $pengumuman) }}"
              class="rounded-[2.5rem] border border-slate-200 bg-white p-10 shadow-xl space-y-6"
              x-data="{ global: {{ $pengumuman->is_global && auth()->user()->isAdminPusat() ? 'true' : 'false' }} }">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Judul</label>
                <input type="text" name="title" value="{{ old('title', $pengumuman->title) }}" required
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500">
                @error('title') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Kategori</label>
                    <select name="category" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500">
                        <option value="umum"      @selected(old('category', $pengumuman->category->value) === 'umum')>Umum</option>
                        <option value="penggajian" @selected(old('category', $pengumuman->category->value) === 'penggajian')>Penggajian</option>
                        <option value="absensi"   @selected(old('category', $pengumuman->category->value) === 'absensi')>Absensi</option>
                        <option value="kegiatan"  @selected(old('category', $pengumuman->category->value) === 'kegiatan')>Kegiatan</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Berlaku Hingga (Opsional)</label>
                    <input type="date" name="expires_at"
                           value="{{ old('expires_at', $pengumuman->expires_at?->format('Y-m-d')) }}"
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500">
                    <p class="mt-1 text-[11px] text-slate-400">Pengumuman akan terhapus otomatis setelah tanggal ini.</p>
                    @error('expires_at') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>

            @if(auth()->user()->isAdminPusat())
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Unit Tujuan</label>
                    <select name="unit_id" :disabled="global" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100">
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $pengumuman->unit_id) == $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center">
                    <label class="inline-flex items-center gap-3 text-sm font-bold text-slate-700">
                        <input type="checkbox" name="is_global" value="1" x-model="global"
                               class="rounded border-slate-300"
                               {{ $pengumuman->is_global ? 'checked' : '' }}>
                        Kirim ke semua unit (global)
                    </label>
                </div>
            </div>
            @endif

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Isi Pengumuman</label>
                <textarea name="content" rows="6" required
                          class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500">{{ old('content', $pengumuman->content) }}</textarea>
                @error('content') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-4">
                <a href="{{ route('admin.pengumuman.index') }}"
                   class="rounded-2xl border border-slate-200 px-8 py-4 text-sm font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 transition-all">Batal</a>
                <button type="submit"
                        class="rounded-2xl bg-[#1a2744] px-8 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg hover:bg-slate-800 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
