<div class="space-y-6">
    <!-- Filter Section -->
    <section class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h2 class="text-lg font-bold text-slate-800 mb-1">Filter Data</h2>
        <p class="text-sm text-slate-500 mb-6">Cari pegawai berdasarkan nama, NIK, unit, tipe, dan status secara real-time.</p>

        <div class="grid gap-4 lg:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Cari nama / NIK</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari karyawan..." class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Unit</label>
                <select wire:model.live="unit_id" class="block w-full py-2 px-3 border border-slate-300 bg-white rounded-lg focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                    <option value="">Semua unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tipe</label>
                <select wire:model.live="type" class="block w-full py-2 px-3 border border-slate-300 bg-white rounded-lg focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                    <option value="">Semua tipe</option>
                    <option value="guru">Guru</option>
                    <option value="non_guru">Non-guru</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                <select wire:model.live="status" class="block w-full py-2 px-3 border border-slate-300 bg-white rounded-lg focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                    <option value="">Semua status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
        </div>
    </section>

    <!-- Table Section -->
    <div class="table-container relative">
        <div wire:loading class="absolute inset-0 bg-white/50 z-10 flex items-center justify-center backdrop-blur-sm">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-teal-500 border-t-transparent"></div>
        </div>

        <div class="overflow-x-auto">
            <table class="table-auto-full">
                <thead class="table-header">
                    <tr>
                        <th class="px-6 py-4">Nama & Email</th>
                        <th class="px-6 py-4">NIK / NUPTK</th>
                        <th class="px-6 py-4">Unit</th>
                        <th class="px-6 py-4">Tipe & Jabatan</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        @php
                            $jabatan = $employee->teacherDetail?->jabatan ?? $employee->nonTeacherDetail?->jabatan ?? '-';
                            $initials = strtoupper(substr($employee->name, 0, 1)) . strtoupper(substr(explode(' ', $employee->name)[1] ?? $employee->name, 0, 1));
                            $typeValue = $employee->type->value ?? $employee->type;
                            $statusValue = $employee->status->value ?? $employee->status;
                        @endphp
                        <tr class="table-row table-row-zebra">
                            <td class="table-cell">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-bold shrink-0">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-900">{{ $employee->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $employee->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="table-cell text-slate-600 font-mono text-sm">
                                <div>{{ $employee->nik }}</div>
                                @if ($employee->type->value === 'guru' || $employee->type === 'guru')
                                    <div class="text-xs text-teal-600 mt-0.5">{{ $employee->nuptk ?: 'NUPTK belum diset' }}</div>
                                @endif
                            </td>
                            <td class="table-cell">
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800">
                                    {{ $employee->unit?->jenjang ?? '-' }}
                                </span>
                            </td>
                            <td class="table-cell">
                                <div>
                                    <span class="badge {{ $typeValue === 'guru' ? 'badge-info' : 'badge-teal' }} mb-1">
                                        {{ str($typeValue)->replace('_', ' ')->title() }}
                                    </span>
                                    <div class="text-xs text-slate-500">{{ $jabatan }}</div>
                                </div>
                            </td>
                            <td class="table-cell">
                                <span class="badge {{ $statusValue === 'aktif' ? 'badge-success' : 'badge-danger' }}">
                                    {{ ucfirst($statusValue) }}
                                </span>
                            </td>
                            <td class="table-cell">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.karyawan.show', $employee) }}" class="inline-flex items-center rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                                        Detail
                                    </a>
                                    @if (auth()->user()->isAdminPusat() || auth()->user()->unit_id === $employee->unit_id)
                                        <a href="{{ route('admin.karyawan.edit', $employee) }}" class="inline-flex items-center rounded-md bg-teal-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-600">
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-12 w-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <p class="text-base font-medium">Data karyawan tidak ditemukan</p>
                                    <p class="text-sm mt-1">Coba sesuaikan filter pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if ($employees->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>
