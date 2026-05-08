<x-layouts.admin title="Ajukan Karyawan">
    <x-flash-message />

    <div class="space-y-6" x-data="{ selectedType: '{{ old('type', 'guru') }}' }">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Ajukan Karyawan Baru</h1>
            <p class="text-sm text-slate-500">Pengajuan ini akan dikirim ke admin pusat untuk ditinjau sebelum data karyawan dibuat.</p>
        </div>

        <form method="POST" action="{{ route('admin.employee-requests.store') }}" enctype="multipart/form-data" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Lampirkan surat izin atau surat keterangan dari ketua yayasan/admin pusat sebagai dasar persetujuan.
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Unit</label><input type="text" value="{{ $unit?->name }}" disabled class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500"></div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Nama lengkap</label><input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">NIK</label><input type="text" name="nik" value="{{ old('nik') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@error('nik') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Email</label><input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Tanggal lahir</label><input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@error('date_of_birth') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Tipe</label><select name="type" x-model="selectedType" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"><option value="guru">Guru</option><option value="non_guru">Non-guru</option></select>@error('type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Jabatan</label><input type="text" name="jabatan" value="{{ old('jabatan') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@error('jabatan') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Salary rate</label>
                    <select name="salary_rate_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                        <option value="">Pilih salary rate</option>
                        @foreach (($salaryRates['guru'] ?? collect()) as $salaryRate)
                            <option x-show="selectedType === 'guru'" value="{{ $salaryRate->id }}" @selected(old('salary_rate_id') == $salaryRate->id)>{{ $salaryRate->jabatan }} - Rp {{ number_format($salaryRate->rate, 0, ',', '.') }}</option>
                        @endforeach
                        @foreach (($salaryRates['non_guru'] ?? collect()) as $salaryRate)
                            <option x-show="selectedType === 'non_guru'" value="{{ $salaryRate->id }}" @selected(old('salary_rate_id') == $salaryRate->id)>{{ $salaryRate->jabatan }} - Rp {{ number_format($salaryRate->rate, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    @error('salary_rate_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Status saat diajukan</label><select name="employment_status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"><option value="aktif" @selected(old('employment_status', 'aktif') === 'aktif')>Aktif</option><option value="nonaktif" @selected(old('employment_status') === 'nonaktif')>Nonaktif</option></select>@error('employment_status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Kontrak berakhir</label><input type="date" name="contract_end_date" value="{{ old('contract_end_date') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@error('contract_end_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div class="md:col-span-2"><label class="mb-2 block text-sm font-medium text-slate-700">Surat izin / keterangan</label><input type="file" name="approval_document" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@error('approval_document') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div class="md:col-span-2"><label class="mb-2 block text-sm font-medium text-slate-700">Catatan pengajuan</label><textarea name="approval_notes" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">{{ old('approval_notes') }}</textarea>@error('approval_notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
            </div>
            <div class="mt-8 flex gap-3"><button type="submit" class="rounded-2xl bg-[#1a2744] px-5 py-3 text-sm font-semibold text-white">Kirim Pengajuan</button><a href="{{ route('admin.employee-requests.index') }}" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700">Batal</a></div>
        </form>
    </div>
</x-layouts.admin>
