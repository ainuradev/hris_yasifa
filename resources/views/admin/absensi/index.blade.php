<x-layouts.admin title="Rekap Kehadiran">
    <x-flash-message />

    <div class="space-y-6">
        <section class="page-hero">
            <div class="page-hero-grid lg:grid-cols-[1.35fr_0.65fr] lg:items-end">
                <div>
                    <span class="app-eyebrow">Attendance Monitor</span>
                    <h1>Monitoring kehadiran harian yang lebih tajam dan lebih gampang di-scan.</h1>
                    <p>Guru dihitung dari kehadiran per sesi mengajar, sedangkan non-guru dari absensi harian. Semua data ditampilkan dalam tampilan yang lebih konsisten dan mobile friendly.</p>
                    
                    {{-- Export Dropdown --}}
                    <div class="mt-4" x-data="{ exportOpen: false }">
                        <div class="relative inline-block">
                            <button @click="exportOpen = !exportOpen"
                                    class="btn-secondary inline-flex items-center gap-2 text-sm py-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Export Rekap Bulanan
                            </button>

                            <div x-show="exportOpen"
                                 x-transition
                                 @click.outside="exportOpen = false"
                                 class="absolute left-0 mt-2 w-72 rounded-2xl border border-slate-200 bg-white p-5 shadow-xl z-50"
                                 style="display:none;">
                                <h4 class="mb-4 text-sm font-black uppercase tracking-widest text-slate-700">Filter Export Absensi</h4>
                                <form action="{{ route('admin.absensi.export') }}" method="GET" class="space-y-3">
                                    @if(auth()->user()->isAdminPusat())
                                    <div>
                                        <label class="mb-1 block text-xs font-bold text-slate-500 uppercase tracking-wider">Unit</label>
                                        <select name="unit_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold focus:border-teal-500 focus:ring-teal-500">
                                            <option value="">Semua Unit</option>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}" @selected(request('unit_id') == $unit->id)>{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="mb-1 block text-xs font-bold text-slate-500 uppercase tracking-wider">Bulan</label>
                                            <select name="month" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold focus:border-teal-500 focus:ring-teal-500">
                                                @foreach(range(1, 12) as $m)
                                                    <option value="{{ $m }}" @selected($m == now()->month)>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-bold text-slate-500 uppercase tracking-wider">Tahun</label>
                                            <select name="year" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold focus:border-teal-500 focus:ring-teal-500">
                                                @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                                    <option value="{{ $y }}" @selected($y == now()->year)>{{ $y }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <button type="submit"
                                            class="mt-2 w-full rounded-xl bg-teal-600 py-3 text-sm font-black uppercase tracking-widest text-white shadow-md hover:bg-teal-700 transition-all inline-flex items-center justify-center gap-2">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Download .xlsx
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="mini-grid">
                    <div class="mini-panel">
                        <span>Tanggal</span>
            </div>
        </section>

        <section class="surface-form">
            <h2>Filter Monitoring</h2>
            <p class="section-note">Pilih tanggal dan unit untuk melihat kondisi kehadiran yang paling relevan.</p>

            <form method="GET" class="mt-6 grid gap-4 md:grid-cols-[1fr_1fr_auto]">
                <div>
                    <label>Tanggal</label>
                    <input type="date" name="date" value="{{ request('date', $date) }}">
                </div>
                <div>
                    <label>Unit</label>
                    <select name="unit_id" @change="$el.closest('form').submit()" @disabled(! auth()->user()->isAdminPusat())>
                        @if (auth()->user()->isAdminPusat())
                            <option value="">Semua unit</option>
                        @endif
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) ($selectedUnitId ?? request('unit_id')) === (string) $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    @if (! auth()->user()->isAdminPusat())
                        <input type="hidden" name="unit_id" value="{{ $selectedUnitId }}">
                    @endif
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full md:w-auto">Terapkan</button>
                </div>
            </form>
        </section>

        <!-- TABS PADA ALPINE JS -->
        <div x-data="{ tab: 'harian' }" class="mt-8">
            <div class="mb-6 flex space-x-1 rounded-xl bg-slate-100 p-1">
                <button @click="tab = 'harian'" :class="{'bg-white shadow text-slate-900': tab === 'harian', 'text-slate-500 hover:text-slate-700': tab !== 'harian'}" class="w-full rounded-lg py-2.5 text-sm font-bold leading-5 transition-all">
                    Data Harian
                </button>
                <button @click="tab = 'rekap'" :class="{'bg-white shadow text-slate-900': tab === 'rekap', 'text-slate-500 hover:text-slate-700': tab !== 'rekap'}" class="w-full rounded-lg py-2.5 text-sm font-bold leading-5 transition-all">
                    Rekap Bulanan
                </button>
            </div>

            <!-- TAB 1: DATA HARIAN -->
            <div x-show="tab === 'harian'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="surface-table">
                <div class="px-6 py-6">
                <h2>Rekap Kehadiran Harian</h2>
                <p class="section-note">Lihat status masuk, pulang, dan riwayat detail setiap pegawai pada tanggal terpilih.</p>
            </div>

            <div class="surface-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Pegawai</th>
                            <th>Unit</th>
                            <th>Masuk</th>
                            <th>Pulang</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th class="text-center">Riwayat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            @php
                                $employee = $attendance->employee;
                                $initials = strtoupper(substr($employee->name, 0, 1))
                                          . strtoupper(substr(explode(' ', $employee->name)[1] ?? $employee->name, 0, 1));
                                $statusVal = strtolower($attendance->status->value ?? $attendance->status);
                                $pillColor = match($statusVal) {
                                    'hadir' => 'green',
                                    'terlambat' => 'amber',
                                    'izin' => 'amber',
                                    'sakit' => 'blue',
                                    'alpa' => 'red',
                                    default => 'gray',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="data-avatar">{{ $initials }}</div>
                                        <div class="data-stack">
                                            <strong>{{ $employee->name }}</strong>
                                            <span>{{ $employee->nik }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="font-bold text-slate-700">{{ $employee->unit?->jenjang }}</span></td>
                                <td><span class="font-bold text-slate-900">{{ $attendance->checked_in_at?->format('H:i') ?? '—' }}</span></td>
                                <td><span class="font-bold text-slate-900">{{ $attendance->checked_out_at?->format('H:i') ?? '—' }}</span></td>
                                <td><x-pill :text="ucfirst($statusVal)" :color="$pillColor" /></td>
                                <td><span class="text-xs text-slate-500 italic">{{ $attendance->notes ?: 'Tidak ada catatan' }}</span></td>
                                <td class="text-center">
                                    <a href="{{ route('admin.absensi.show', $employee) }}" 
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-primary hover:text-white transition-all">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><x-empty-state message="Belum ada data absensi untuk tanggal ini." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-5">
                {{ $attendances->links() }}
            </div>
        </section>

        <section class="surface-table">
            <div class="px-6 py-6">
                <h2>Izin Sesi Guru (Menunggu ACC)</h2>
                <p class="section-note">Persetujuan izin per mata pelajaran/jam pelajaran guru.</p>
            </div>

            <div class="surface-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Guru</th>
                            <th>Unit</th>
                            <th>Mata Pelajaran</th>
                            <th>Jadwal Sesi</th>
                            <th>Alasan Izin</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingSessionLeaves as $sessionLeave)
                            <tr>
                                <td>
                                    <div class="data-stack">
                                        <strong>{{ $sessionLeave->employee?->name }}</strong>
                                        <span>{{ $sessionLeave->employee?->nik }}</span>
                                    </div>
                                </td>
                                <td><span class="font-bold text-slate-700 text-xs">{{ $sessionLeave->employee?->unit?->name }}</span></td>
                                <td>
                                    <span class="inline-flex items-center rounded-lg bg-indigo-50 border border-indigo-100 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-indigo-600">
                                        {{ $sessionLeave->teacherSubjectUnit?->subject?->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="data-stack">
                                        <strong>{{ $sessionLeave->schedule?->work_date->translatedFormat('d M Y') }}</strong>
                                        <span>{{ $sessionLeave->teacherSubjectUnit?->start_time?->format('H:i') }} - {{ $sessionLeave->teacherSubjectUnit?->end_time?->format('H:i') }}</span>
                                    </div>
                                </td>
                                <td><span class="text-xs text-slate-500 italic">"{{ $sessionLeave->notes }}"</span></td>
                                <td>
                                    <div class="flex justify-center gap-2">
                                        <form method="POST" action="{{ route('admin.absensi.session.approve', $sessionLeave) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn-success text-[10px] py-2 px-4 shadow-emerald-100">Setuju</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.absensi.session.reject', $sessionLeave) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn-danger text-[10px] py-2 px-4 shadow-rose-100">Tolak</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state message="Tidak ada izin sesi pending." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-5">
                {{ $pendingSessionLeaves->links() }}
            </div>
        </section>

        <section class="surface-table">
            <div class="px-6 py-6">
                <h2>Cuti Pending</h2>
                <p class="section-note">Pengajuan cuti harian yang masih menunggu persetujuan admin.</p>
            </div>

            <div class="surface-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Pegawai</th>
                            <th>Unit</th>
                            <th>Jenis Cuti</th>
                            <th>Rentang Tanggal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingLeaves as $leave)
                            <tr>
                                <td>
                                    <div class="data-stack">
                                        <strong>{{ $leave->employee?->name }}</strong>
                                        <span>{{ $leave->employee?->nik }}</span>
                                    </div>
                                </td>
                                <td><span class="font-bold text-slate-700 text-xs">{{ $leave->employee?->unit?->name }}</span></td>
                                <td>
                                    @php
                                        $jenisRaw = str_replace('_', ' ', $leave->leave_type->value ?? $leave->leave_type);
                                        $jenisLower = strtolower($jenisRaw);
                                        $leaveBadge = match(true) {
                                            str_contains($jenisLower, 'sakit') => 'bg-rose-50 border-rose-100 text-rose-600',
                                            str_contains($jenisLower, 'izin') => 'bg-amber-50 border-amber-100 text-amber-600',
                                            str_contains($jenisLower, 'tahunan') => 'bg-blue-50 border-blue-100 text-blue-600',
                                            default => 'bg-slate-50 border-slate-100 text-slate-600',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-lg border px-2.5 py-1 text-[11px] font-black uppercase tracking-wider {{ $leaveBadge }}">
                                        {{ $jenisRaw }}
                                    </span>
                                </td>
                                <td>
                                    <div class="data-stack">
                                        <strong>{{ $leave->start_date->format('d M Y') }}</strong>
                                        <span>Sampai {{ $leave->end_date->format('d M Y') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex justify-center gap-2">
                                        @if (! auth()->user()->isAdminPusat())
                                            <form method="POST" action="{{ route('admin.absensi.approve', $leave) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn-success py-2 px-4 shadow-emerald-100">Setuju</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.absensi.reject', $leave) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn-danger py-2 px-4 shadow-rose-100">Tolak</button>
                                            </form>
                                        @else
                                            <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Menunggu Admin Unit</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
@empty
                            <tr><td colspan="5"><x-empty-state message="Tidak ada cuti pending." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-5">
                {{ $pendingLeaves->links() }}
            </div>
        </section>
        </div>

        <!-- TAB 2: REKAP BULANAN -->
        <div x-show="tab === 'rekap'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;" class="surface-table">
            <div class="px-6 py-6">
                <h2>Rekap Matriks Kehadiran</h2>
                <p class="section-note">Rekap kehadiran bulanan seluruh pegawai di unit yang dipilih pada bulan ini.</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="table-auto-full whitespace-nowrap min-w-max text-sm">
                    <thead class="bg-slate-50 border-y border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-slate-900 bg-slate-50 sticky left-0 z-10 border-r border-slate-100">Nama Pegawai</th>
                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-slate-500">Tipe</th>
                            <!-- Days of month -->
                            @for($i = 1; $i <= \Carbon\Carbon::parse(request('date', now()))->daysInMonth; $i++)
                                <th class="px-2 py-4 text-center text-[10px] font-black uppercase tracking-tighter text-slate-400 border-l border-slate-100">{{ $i }}</th>
                            @endfor
                            <!-- Summary -->
                            <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-widest text-emerald-600 border-l-2 border-slate-200 bg-emerald-50/30">Hadir</th>
                            <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-widest text-teal-600 bg-teal-50/30">JTM</th>
                            <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-widest text-amber-600 bg-amber-50/30">T</th>
                            <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-widest text-blue-600 bg-blue-50/30">I</th>
                            <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-widest text-rose-600 bg-rose-50/30">A</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @if(isset($rekapMatriks) && count($rekapMatriks) > 0)
                            @foreach($rekapMatriks as $row)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-medium text-slate-900 bg-white group-hover:bg-slate-50 sticky left-0 z-10 shadow-[1px_0_0_0_#e2e8f0]">{{ $row['name'] }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $row['type'] }}</td>
                                    @foreach($row['days'] as $day => $status)
                                        <td class="px-2 py-3 text-center border-l border-slate-200 font-bold
                                            @if($status === 'H') text-emerald-600
                                            @elseif($status === 'T') text-amber-500
                                            @elseif($status === 'I') text-blue-500
                                            @elseif($status === 'S') text-blue-400
                                            @elseif($status === 'A') text-rose-600
                                            @else text-slate-300 font-normal @endif
                                        ">
                                            {{ $status }}
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-center font-bold text-emerald-700 border-l-2 border-slate-300">{{ $row['summary']['hadir'] }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-teal-700">{{ $row['summary']['jtm'] !== '-' ? $row['summary']['jtm'].'j' : '-' }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-amber-700">{{ $row['summary']['terlambat'] }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-blue-700">{{ $row['summary']['izin'] }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-rose-700">{{ $row['summary']['alpa'] }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="{{ \Carbon\Carbon::parse(request('date', now()))->daysInMonth + 7 }}" class="px-4 py-12 text-center text-slate-500">
                                    Data rekap bulan ini belum tersedia.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>
</x-layouts.admin>
