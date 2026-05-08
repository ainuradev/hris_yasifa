@php
    $photoUrl = $employee->photo_path ? asset('storage/' . $employee->photo_path) : null;
    $nameParts = preg_split('/\s+/', trim($employee->name));
    $initials = strtoupper(substr($nameParts[0] ?? $employee->name, 0, 1) . substr($nameParts[1] ?? $employee->name, 0, 1));
    $typeValue = $employee->type->value ?? $employee->type;
    $roleValue = $employee->role->value ?? $employee->role;
    $statusValue = $employee->status->value ?? $employee->status;
    $jobTitle = $employee->teacherDetail?->jabatan ?? $employee->nonTeacherDetail?->jabatan ?? '-';
    $contractEndDate = $employee->contract_end_date?->format('d M Y') ?? '-';
@endphp

<div class="space-y-6">
    <x-flash-message />

    @if ($employee->must_change_password)
        <section class="overflow-hidden rounded-[2rem] border border-amber-200 bg-gradient-to-r from-amber-50 to-white shadow-[0_18px_45px_-30px_rgba(120,53,15,0.45)]">
            <div class="flex items-start gap-4 px-6 py-5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-amber-900">Password bawaan masih aktif</h2>
                    <p class="mt-1 text-sm leading-6 text-amber-800">
                        Demi keamanan, ubah password terlebih dahulu sebelum melanjutkan ke menu lain.
                    </p>
                </div>
            </div>
        </section>
    @endif

    <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-[linear-gradient(135deg,rgba(255,255,255,0.96),rgba(240,253,244,0.94))] shadow-[0_24px_65px_-34px_rgba(13,148,136,0.35)] ring-1 ring-white/60">
        <div class="grid gap-6 px-6 py-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(260px,0.8fr)] lg:px-8 lg:py-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                @if ($photoUrl)
                    <img src="{{ $photoUrl }}" alt="Foto profil" class="h-24 w-24 rounded-[1.75rem] object-cover shadow-lg shadow-teal-900/10 ring-4 ring-white">
                @else
                    <div class="flex h-24 w-24 items-center justify-center rounded-[1.75rem] bg-gradient-to-br from-[#0D9488] to-[#134E4A] text-3xl font-bold text-white shadow-lg shadow-teal-900/20">
                        {{ $initials }}
                    </div>
                @endif

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-teal-600">Akun Personal</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-[#134E4A]">{{ $employee->name }}</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                        Kelola identitas akun, data pribadi, foto profil, dan keamanan akses Anda dari satu halaman yang ringkas.
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="rounded-full border border-teal-200 bg-teal-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-teal-700">
                            {{ str($typeValue)->replace('_', ' ')->title() }}
                        </span>
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                            {{ str($statusValue)->replace('_', ' ')->title() }}
                        </span>
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-600">
                            {{ str($roleValue)->replace('_', ' ')->title() }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                <div class="rounded-[1.5rem] border border-teal-100 bg-white/80 px-4 py-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">Unit</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800">{{ $employee->unit?->name ?? '-' }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-teal-100 bg-white/80 px-4 py-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">NIK</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800">{{ $employee->nik }}</p>
                </div>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.8fr)]">
            <div class="space-y-6">
                <section class="rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-[0_24px_65px_-38px_rgba(13,148,136,0.28)] ring-1 ring-white/60">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-teal-600">Profil Akun</p>
                            <h2 class="mt-1 text-xl font-bold text-[#134E4A]">Identitas Utama</h2>
                        </div>
                        <p class="text-sm text-slate-500">Gunakan email aktif agar notifikasi dan login tetap lancar.</p>
                    </div>

                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Nama lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $employee->name) }}" class="w-full rounded-2xl border border-teal-100 bg-teal-50/40 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $employee->email) }}" class="w-full rounded-2xl border border-teal-100 bg-teal-50/40 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2" x-data="{ fileName: '' }">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Foto profil</label>
                            <div class="flex flex-col gap-4 rounded-[1.5rem] border border-dashed border-teal-200 bg-[linear-gradient(135deg,rgba(240,253,244,0.85),rgba(255,255,255,0.92))] p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-100 text-teal-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">Unggah foto baru</p>
                                        <p class="text-sm text-slate-500" x-text="fileName || 'PNG/JPG maksimal 2 MB'"></p>
                                    </div>
                                </div>
                                <label class="inline-flex cursor-pointer items-center justify-center rounded-2xl bg-[#0D9488] px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-700/20 transition hover:bg-[#0B7A6F]">
                                    Pilih file
                                    <input type="file" name="photo" accept="image/*" class="hidden" @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                                </label>
                            </div>
                            @error('photo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-[0_24px_65px_-38px_rgba(13,148,136,0.28)] ring-1 ring-white/60">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-teal-600">Data Pribadi</p>
                        <h2 class="mt-1 text-xl font-bold text-[#134E4A]">Informasi Kontak dan Biodata</h2>
                    </div>

                    <div class="mt-6 grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">No. HP</label>
                            <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="w-full rounded-2xl border border-teal-100 bg-teal-50/40 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                            @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Tempat lahir</label>
                            <input type="text" name="place_of_birth" value="{{ old('place_of_birth', $employee->place_of_birth) }}" class="w-full rounded-2xl border border-teal-100 bg-teal-50/40 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                            @error('place_of_birth') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Tanggal lahir</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($employee->date_of_birth)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-teal-100 bg-teal-50/40 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                            @error('date_of_birth') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Jenis kelamin</label>
                            <select name="gender" class="w-full rounded-2xl border border-teal-100 bg-teal-50/40 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                                <option value="">Pilih</option>
                                <option value="laki_laki" @selected(old('gender', $employee->gender) === 'laki_laki')>Laki-laki</option>
                                <option value="perempuan" @selected(old('gender', $employee->gender) === 'perempuan')>Perempuan</option>
                            </select>
                            @error('gender') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Alamat</label>
                            <textarea name="address" rows="4" class="w-full rounded-2xl border border-teal-100 bg-teal-50/40 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">{{ old('address', $employee->address) }}</textarea>
                            @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-[0_24px_65px_-38px_rgba(13,148,136,0.28)] ring-1 ring-white/60">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-teal-600">Kontak Darurat</p>
                        <h2 class="mt-1 text-xl font-bold text-[#134E4A]">Cadangan Saat Mendesak</h2>
                    </div>

                    <div class="mt-6 space-y-5">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Nama kontak darurat</label>
                            <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}" class="w-full rounded-2xl border border-teal-100 bg-teal-50/40 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                            @error('emergency_contact_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">No. HP kontak darurat</label>
                            <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}" class="w-full rounded-2xl border border-teal-100 bg-teal-50/40 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                            @error('emergency_contact_phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-[0_24px_65px_-38px_rgba(13,148,136,0.28)] ring-1 ring-white/60">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-teal-600">Keamanan Akun</p>
                        <h2 class="mt-1 text-xl font-bold text-[#134E4A]">Perbarui Password</h2>
                    </div>

                    <div class="mt-6 space-y-5">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Password saat ini</label>
                            <input type="password" name="current_password" class="w-full rounded-2xl border border-teal-100 bg-teal-50/40 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                {{ $employee->must_change_password ? 'Masukkan password bawaan untuk verifikasi sebelum mengganti password.' : 'Kosongkan jika Anda tidak ingin mengganti password.' }}
                            </p>
                            @error('current_password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Password baru</label>
                            <input type="password" name="password" class="w-full rounded-2xl border border-teal-100 bg-teal-50/40 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                            @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Konfirmasi password baru</label>
                            <input type="password" name="password_confirmation" class="w-full rounded-2xl border border-teal-100 bg-teal-50/40 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                        </div>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-white/70 bg-[linear-gradient(135deg,rgba(240,253,244,0.92),rgba(255,255,255,0.96))] p-6 shadow-[0_24px_65px_-38px_rgba(13,148,136,0.28)] ring-1 ring-white/60">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-teal-600">Informasi Kepegawaian</p>
                        <h2 class="mt-1 text-xl font-bold text-[#134E4A]">Status Saat Ini</h2>
                    </div>

                    <dl class="mt-6 space-y-4">
                        <div class="flex items-start justify-between gap-4 border-b border-teal-100/80 pb-3">
                            <dt class="text-sm text-slate-500">Jabatan</dt>
                            <dd class="text-right text-sm font-semibold text-slate-800">{{ $jobTitle }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 border-b border-teal-100/80 pb-3">
                            <dt class="text-sm text-slate-500">Unit</dt>
                            <dd class="text-right text-sm font-semibold text-slate-800">{{ $employee->unit?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 border-b border-teal-100/80 pb-3">
                            <dt class="text-sm text-slate-500">Status</dt>
                            <dd class="text-right text-sm font-semibold text-slate-800">{{ str($statusValue)->replace('_', ' ')->title() }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-sm text-slate-500">Kontrak berakhir</dt>
                            <dd class="text-right text-sm font-semibold text-slate-800">{{ $contractEndDate }}</dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>

        <div class="sticky bottom-4 z-10 flex justify-end">
            <div class="flex w-full max-w-md items-center justify-between gap-4 rounded-[1.5rem] border border-white/70 bg-white/90 px-4 py-4 shadow-[0_22px_50px_-30px_rgba(13,148,136,0.35)] backdrop-blur md:w-auto">
                <p class="text-sm text-slate-500">Perubahan akan langsung memperbarui data akun Anda.</p>
                <button type="submit" class="shrink-0 rounded-2xl bg-[#0D9488] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-700/20 transition hover:bg-[#0B7A6F]">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>
