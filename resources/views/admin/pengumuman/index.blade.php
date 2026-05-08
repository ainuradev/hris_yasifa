<x-layouts.admin title="Pengumuman">
    <x-flash-message />

    <div class="space-y-8" x-data="{
        global: {{ auth()->user()->isAdminPusat() && old('is_global') ? 'true' : 'false' }},
        isHoliday: {{ old('is_holiday') ? 'true' : 'false' }}
    }">

        {{-- Hero --}}
        <div>
            <span class="app-eyebrow">Manajemen Informasi</span>
            <h1 class="text-2xl font-black text-slate-900">Pengumuman</h1>
            <p class="text-sm text-slate-500 mt-1">{{ auth()->user()->isAdminPusat() ? 'Kirim pengumuman untuk seluruh unit atau unit tertentu.' : 'Buat pengumuman yang berlaku untuk unit Anda.' }}</p>
        </div>

        {{-- Form Tambah --}}
        <form method="POST" action="{{ route('admin.pengumuman.store') }}"
              class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-xl space-y-6">
            @csrf

            <h2 class="text-xs font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-3">Buat Pengumuman Baru</h2>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Judul <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 placeholder-slate-400 focus:border-teal-500 focus:ring-teal-500"
                       placeholder="Judul pengumuman...">
                @error('title') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Kategori</label>
                    <select name="category" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500">
                        <option value="umum"      @selected(old('category') === 'umum')>Umum</option>
                        <option value="penggajian" @selected(old('category') === 'penggajian')>Penggajian</option>
                        <option value="absensi"   @selected(old('category') === 'absensi')>Absensi</option>
                        <option value="kegiatan"  @selected(old('category') === 'kegiatan')>Kegiatan</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Unit</label>
                    @if(auth()->user()->isAdminPusat())
                        <select name="unit_id" :disabled="global" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100">
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" @selected((string) old('unit_id', auth()->user()->unit_id) === (string) $unit->id)>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" name="unit_id" value="{{ auth()->user()->unit_id }}">
                        <div class="rounded-2xl border border-slate-200 bg-slate-100 px-5 py-4 text-sm font-semibold text-slate-600">{{ auth()->user()->unit?->name }}</div>
                    @endif
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Berlaku Hingga <span class="text-slate-400 font-medium">(Opsional)</span></label>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500">
                    <p class="mt-1 text-[11px] text-slate-400">Otomatis terhapus setelah tanggal ini.</p>
                </div>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Isi Pengumuman <span class="text-rose-500">*</span></label>
                <textarea name="content" rows="4" required
                          class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 placeholder-slate-400 focus:border-teal-500 focus:ring-teal-500"
                          placeholder="Tulis isi pengumuman di sini...">{{ old('content') }}</textarea>
                @error('content') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-wrap gap-6 items-start">
                <div class="space-y-3">
                    @if(auth()->user()->isAdminPusat())
                    <label class="inline-flex items-center gap-3 text-sm font-bold text-slate-700">
                        <input type="checkbox" name="is_global" value="1" x-model="global" class="rounded border-slate-300">
                        Kirim ke semua unit (global)
                    </label>
                    @endif

                    <label class="inline-flex items-center gap-3 text-sm font-bold text-amber-700">
                        <input type="checkbox" name="is_holiday" value="1" x-model="isHoliday" class="rounded border-amber-300 text-amber-600">
                        Tandai sebagai Hari Libur
                    </label>
                </div>

                <div x-show="isHoliday" x-transition class="space-y-1">
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500">Tanggal Libur</label>
                    <input type="date" name="holiday_date" value="{{ old('holiday_date', today()->toDateString()) }}"
                           class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold focus:border-amber-400">
                    <p class="text-[11px] text-amber-600 italic">*Tombol absen akan dinonaktifkan di tanggal ini.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit"
                        class="rounded-2xl bg-[#1a2744] px-8 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg hover:bg-slate-800 transition-all">
                    Kirim Pengumuman
                </button>
            </div>
        </form>

        {{-- List --}}
        <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Daftar Pengumuman</h2>
                <span class="text-xs text-slate-400">{{ $announcements->total() }} pengumuman</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-widest text-slate-500">Judul</th>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-widest text-slate-500">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-widest text-slate-500">Tujuan</th>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-widest text-slate-500">Dibuat</th>
                            <th class="px-4 py-3 text-left text-xs font-black uppercase tracking-widest text-slate-500">Kadaluarsa</th>
                            <th class="px-4 py-3 text-right text-xs font-black uppercase tracking-widest text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($announcements as $announcement)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-800">{{ $announcement->title }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ \Illuminate\Support\Str::limit($announcement->content, 70) }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-block rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">
                                    {{ ucfirst($announcement->category->value ?? $announcement->category) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span @class([
                                    'inline-block rounded-full px-3 py-1 text-xs font-bold',
                                    'bg-teal-100 text-teal-700' => $announcement->is_global,
                                    'bg-slate-100 text-slate-600' => !$announcement->is_global,
                                ])>
                                    {{ $announcement->is_global ? '🌐 Semua Unit' : ($announcement->unit?->name ?? '—') }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-500">
                                <p>{{ $announcement->createdBy?->name ?? '—' }}</p>
                                <p class="text-slate-400">{{ $announcement->created_at?->format('d M Y') }}</p>
                            </td>
                            <td class="px-4 py-4">
                                @if($announcement->expires_at)
                                    <span @class([
                                        'inline-block rounded-full px-3 py-1 text-xs font-bold',
                                        'bg-rose-100 text-rose-700' => $announcement->expires_at->isPast(),
                                        'bg-amber-100 text-amber-700' => !$announcement->expires_at->isPast(),
                                    ])>
                                        {{ $announcement->expires_at->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.pengumuman.edit', $announcement) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-blue-600 transition-colors"
                                       title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.pengumuman.destroy', $announcement) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="button"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors"
                                                @click="if(confirm('Yakin hapus pengumuman ini?')) $el.closest('form').submit()"
                                                title="Hapus">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <x-empty-state message="Belum ada pengumuman." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($announcements->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $announcements->links() }}
            </div>
            @endif
        </section>

    </div>
</x-layouts.admin>
