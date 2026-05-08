<x-layouts.admin title="Data Rombel">
    <x-flash-message />

    <div class="space-y-6">
        <section class="page-hero">
            <div class="page-hero-grid lg:grid-cols-[1fr_auto] lg:items-end">
                <div>
                    <span class="app-eyebrow">Unified Schedule Management</span>
                    <h1>Data Rombel & Jadwal Mengajar</h1>
                    <p>Kelola data rombongan belajar (kelas) dan jadwal mengajar guru dalam satu pintu.</p>
                </div>
                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <a href="{{ route('admin.rombel.index', ['view_type' => 'kelas']) }}" 
                       class="rounded-xl px-4 py-2 text-sm font-bold transition-all {{ $viewType === 'kelas' ? 'bg-[#1a2744] text-white shadow-lg' : 'bg-white text-slate-600 border border-slate-200' }}">
                        Berdasarkan Kelas
                    </a>
                    <a href="{{ route('admin.rombel.index', ['view_type' => 'guru']) }}" 
                       class="rounded-xl px-4 py-2 text-sm font-bold transition-all {{ $viewType === 'guru' ? 'bg-[#1a2744] text-white shadow-lg' : 'bg-white text-slate-600 border border-slate-200' }}">
                        Berdasarkan Guru
                    </a>
                    @if($viewType === 'kelas')
                        <a href="{{ route('admin.rombel.create') }}" class="btn-primary ml-2">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Kelas
                        </a>
                    @endif
                </div>
            </div>
        </section>

        {{-- Filter & Search --}}
        <form method="GET" action="{{ route('admin.rombel.index') }}"
              class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <input type="hidden" name="view_type" value="{{ $viewType }}">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="{{ $viewType === 'kelas' ? 'Cari nama kelas...' : 'Cari nama guru atau NIK...' }}"
                class="flex-1 min-w-48 rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#1a2744] focus:outline-none focus:ring-2 focus:ring-slate-200">

            @if(auth()->user()->isAdminPusat())
                <select name="unit_id"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-[#1a2744] focus:outline-none">
                    <option value="">Semua Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected(request('unit_id') == $unit->id)>
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="btn-primary">Cari</button>
            @if(request()->hasAny(['search', 'unit_id']))
                <a href="{{ route('admin.rombel.index', ['view_type' => $viewType]) }}" class="btn-secondary">Reset</a>
            @endif
        </form>

        {{-- Table Berdasarkan Kelas --}}
        @if($viewType === 'kelas')
            <div class="table-container">
                <div class="overflow-x-auto">
                    <table class="table-auto-full">
                        <thead class="table-header">
                            <tr>
                                <th class="table-cell">No</th>
                                <th class="table-cell">Nama Kelas</th>
                                <th class="table-cell">Unit</th>
                                <th class="table-cell">Wali Kelas</th>
                                <th class="table-cell">Total Sesi</th>
                                <th class="table-cell text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classes as $class)
                                <tr class="table-row">
                                    <td class="table-cell text-slate-400">{{ $classes->firstItem() + $loop->index }}</td>
                                    <td class="table-cell">
                                        <div class="font-semibold text-slate-800">{{ $class->name }}</div>
                                        @if($class->major)
                                            <div class="text-xs text-slate-500">{{ $class->major }}</div>
                                        @endif
                                    </td>
                                    <td class="table-cell">
                                        <span class="badge badge-info">{{ $class->unit->name ?? '—' }}</span>
                                    </td>
                                    <td class="table-cell">
                                        @if($class->homeroomTeacher)
                                            <span class="font-medium text-slate-800">{{ $class->homeroomTeacher->employee->name }}</span>
                                        @else
                                            <span class="text-xs italic text-slate-400">Belum diatur</span>
                                        @endif
                                    </td>
                                    <td class="table-cell">
                                        @if($class->teacher_subject_units_count > 0)
                                            <strong>{{ $class->teacher_subject_units_count }}</strong>
                                            <span class="text-xs text-slate-400">sesi/minggu</span>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="table-cell text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.rombel.show', $class) }}" class="btn-primary py-1.5 px-3 text-xs">
                                                Atur Jadwal
                                            </a>
                                            <a href="{{ route('admin.rombel.edit', $class) }}" class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </a>
                                            <form action="{{ route('admin.rombel.destroy', $class) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus kelas ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="table-cell py-12 text-center text-slate-400">Tidak ada data kelas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($classes->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">{{ $classes->links() }}</div>
                @endif
            </div>
        @else
            {{-- Table Berdasarkan Guru --}}
            <div class="table-container">
                <div class="overflow-x-auto">
                    <table class="table-auto-full">
                        <thead class="table-header">
                            <tr>
                                <th class="table-cell">No</th>
                                <th class="table-cell">Nama Guru</th>
                                <th class="table-cell">NIK</th>
                                <th class="table-cell">Unit</th>
                                <th class="table-cell">Beban Mengajar</th>
                                <th class="table-cell text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($teacherDetails as $td)
                                <tr class="table-row">
                                    <td class="table-cell text-slate-400">{{ $teacherDetails->firstItem() + $loop->index }}</td>
                                    <td class="table-cell font-semibold text-slate-800">{{ $td->employee->name }}</td>
                                    <td class="table-cell font-mono text-xs text-slate-500">{{ $td->employee->nik }}</td>
                                    <td class="table-cell">
                                        <span class="badge badge-info">{{ $td->employee->unit->name ?? '—' }}</span>
                                    </td>
                                    <td class="table-cell">
                                        @php $totalSesi = $td->teacherSubjectUnits->count(); @endphp
                                        @if($totalSesi > 0)
                                            <strong>{{ $totalSesi }}</strong> <span class="text-xs text-slate-400">sesi/minggu</span>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="table-cell text-center">
                                        <a href="{{ route('admin.rombel.guru.show', $td) }}" class="btn-primary py-1.5 px-3 text-xs">
                                            Lihat Jadwal Guru
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="table-cell py-12 text-center text-slate-400">Tidak ada data guru.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($teacherDetails->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">{{ $teacherDetails->links() }}</div>
                @endif
            </div>
        @endif
    </div>
</x-layouts.admin>
