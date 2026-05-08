<x-layouts.app>
    @slot('header')
        Master Rate Gaji (Fungsional)
    @endslot

    <x-flash-message />

    <div class="max-w-5xl mx-auto space-y-8">
        <section class="page-hero">
            <div class="page-hero-grid lg:grid-cols-[1.3fr_0.7fr] lg:items-end">
                <div>
                    <span class="app-eyebrow">Master Data — Keuangan</span>
                    <h1>Atur Honor & Gaji Pokok</h1>
                    <p>Definisikan honor per jam untuk guru dan gaji pokok untuk karyawan non-guru berdasarkan jabatan fungsional mereka.</p>
                </div>

                {{-- Form Tambah --}}
                <div class="w-full" x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                            class="btn-primary w-full sm:w-auto inline-flex items-center gap-2 shadow-lg shadow-teal-500/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Rate Baru
                    </button>

                    <div x-show="open" x-transition @click.outside="open=false"
                         class="mt-3 rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
                         style="display:none">
                        <form action="{{ route('admin.salary-rates.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="mb-1.5 block text-xs font-black uppercase tracking-widest text-slate-500">Nama Jabatan / Level <span class="text-rose-500">*</span></label>
                                    <input type="text" name="jabatan" required value="{{ old('jabatan') }}"
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold focus:border-teal-500 focus:ring-teal-500"
                                           placeholder="Contoh: Guru Madya, Staff Senior...">
                                    @error('jabatan') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                                </div>
                                @if(auth()->user()->isAdminPusat())
                                <div>
                                    <label class="mb-1.5 block text-xs font-black uppercase tracking-widest text-slate-500">Unit Sekolah <span class="text-rose-500">*</span></label>
                                    <select name="unit_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold focus:border-teal-500 focus:ring-teal-500">
                                        <option value="">Pilih Unit...</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('unit_id') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                                </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="mb-1.5 block text-xs font-black uppercase tracking-widest text-slate-500">Tipe Karyawan <span class="text-rose-500">*</span></label>
                                    <select name="type" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold focus:border-teal-500 focus:ring-teal-500">
                                        <option value="guru">Guru (Honor per Jam)</option>
                                        <option value="non_guru">Non-Guru (Gaji Pokok)</option>
                                    </select>
                                    @error('type') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-black uppercase tracking-widest text-slate-500">Nominal (Rp) <span class="text-rose-500">*</span></label>
                                    <input type="number" name="rate" required value="{{ old('rate') }}"
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold focus:border-teal-500 focus:ring-teal-500"
                                           placeholder="Contoh: 50000">
                                    @error('rate') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="submit" class="rounded-xl bg-teal-600 px-6 py-3 text-sm font-black uppercase tracking-widest text-white hover:bg-teal-700 transition-all">Simpan Rate</button>
                                <button type="button" @click="open=false" class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid md:grid-cols-2 gap-8">
            {{-- Guru Section --}}
            <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm overflow-hidden h-fit">
                <div class="flex items-center justify-between px-8 py-5 bg-teal-50 border-b border-teal-100">
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-widest text-teal-700">Tipe Guru</h2>
                        <p class="text-xs text-teal-500 mt-0.5">Dihitung per jam tatap muka (JTM)</p>
                    </div>
                    <span class="rounded-full bg-teal-100 px-4 py-1 text-xs font-black text-teal-700">{{ $salaryRates->where('type.value', 'guru')->count() }} Item</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @foreach($salaryRates->where('type.value', 'guru') as $rate)
                                <tr x-data="{ editing: false }" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-4">
                                        <div x-show="!editing">
                                            <div class="flex items-center gap-2">
                                                <div class="font-bold text-slate-800">{{ $rate->jabatan }}</div>
                                                @if(auth()->user()->isAdminPusat())
                                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-500 uppercase">{{ $rate->unit?->name ?? 'Global' }}</span>
                                                @endif
                                            </div>
                                            <div class="text-xs font-bold text-teal-600">Rp {{ number_format($rate->rate, 0, ',', '.') }} / Jam</div>
                                        </div>
                                        
                                        <form x-show="editing" x-cloak action="{{ route('admin.salary-rates.update', $rate) }}" method="POST" class="space-y-3">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="type" value="guru">
                                            <div class="grid grid-cols-1 gap-2">
                                                <input type="text" name="jabatan" value="{{ $rate->jabatan }}" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold focus:border-teal-500">
                                                @if(auth()->user()->isAdminPusat())
                                                <select name="unit_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold focus:border-teal-500">
                                                    @foreach($units as $unit)
                                                        <option value="{{ $unit->id }}" {{ $rate->unit_id == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                                    @endforeach
                                                </select>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input type="number" name="rate" value="{{ $rate->rate }}" required class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold focus:border-teal-500">
                                                <button type="submit" class="rounded-lg bg-teal-600 px-3 py-2 text-xs font-bold text-white">Save</button>
                                                <button type="button" @click="editing=false" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-500">X</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="px-8 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1" x-show="!editing">
                                            <button @click="editing=true" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-teal-50 hover:text-teal-600 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <form action="{{ route('admin.salary-rates.destroy', $rate) }}" method="POST" onsubmit="return confirm('Hapus rate ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors">
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

            {{-- Non-Guru Section --}}
            <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm overflow-hidden h-fit">
                <div class="flex items-center justify-between px-8 py-5 bg-blue-50 border-b border-blue-100">
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-widest text-blue-700">Tipe Non-Guru</h2>
                        <p class="text-xs text-blue-500 mt-0.5">Dihitung flat per bulan</p>
                    </div>
                    <span class="rounded-full bg-blue-100 px-4 py-1 text-xs font-black text-blue-700">{{ $salaryRates->where('type.value', 'non_guru')->count() }} Item</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-100">
                            @foreach($salaryRates->where('type.value', 'non_guru') as $rate)
                                <tr x-data="{ editing: false }" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-4">
                                        <div x-show="!editing">
                                            <div class="flex items-center gap-2">
                                                <div class="font-bold text-slate-800">{{ $rate->jabatan }}</div>
                                                @if(auth()->user()->isAdminPusat())
                                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-500 uppercase">{{ $rate->unit?->name ?? 'Global' }}</span>
                                                @endif
                                            </div>
                                            <div class="text-xs font-bold text-blue-600">Rp {{ number_format($rate->rate, 0, ',', '.') }} / Bulan</div>
                                        </div>
                                        
                                        <form x-show="editing" x-cloak action="{{ route('admin.salary-rates.update', $rate) }}" method="POST" class="space-y-3">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="type" value="non_guru">
                                            <div class="grid grid-cols-1 gap-2">
                                                <input type="text" name="jabatan" value="{{ $rate->jabatan }}" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold focus:border-blue-500">
                                                @if(auth()->user()->isAdminPusat())
                                                <select name="unit_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold focus:border-blue-500">
                                                    @foreach($units as $unit)
                                                        <option value="{{ $unit->id }}" {{ $rate->unit_id == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                                    @endforeach
                                                </select>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input type="number" name="rate" value="{{ $rate->rate }}" required class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold focus:border-blue-500">
                                                <button type="submit" class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white">Save</button>
                                                <button type="button" @click="editing=false" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-500">X</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td class="px-8 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1" x-show="!editing">
                                            <button @click="editing=true" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <form action="{{ route('admin.salary-rates.destroy', $rate) }}" method="POST" onsubmit="return confirm('Hapus rate ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors">
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
        </div>
    </div>
</x-layouts.app>
