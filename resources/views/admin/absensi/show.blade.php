<x-layouts.admin title="Riwayat Absensi Karyawan">
    <x-flash-message />

    @php
        $initials = strtoupper(substr($employee->name, 0, 1))
                  . strtoupper(substr(explode(' ', $employee->name)[1] ?? $employee->name, 0, 1));
    @endphp

    <div class="space-y-6">
        <section class="page-hero">
            <div class="page-hero-grid lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
                <div class="flex items-center gap-4">
                    <div class="data-avatar h-16 w-16 text-lg">{{ $initials }}</div>
                    <div>
                        <span class="app-eyebrow">Attendance History</span>
                        <h1>{{ $employee->name }}</h1>
                        <p>Riwayat absensi bulanan karyawan.</p>
                    </div>
                </div>
                <div class="mini-grid">
                    <div class="mini-panel"><span>Role</span><strong>{{ ucfirst($employee->type->value) }}</strong></div>
                    <div class="mini-panel"><span>Persentase</span><strong>{{ $rekap['persentase_kehadiran'] }}%</strong></div>
                </div>
            </div>
        </section>

        <section class="surface-form">
            <h2>Filter Periode</h2>
            <p class="section-note">Tampilkan riwayat berdasarkan bulan dan tahun tertentu.</p>

            <form method="GET" class="mt-6 grid gap-4 md:grid-cols-[1fr_1fr_auto]">
                <div>
                    <label>Bulan</label>
                    <input type="number" name="month" value="{{ $month }}" min="1" max="12" placeholder="1-12">
                </div>
                <div>
                    <label>Tahun</label>
                    <input type="number" name="year" value="{{ $year }}" min="2024" max="2030" placeholder="2024-2030">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full md:w-auto">Terapkan</button>
                </div>
            </form>
        </section>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">

            {{-- Card: Hadir --}}
            <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg"
                 style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-6 -right-6 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-emerald-100">Hadir Tepat</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $rekap['hadir'] }}</p>
                    <p class="mt-1 text-xs text-emerald-200">Kehadiran valid</p>
                </div>
            </div>

            {{-- Card: Terlambat --}}
            <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg"
                 style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-6 -right-6 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-amber-100">Terlambat</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $rekap['terlambat'] }}</p>
                    <p class="mt-1 text-xs text-amber-200">> 08:00</p>
                </div>
            </div>

            {{-- Card: Izin --}}
            <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg"
                 style="background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-6 -right-6 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-amber-100">Izin</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $rekap['izin'] }}</p>
                    <p class="mt-1 text-xs text-amber-200">Persetujuan izin</p>
                </div>
            </div>

            {{-- Card: Sakit --}}
            <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg"
                 style="background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-6 -right-6 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-blue-100">Sakit</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $rekap['sakit'] }}</p>
                    <p class="mt-1 text-xs text-blue-200">Status kesehatan</p>
                </div>
            </div>

            {{-- Card: Alpa --}}
            <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg"
                 style="background: linear-gradient(135deg, #be123c 0%, #f43f5e 100%);">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-6 -right-6 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-rose-100">Alpa</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $rekap['alpa'] }}</p>
                    <p class="mt-1 text-xs text-rose-200">Perlu evaluasi</p>
                </div>
            </div>

            {{-- Card: Kehadiran % --}}
            <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg"
                 style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-6 -right-6 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-emerald-100">Kehadiran</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $rekap['persentase_kehadiran'] }}%</p>
                    <p class="mt-1 text-xs text-emerald-200">Rasio periode</p>
                </div>
            </div>

        </div>

        <section class="surface-table">
            <div class="surface-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            @php
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
                                <td>{{ $attendance->schedule?->work_date?->format('d M Y') }}</td>
                                <td>{{ $attendance->checked_in_at?->format('H:i') ?? '-' }}</td>
                                <td>{{ $attendance->checked_out_at?->format('H:i') ?? '-' }}</td>
                                <td><x-pill :text="ucfirst($statusVal)" :color="$pillColor" /></td>
                                <td>{{ $attendance->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-empty-state message="Belum ada riwayat kehadiran pada periode ini." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-5">
                {{ $attendances->links() }}
            </div>
        </section>
    </div>
</x-layouts.admin>
