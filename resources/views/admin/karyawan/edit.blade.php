<x-layouts.admin title="Edit Karyawan">
    <x-flash-message />

    @php
        $selectedType = old('type', $employee->type->value ?? $employee->type);
        $detail = $employee->teacherDetail ?? $employee->nonTeacherDetail;
    @endphp

    <div class="space-y-6" x-data="{ selectedType: '{{ $selectedType }}' }">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Karyawan</h1>
            <p class="text-sm text-slate-500">Kosongkan password jika tidak ingin mengubah password. Jika diisi, karyawan akan diminta mengganti password lagi saat login berikutnya.</p>
        </div>

        <form method="POST" action="{{ route('admin.karyawan.update', $employee) }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Nama lengkap</label><input type="text" name="name" value="{{ old('name', $employee->name) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">NIK</label><input type="text" name="nik" value="{{ old('nik', $employee->nik) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@error('nik') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div x-show="selectedType === 'guru'" style="display: none;"><label class="mb-2 block text-sm font-medium text-slate-700">NUPTK</label><input type="text" name="nuptk" value="{{ old('nuptk', $employee->nuptk) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@error('nuptk') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Email</label><input type="email" name="email" value="{{ old('email', $employee->email) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Tanggal lahir</label><input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($employee->date_of_birth)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@error('date_of_birth') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Password baru</label><input type="password" name="password" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Konfirmasi password</label><input type="password" name="password_confirmation" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"></div>
                @if (auth()->user()->isAdminPusat())
                    <div><label class="mb-2 block text-sm font-medium text-slate-700">Unit</label><select name="unit_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@foreach ($units as $unit)<option value="{{ $unit->id }}" @selected((string) old('unit_id', $employee->unit_id) === (string) $unit->id)>{{ $unit->name }}</option>@endforeach</select></div>
                @else
                    <input type="hidden" name="unit_id" value="{{ $employee->unit_id }}">
                @endif
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Tipe</label><select name="type" x-model="selectedType" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"><option value="guru">Guru</option><option value="non_guru">Non-guru</option></select></div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Jabatan</label><input type="text" name="jabatan" value="{{ old('jabatan', $detail?->jabatan) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"></div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Salary rate</label><select name="salary_rate_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@foreach (($salaryRates['guru'] ?? collect()) as $salaryRate)<option x-show="selectedType === 'guru'" value="{{ $salaryRate->id }}" @selected((string) old('salary_rate_id', $detail?->salary_rate_id) === (string) $salaryRate->id)>{{ $salaryRate->jabatan }} - Rp {{ number_format($salaryRate->rate, 0, ',', '.') }}</option>@endforeach @foreach (($salaryRates['non_guru'] ?? collect()) as $salaryRate)<option x-show="selectedType === 'non_guru'" value="{{ $salaryRate->id }}" @selected((string) old('salary_rate_id', $detail?->salary_rate_id) === (string) $salaryRate->id)>{{ $salaryRate->jabatan }} - Rp {{ number_format($salaryRate->rate, 0, ',', '.') }}</option>@endforeach</select></div>
                @if (auth()->user()->isAdminPusat())
                    <div><label class="mb-2 block text-sm font-medium text-slate-700">Role</label><select name="role" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"><option value="karyawan" @selected((old('role', $employee->role->value ?? $employee->role)) === 'karyawan')>Karyawan</option><option value="admin_unit" @selected((old('role', $employee->role->value ?? $employee->role)) === 'admin_unit')>Admin Unit</option><option value="admin_pusat" @selected((old('role', $employee->role->value ?? $employee->role)) === 'admin_pusat')>Admin Pusat</option></select></div>
                @else
                    <input type="hidden" name="role" value="{{ $employee->role->value ?? $employee->role }}">
                @endif
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Status</label><select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"><option value="aktif" @selected((old('status', $employee->status->value ?? $employee->status)) === 'aktif')>Aktif</option><option value="nonaktif" @selected((old('status', $employee->status->value ?? $employee->status)) === 'nonaktif')>Nonaktif</option></select></div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Kontrak berakhir</label><input type="date" name="contract_end_date" value="{{ old('contract_end_date', optional($employee->contract_end_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@error('contract_end_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror</div>
            </div>
            <div class="mt-8 flex gap-3"><button type="submit" class="rounded-2xl bg-[#1a2744] px-5 py-3 text-sm font-semibold text-white">Simpan Perubahan</button><a href="{{ route('admin.karyawan.show', $employee) }}" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700">Batal</a></div>
        </form>
    </div>
</x-layouts.admin>
