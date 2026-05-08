<x-layouts.admin title="Tambah Karyawan">
    <x-flash-message />

    <div class="max-w-4xl mx-auto space-y-8" x-data="{ selectedType: '{{ old('type', 'guru') }}' }">

        {{-- Hero --}}
        <section class="page-hero">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.karyawan.index') }}"
                   class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-slate-600 transition-all">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <span class="app-eyebrow">Employee Provisioning</span>
                    <h1>Tambah Karyawan Baru</h1>
                    <p class="mt-1 text-slate-500 text-sm">Isi data dasar, role, unit, dan skema gaji karyawan.</p>
                </div>
            </div>
        </section>

        {{-- Password Notice --}}
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-6 py-4 flex gap-4 items-start">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-bold text-amber-800">Password Default</p>
                <p class="text-sm text-amber-700 mt-0.5">Password awal dibuat otomatis dari tanggal lahir format <strong>DDMMYYYY</strong>. Karyawan wajib mengganti password saat login pertama.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.karyawan.store') }}"
              class="rounded-[2.5rem] border border-slate-200 bg-white p-10 shadow-xl space-y-8">
            @csrf

            {{-- Section: Data Pribadi --}}
            <div>
                <h2 class="mb-6 text-xs font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-3">Data Pribadi</h2>
                <div class="grid gap-6 md:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 placeholder-slate-400 focus:border-teal-500 focus:ring-teal-500 focus:outline-none transition"
                               placeholder="Masukkan nama lengkap">
                        @error('name') <p class="mt-1.5 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">NIK <span class="text-rose-500">*</span></label>
                        <input type="text" name="nik" value="{{ old('nik') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 placeholder-slate-400 focus:border-teal-500 focus:ring-teal-500 focus:outline-none transition"
                               placeholder="Nomor Induk Karyawan">
                        @error('nik') <p class="mt-1.5 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="selectedType === 'guru'" x-transition>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">NUPTK</label>
                        <input type="text" name="nuptk" value="{{ old('nuptk') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 placeholder-slate-400 focus:border-teal-500 focus:ring-teal-500 focus:outline-none transition"
                               placeholder="Nomor Unik Pendidik dan Tenaga Kependidikan">
                        @error('nuptk') <p class="mt-1.5 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 placeholder-slate-400 focus:border-teal-500 focus:ring-teal-500 focus:outline-none transition"
                               placeholder="email@contoh.com">
                        @error('email') <p class="mt-1.5 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Tanggal Lahir <span class="text-rose-500">*</span></label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500 focus:outline-none transition">
                        @error('date_of_birth') <p class="mt-1.5 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">No. Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 placeholder-slate-400 focus:border-teal-500 focus:ring-teal-500 focus:outline-none transition"
                               placeholder="08xx-xxxx-xxxx">
                        @error('phone') <p class="mt-1.5 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                </div>
            </div>

            {{-- Section: Unit & Role --}}
            <div>
                <h2 class="mb-6 text-xs font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 pb-3">Penempatan & Jabatan</h2>
                <div class="grid gap-6 md:grid-cols-2">

                    @if (auth()->user()->isAdminPusat())
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Unit <span class="text-rose-500">*</span></label>
                        <select name="unit_id"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500 focus:outline-none transition">
                            <option value="">Pilih Unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        @error('unit_id') <p class="mt-1.5 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    @else
                        <input type="hidden" name="unit_id" value="{{ auth()->user()->unit_id }}">
                    @endif

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Tipe Karyawan <span class="text-rose-500">*</span></label>
                        <select name="type" x-model="selectedType"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500 focus:outline-none transition">
                            <option value="guru">Guru</option>
                            <option value="non_guru">Non-Guru</option>
                        </select>
                        @error('type') <p class="mt-1.5 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Jabatan</label>
                        <input type="text" name="jabatan" value="{{ old('jabatan') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 placeholder-slate-400 focus:border-teal-500 focus:ring-teal-500 focus:outline-none transition"
                               placeholder="Contoh: Guru Kelas, Staff TU">
                        @error('jabatan') <p class="mt-1.5 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Skema Gaji <span class="text-rose-500">*</span></label>
                        <select name="salary_rate_id"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500 focus:outline-none transition">
                            <option value="">Pilih Skema Gaji</option>
                            <template x-if="selectedType === 'guru'">
                                <span>
                                    @foreach (($salaryRates['guru'] ?? collect()) as $salaryRate)
                                        <option value="{{ $salaryRate->id }}" @selected(old('salary_rate_id') == $salaryRate->id)>
                                            {{ $salaryRate->jabatan }} — Rp {{ number_format($salaryRate->rate, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </span>
                            </template>
                            @foreach (($salaryRates['guru'] ?? collect()) as $salaryRate)
                                <option x-show="selectedType === 'guru'" value="{{ $salaryRate->id }}" @selected(old('salary_rate_id') == $salaryRate->id)>
                                    {{ $salaryRate->jabatan }} — Rp {{ number_format($salaryRate->rate, 0, ',', '.') }}
                                </option>
                            @endforeach
                            @foreach (($salaryRates['non_guru'] ?? collect()) as $salaryRate)
                                <option x-show="selectedType === 'non_guru'" value="{{ $salaryRate->id }}" @selected(old('salary_rate_id') == $salaryRate->id)>
                                    {{ $salaryRate->jabatan }} — Rp {{ number_format($salaryRate->rate, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        @error('salary_rate_id') <p class="mt-1.5 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    @if (auth()->user()->isAdminPusat())
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Role Sistem</label>
                        <select name="role"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500 focus:outline-none transition">
                            <option value="karyawan" @selected(old('role', 'karyawan') === 'karyawan')>Karyawan</option>
                            <option value="admin_unit" @selected(old('role') === 'admin_unit')>Admin Unit</option>
                            <option value="admin_pusat" @selected(old('role') === 'admin_pusat')>Admin Pusat</option>
                        </select>
                        @error('role') <p class="mt-1.5 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    @else
                        <input type="hidden" name="role" value="karyawan">
                    @endif

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Status Kepegawaian</label>
                        <select name="status"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500 focus:outline-none transition">
                            <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(old('status') === 'nonaktif')>Nonaktif</option>
                        </select>
                        @error('status') <p class="mt-1.5 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-500">Tanggal Kontrak Berakhir</label>
                        <input type="date" name="contract_end_date" value="{{ old('contract_end_date') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 focus:border-teal-500 focus:ring-teal-500 focus:outline-none transition">
                        @error('contract_end_date') <p class="mt-1.5 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                </div>
            </div>

            {{-- Actions --}}
            <div class="pt-4 border-t border-slate-100 flex justify-end gap-4">
                <a href="{{ route('admin.karyawan.index') }}"
                   class="rounded-2xl border border-slate-200 px-8 py-4 text-sm font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 transition-all">
                    Batal
                </a>
                <button type="submit"
                        class="rounded-2xl bg-[#1a2744] px-8 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all">
                    Simpan Karyawan
                </button>
            </div>

        </form>
    </div>
</x-layouts.admin>
