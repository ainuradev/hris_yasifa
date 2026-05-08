<x-layouts.admin title="Detail Karyawan">
    <x-flash-message />

    @php
        $initials = strtoupper(substr($employee->name, 0, 1)) . strtoupper(substr(explode(' ', $employee->name)[1] ?? $employee->name, 0, 1));
        $jabatan = $employee->teacherDetail?->jabatan ?? $employee->nonTeacherDetail?->jabatan ?? '-';
        $teacherSubjectUnits = $employee->teacherDetail?->teacherSubjectUnits ?? collect();
        $totalHours = $teacherSubjectUnits->sum('hours_per_week');
        $typeValue = $employee->type->value ?? $employee->type;
        $statusValue = $employee->status->value ?? $employee->status;
    @endphp

    <div class="space-y-6" x-data="{ tab: 'info' }">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-[#1a2744] text-2xl font-bold text-white">{{ $initials }}</div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">{{ $employee->name }}</h1>
                        <p class="text-sm text-slate-500">{{ $jabatan }} - {{ $employee->unit?->name }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <x-pill :text="ucfirst($statusValue)" :color="$statusValue === 'aktif' ? 'green' : 'red'" />
                            <x-pill :text="str($typeValue)->replace('_', ' ')->title()" :color="$typeValue === 'guru' ? 'blue' : 'teal'" />
                        </div>
                    </div>
                </div>
                @if (auth()->user()->isAdmin())
                    <div class="flex items-center gap-3">
                        <form action="{{ route('admin.karyawan.reset-password', $employee) }}" method="POST" onsubmit="return confirm('Reset password karyawan ini menjadi 123456?')">
                            @csrf
                            <button type="submit" class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-600 hover:bg-rose-100 transition-colors">Reset Password</button>
                        </form>
                        <a href="{{ route('admin.karyawan.edit', $employee) }}" class="rounded-2xl bg-[#1a2744] px-5 py-3 text-sm font-semibold text-white">Edit</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap gap-2 border-b border-slate-100 pb-4">
                <button type="button" class="rounded-2xl px-4 py-2 text-sm font-semibold" :class="tab === 'info' ? 'bg-[#1a2744] text-white' : 'bg-slate-100 text-slate-700'" @click="tab='info'">Info</button>
                @if ($typeValue === 'guru')
                    <button type="button" class="rounded-2xl px-4 py-2 text-sm font-semibold" :class="tab === 'mengajar' ? 'bg-[#1a2744] text-white' : 'bg-slate-100 text-slate-700'" @click="tab='mengajar'">Mengajar</button>
                @endif
                <button type="button" class="rounded-2xl px-4 py-2 text-sm font-semibold" :class="tab === 'komponen' ? 'bg-[#1a2744] text-white' : 'bg-slate-100 text-slate-700'" @click="tab='komponen'">Tunjangan & Potongan</button>
                <button type="button" class="rounded-2xl px-4 py-2 text-sm font-semibold" :class="tab === 'riwayat' ? 'bg-[#1a2744] text-white' : 'bg-slate-100 text-slate-700'" @click="tab='riwayat'">Riwayat</button>
            </div>

            <div class="pt-6" x-show="tab === 'info'">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs uppercase text-slate-400">NIK</p><p class="mt-2 font-semibold text-slate-800">{{ $employee->nik }}</p></div>
                    @if($typeValue === 'guru')
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs uppercase text-slate-400">NUPTK</p><p class="mt-2 font-semibold text-slate-800">{{ $employee->nuptk ?? '-' }}</p></div>
                        <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs uppercase text-slate-400">Wali Kelas</p><p class="mt-2 font-semibold text-slate-800">{{ $employee->teacherDetail?->homeroomClass?->name ?? 'Bukan Wali Kelas' }}</p></div>
                    @endif
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs uppercase text-slate-400">Email</p><p class="mt-2 font-semibold text-slate-800">{{ $employee->email }}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs uppercase text-slate-400">Unit</p><p class="mt-2 font-semibold text-slate-800">{{ $employee->unit?->name }}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs uppercase text-slate-400">Tipe</p><p class="mt-2 font-semibold text-slate-800">{{ str($typeValue)->replace('_', ' ')->title() }}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs uppercase text-slate-400">Status</p><p class="mt-2 font-semibold text-slate-800">{{ ucfirst($statusValue) }}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs uppercase text-slate-400">Bergabung</p><p class="mt-2 font-semibold text-slate-800">{{ $employee->created_at?->format('d M Y') }}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-xs uppercase text-slate-400">Kontrak Berakhir</p><p class="mt-2 font-semibold text-slate-800">{{ $employee->contract_end_date?->format('d M Y') ?? '-' }}</p></div>
                </div>
            </div>

            @if ($typeValue === 'guru')
                <div class="space-y-4 pt-6" x-show="tab === 'mengajar'">
                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-semibold text-slate-500">Mata Pelajaran</th><th class="px-4 py-3 text-left font-semibold text-slate-500">Hari</th><th class="px-4 py-3 text-left font-semibold text-slate-500">Jam</th><th class="px-4 py-3 text-left font-semibold text-slate-500">Kelas</th><th class="px-4 py-3 text-left font-semibold text-slate-500">Unit</th><th class="px-4 py-3 text-left font-semibold text-slate-500">Jam/Minggu</th></tr></thead>
                            <tbody class="divide-y divide-slate-100 bg-white">@forelse ($teacherSubjectUnits as $item)<tr><td class="px-4 py-3">{{ $item->subject?->name }}</td><td class="px-4 py-3">{{ $item->day_name ?? '-' }}</td><td class="px-4 py-3">{{ $item->start_time?->format('H:i') ?? '-' }} - {{ $item->end_time?->format('H:i') ?? '-' }}</td><td class="px-4 py-3">{{ $item->class?->name ?? '-' }}</td><td class="px-4 py-3">{{ $item->unit?->name }}</td><td class="px-4 py-3">{{ $item->hours_per_week }} jam</td></tr>@empty<tr><td colspan="6" class="px-4 py-6"><x-empty-state message="Belum ada beban mengajar." /></td></tr>@endforelse</tbody>
                        </table>
                    </div>
                    <div class="rounded-2xl bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700">Total jam per minggu: {{ $totalHours }} jam</div>
                </div>
            @endif

            <div class="space-y-6 pt-6" x-show="tab === 'riwayat'">
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-slate-800">10 Absensi Terbaru</h3>
                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-100 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-semibold text-slate-500">Tanggal</th><th class="px-4 py-3 text-left font-semibold text-slate-500">Jam</th><th class="px-4 py-3 text-left font-semibold text-slate-500">Status</th></tr></thead><tbody class="divide-y divide-slate-100 bg-white">@forelse ($employee->attendances as $attendance)<tr><td class="px-4 py-3">{{ $attendance->schedule?->work_date?->format('d M Y') }}</td><td class="px-4 py-3">{{ $attendance->checked_in_at?->format('H:i') ?? '-' }}</td><td class="px-4 py-3"><x-pill :text="ucfirst($attendance->status->value ?? $attendance->status)" color="blue" /></td></tr>@empty<tr><td colspan="3" class="px-4 py-6"><x-empty-state message="Belum ada riwayat absensi." /></td></tr>@endforelse</tbody></table>
                    </div>
                </div>
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-slate-800">5 Payroll Terbaru</h3>
                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-100 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-semibold text-slate-500">Periode</th><th class="px-4 py-3 text-left font-semibold text-slate-500">Take Home</th><th class="px-4 py-3 text-left font-semibold text-slate-500">Status</th></tr></thead><tbody class="divide-y divide-slate-100 bg-white">@forelse ($employee->payrolls as $payroll)<tr><td class="px-4 py-3">{{ sprintf('%02d', $payroll->month) }}/{{ $payroll->year }}</td><td class="px-4 py-3">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</td><td class="px-4 py-3"><x-pill :text="ucfirst($payroll->status->value ?? $payroll->status)" color="green" /></td></tr>@empty<tr><td colspan="3" class="px-4 py-6"><x-empty-state message="Belum ada riwayat payroll." /></td></tr>@endforelse</tbody></table>
                    </div>
                </div>
            </div>

            <div class="space-y-6 pt-6" x-show="tab === 'komponen'">
                <div class="flex flex-col md:flex-row gap-6 items-start">
                    <div class="w-full md:w-2/3">
                        <div class="table-container">
                            <div class="overflow-x-auto">
                                <table class="table-auto-full">
                                    <thead class="table-header">
                                        <tr>
                                            <th class="table-cell">Komponen</th>
                                            <th class="table-cell">Tipe</th>
                                            <th class="table-cell text-right">Nominal</th>
                                            <th class="table-cell text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @forelse ($employee->salaryComponents as $empComp)
                                            <tr class="table-row" x-data="{ editMode: false }">
                                                <td class="table-cell font-medium text-slate-800">
                                                    {{ $empComp->salaryComponent->name }}
                                                    @if($empComp->amount != $empComp->salaryComponent->default_amount)
                                                        <span class="block text-[10px] text-amber-600 font-medium">Custom Override</span>
                                                    @endif
                                                </td>
                                                <td class="table-cell">
                                                    @if($empComp->salaryComponent->type === 'tunjangan')
                                                        <span class="badge badge-success">Tunjangan</span>
                                                    @else
                                                        <span class="badge badge-danger">Potongan</span>
                                                    @endif
                                                </td>
                                                
                                                <td class="table-cell text-right" x-show="!editMode">
                                                    <span class="font-medium text-slate-800">Rp {{ number_format($empComp->amount, 0, ',', '.') }}</span>
                                                </td>
                                                
                                                <td class="table-cell text-center" x-show="!editMode">
                                                    <div class="flex items-center justify-center gap-2">
                                                        <button type="button" @click="editMode = true" class="text-blue-600 hover:text-blue-800 transition-colors p-1" title="Edit Nominal">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                        </button>
                                                        <form action="{{ route('admin.karyawan.salary-components.destroy', [$employee, $empComp]) }}" method="POST" class="inline" onsubmit="return confirm('Hapus komponen gaji ini?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="text-red-500 hover:text-red-700 transition-colors p-1" title="Hapus">
                                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                                
                                                {{-- Inline Edit Form --}}
                                                <td colspan="2" class="table-cell bg-slate-50" x-show="editMode" style="display: none;">
                                                    <form action="{{ route('admin.karyawan.salary-components.update', [$employee, $empComp]) }}" method="POST" class="flex items-center justify-end gap-2">
                                                        @csrf @method('PUT')
                                                        <input type="number" name="amount" value="{{ (int)$empComp->amount }}" class="w-32 rounded-xl border border-slate-200 px-3 py-1.5 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Nominal" required min="0">
                                                        <button type="button" @click="editMode = false" class="btn-secondary py-1.5 px-3 text-xs">Batal</button>
                                                        <button type="submit" class="btn-primary py-1.5 px-3 text-xs">Simpan</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="table-cell py-8 text-center">
                                                    <x-empty-state message="Belum ada tunjangan atau potongan." />
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="w-full md:w-1/3 rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
                        <h3 class="mb-4 text-sm font-semibold text-slate-800">Assign Komponen Baru</h3>
                        <form action="{{ route('admin.karyawan.salary-components.store', $employee) }}" method="POST" class="grid gap-4">
                            @csrf
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Pilih Komponen</label>
                                <select name="salary_component_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">-- Pilih Komponen --</option>
                                    @foreach ($availableComponents as $comp)
                                        <option value="{{ $comp->id }}">{{ $comp->name }} ({{ ucfirst($comp->type) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nominal Custom (Rp)</label>
                                <input type="number" name="amount" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Kosongkan untuk pakai default" min="0">
                                <p class="mt-1 text-[10px] text-slate-500">Isi hanya jika ingin mengubah nominal default khusus untuk karyawan ini.</p>
                            </div>
                            <button type="submit" class="btn-primary w-full mt-2">
                                Tambahkan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
