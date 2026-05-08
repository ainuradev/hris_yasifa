<x-layouts.app>
    @slot('header')
        Mata Pelajaran
    @endslot
    <x-flash-message />

    <div class="max-w-5xl mx-auto space-y-8">

        <section class="page-hero">
            <div class="page-hero-grid lg:grid-cols-[1.3fr_0.7fr] lg:items-end">
                <div>
                    <span class="app-eyebrow">Master Data — KMA 1503 2025</span>
                    <h1>Daftar Mata Pelajaran per Unit</h1>
                    <p>Mata pelajaran ditampilkan sesuai jenjang masing-masing. Admin unit hanya dapat menambah mapel untuk unitnya sendiri.</p>
                </div>

                {{-- Form Tambah --}}
                <div class="w-full" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="btn-primary w-full sm:w-auto inline-flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Mata Pelajaran
                    </button>

                    <div x-show="open" x-transition @click.outside="open=false"
                         class="mt-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-lg"
                         style="display:none">
                        <form action="{{ route('admin.subjects.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="mb-1.5 block text-xs font-black uppercase tracking-widest text-slate-500">Nama Mata Pelajaran <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" required value="{{ old('name') }}"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="Contoh: Matematika, Al-Qur'an Hadis...">
                                @error('name') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-black uppercase tracking-widest text-slate-500">JP per Minggu <span class="text-rose-500">*</span></label>
                                <input type="number" name="jp_per_week" min="1" max="60" required value="{{ old('jp_per_week', 4) }}"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold focus:border-teal-500 focus:ring-teal-500"
                                       placeholder="Contoh: 4">
                                @error('jp_per_week') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>

                            @if(auth()->user()->isAdminPusat())
                            <div>
                                <label class="mb-1.5 block text-xs font-black uppercase tracking-widest text-slate-500">Tambah ke Unit</label>
                                <select name="unit_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold focus:border-teal-500 focus:ring-teal-500">
                                    <option value="">— Global (semua unit) —</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-[11px] text-slate-400">Kosongkan untuk mapel global (tidak terikat unit).</p>
                            </div>
                            @endif

                            <div class="flex gap-3">
                                <button type="submit" class="rounded-xl bg-teal-600 px-6 py-3 text-sm font-black uppercase tracking-widest text-white hover:bg-teal-700 transition-all">Simpan</button>
                                <button type="button" @click="open=false" class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        {{-- Global Subjects (Admin Pusat only) --}}
        @if(auth()->user()->isAdminPusat() && $globalSubjects->count() > 0)
        <section class="rounded-[2rem] border border-blue-200 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-8 py-5 bg-blue-50 border-b border-blue-100">
                <div>
                    <h2 class="text-sm font-black uppercase tracking-widest text-blue-700">Global</h2>
                    <p class="text-xs text-blue-500 mt-0.5">Tidak terikat unit tertentu</p>
                </div>
                <span class="rounded-full bg-blue-100 px-4 py-1 text-xs font-black text-blue-700">{{ $globalSubjects->count() }} Mapel</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @foreach($globalSubjects as $index => $subject)
                            <tr x-data="{ editing: false }" class="hover:bg-slate-50/50 transition-colors">
                                <td class="w-12 px-6 py-3 text-slate-400 text-center font-mono text-xs">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <span x-show="!editing" class="font-semibold text-slate-800">{{ $subject->name }}</span>
                                    <form x-show="editing" x-cloak action="{{ route('admin.subjects.update', $subject) }}" method="POST" class="flex items-center gap-2">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" value="{{ $subject->name }}" required class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm font-semibold focus:border-teal-500">
                                        <input type="number" name="jp_per_week" min="1" max="60" value="{{ $subject->jp_per_week }}" required class="w-24 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm font-semibold focus:border-teal-500">
                                        <button type="submit" class="rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-bold text-white">Simpan</button>
                                        <button type="button" @click="editing=false" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-500">Batal</button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-slate-500">{{ $subject->jp_per_week ?? '-' }} JP</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1" x-show="!editing">
                                        <button @click="editing=true" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-blue-600 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" class="inline" onsubmit="return confirm('Hapus mata pelajaran ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- Per-Unit Subjects --}}
        @foreach($units as $unit)
        <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-8 py-5 bg-slate-50 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">{{ $unit->name }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Jenjang: <strong>{{ $unit->jenjang ?? '—' }}</strong></p>
                </div>
                <span class="rounded-full bg-teal-100 px-4 py-1 text-xs font-black text-teal-700">{{ $unit->subjects->count() }} Mapel</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100">
                        @forelse($unit->subjects as $index => $subject)
                            <tr x-data="{ editing: false }" class="hover:bg-slate-50/50 transition-colors">
                                <td class="w-12 px-6 py-3 text-slate-400 text-center font-mono text-xs">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <span x-show="!editing" class="font-semibold text-slate-800">{{ $subject->name }}</span>
                                    <form x-show="editing" x-cloak action="{{ route('admin.subjects.update', $subject) }}" method="POST" class="flex items-center gap-2">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" value="{{ $subject->name }}" required class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm font-semibold focus:border-teal-500">
                                        <input type="number" name="jp_per_week" min="1" max="60" value="{{ $subject->jp_per_week }}" required class="w-24 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm font-semibold focus:border-teal-500">
                                        <button type="submit" class="rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-bold text-white">Simpan</button>
                                        <button type="button" @click="editing=false" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-500">Batal</button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-slate-500">{{ $subject->jp_per_week ?? '-' }} JP</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1" x-show="!editing">
                                        <button @click="editing=true" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-blue-600 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" class="inline" onsubmit="return confirm('Hapus mata pelajaran ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-6 text-center text-sm text-slate-400">Belum ada mata pelajaran untuk unit ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        @endforeach

    </div>
</x-layouts.app>
