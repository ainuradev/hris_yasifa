<x-layouts.admin title="Jadwal Guru">
    <x-flash-message />

    <div class="space-y-6">
        <section class="page-hero">
            <div class="page-hero-grid lg:grid-cols-[1fr_auto] lg:items-end">
                <div>
                    <span class="app-eyebrow">Teaching Schedule</span>
                    <h1>Manajemen Jadwal Mengajar</h1>
                    <p>Atur jadwal mengajar setiap guru per jam pelajaran dan hari secara terpusat.</p>
                </div>
            </div>
        </section>

        {{-- Filter & Search --}}
        <form method="GET" action="{{ route('admin.jadwal.index') }}"
              class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari nama guru atau NIK..."
                class="flex-1 min-w-48 rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">

            @if(auth()->user()->isAdminPusat())
                <select name="unit_id"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-slate-400 focus:outline-none">
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
                <a href="{{ route('admin.jadwal.index') }}" class="btn-secondary">Reset</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="table-container">
            <div class="overflow-x-auto">
                <table class="table-auto-full">
                    <thead class="table-header">
                        <tr>
                            <th class="table-cell">No</th>
                            <th class="table-cell">Nama Guru</th>
                            <th class="table-cell">NIK</th>
                            <th class="table-cell">Unit</th>
                            <th class="table-cell">Mata Pelajaran</th>
                            <th class="table-cell">Sesi/Minggu</th>
                            <th class="table-cell text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teacherDetails as $td)
                            @php
                                $subjects = $td->teacherSubjectUnits->pluck('subject.name')->filter()->unique()->sort()->values();
                                $totalSesi = $td->teacherSubjectUnits->count();
                            @endphp
                            <tr class="table-row">
                                <td class="table-cell text-slate-400">{{ $teacherDetails->firstItem() + $loop->index }}</td>
                                <td class="table-cell font-semibold text-slate-800">{{ $td->employee->name }}</td>
                                <td class="table-cell font-mono text-xs text-slate-500">{{ $td->employee->nik }}</td>
                                <td class="table-cell">
                                    <span class="badge badge-info">{{ $td->employee->unit->name ?? '—' }}</span>
                                </td>
                                <td class="table-cell">
                                    @if($subjects->isEmpty())
                                        <span class="text-xs italic text-slate-400">Belum ada jadwal</span>
                                    @else
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($subjects->take(3) as $mapel)
                                                <span class="badge badge-success">{{ $mapel }}</span>
                                            @endforeach
                                            @if($subjects->count() > 3)
                                                <span class="badge" style="background:#f1f5f9;color:#64748b">
                                                    +{{ $subjects->count() - 3 }} lainnya
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="table-cell">
                                    @if($totalSesi > 0)
                                        <strong>{{ $totalSesi }}</strong>
                                        <span class="text-xs text-slate-400">sesi</span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="table-cell text-center">
                                    <a href="{{ route('admin.jadwal.show', $td) }}" class="btn-primary py-1.5 px-3 text-xs">
                                        Atur Jadwal
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="table-cell py-12 text-center text-slate-400">
                                    Tidak ada data guru ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($teacherDetails->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $teacherDetails->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
