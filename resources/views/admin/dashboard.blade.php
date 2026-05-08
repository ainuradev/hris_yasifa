<x-layouts.admin title="Dashboard Admin">
    <x-flash-message />

    <div class="space-y-6">

        {{-- ── STAT CARDS ──────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">

            {{-- Card 1: Total Karyawan Aktif --}}
            <div class="stat-card group">
                <div class="stat-card-icon bg-teal-50 text-teal-600">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8zm6 0a3 3 0 100-6 3 3 0 000 6zM3 14a3 3 0 100-6 3 3 0 000 6z"/>
                    </svg>
                </div>
                <div class="stat-card-body">
                    <span class="stat-card-label">Total Karyawan</span>
                    <strong class="stat-card-value">{{ $stats['total_karyawan_aktif'] ?? 0 }}</strong>
                    <span class="stat-card-sub">karyawan aktif</span>
                </div>
            </div>

            {{-- Card 2: Hadir Hari Ini --}}
            <div class="stat-card group">
                <div class="stat-card-icon bg-emerald-50 text-emerald-600">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div class="stat-card-body">
                    <span class="stat-card-label">Hadir Hari Ini</span>
                    <strong class="stat-card-value">{{ $stats['hadir_hari_ini'] ?? 0 }}</strong>
                    <span class="stat-card-sub">sesi tercatat</span>
                </div>
            </div>

            {{-- Card 3: Karyawan Cuti / Cuti Pending --}}
            @if ($isAdminPusat)
            <div class="stat-card group">
                <div class="stat-card-icon bg-violet-50 text-violet-600">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="stat-card-body">
                    <span class="stat-card-label">Karyawan Cuti</span>
                    <strong class="stat-card-value">{{ $stats['karyawan_cuti_disetujui'] ?? 0 }}</strong>
                    <span class="stat-card-sub">sedang cuti hari ini</span>
                </div>
            </div>
            @else
            <div class="stat-card group">
                <div class="stat-card-icon bg-amber-50 text-amber-600">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="stat-card-body">
                    <span class="stat-card-label">Cuti Pending</span>
                    <strong class="stat-card-value">{{ $stats['cuti_pending'] ?? 0 }}</strong>
                    <span class="stat-card-sub">menunggu persetujuan</span>
                </div>
            </div>
            @endif

            {{-- Card 4: Total Payroll Dibayar Bulan Ini --}}
            <div class="stat-card group">
                <div class="stat-card-icon bg-blue-50 text-blue-600">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="stat-card-body">
                    <span class="stat-card-label">Payroll Bulan Ini</span>
                    <strong class="stat-card-value text-sm sm:text-base leading-tight">Rp&nbsp;{{ number_format($stats['total_payroll_dibayar_bulan_ini'] ?? 0, 0, ',', '.') }}</strong>
                    <span class="stat-card-sub">total dibayar</span>
                </div>
            </div>

        </div>


        {{-- ── MAIN CONTENT GRID ──────────────────────────────────── --}}
        <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">

            {{-- KIRI: Pie Chart Kehadiran per Unit + Pengumuman --}}
            <div class="space-y-6">

                {{-- Kehadiran per Unit (Pie Chart) --}}
                <section class="surface-card">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2>Kehadiran per Unit</h2>
                            <p class="section-note">Distribusi kehadiran bulan berjalan per unit.</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-600">
                            {{ now()->translatedFormat('F Y') }}
                        </span>
                    </div>

                    {{-- Pie Chart canvas --}}
                    <div class="mt-4" style="height: 140px; max-height: 140px; position: relative;">
                        <canvas id="unitPieChart"></canvas>
                    </div>

                    {{-- Legend per unit --}}
                    @php $unitColors = ['#0d9488','#3b82f6','#f59e0b','#f43f5e','#8b5cf6']; @endphp
                    <div class="mt-5 space-y-2.5">
                        @foreach (($stats['kehadiran_per_unit'] ?? collect()) as $idx => $item)
                        <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-2.5">
                            <div class="flex items-center gap-2.5">
                                <span class="flex-shrink-0 w-2.5 h-2.5 rounded-full"
                                      style="background: {{ $unitColors[$idx % count($unitColors)] }}"></span>
                                <span class="text-sm font-medium text-slate-700">{{ $item['unit']->name }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-400">{{ $item['hadir'] }}/{{ $item['total'] }} sesi</span>
                                <span class="text-sm font-bold tabular-nums w-10 text-right"
                                    style="color: {{ $item['persentase'] >= 75 ? '#10b981' : ($item['persentase'] >= 50 ? '#f59e0b' : '#f43f5e') }}">
                                    {{ $item['persentase'] }}%
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>

            </div>

            {{-- KANAN: Pie Chart Status Hari Ini --}}
            <div class="space-y-6">

                <section class="surface-card">
                    <h2>Status Hari Ini</h2>
                    <p class="section-note">Distribusi kehadiran seluruh karyawan hari ini.</p>

                    {{-- Pie Chart --}}
                    <div class="mt-4" style="height: 150px; max-height: 150px; position: relative;">
                        <canvas id="statusPieChart"></canvas>
                    </div>

                    {{-- Legend compact --}}
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <div class="flex items-center gap-2.5 rounded-lg bg-slate-50 px-3 py-2">
                            <span class="flex-shrink-0 w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <div>
                                <p class="text-[10px] text-slate-500 leading-none">Hadir</p>
                                <p class="text-base font-bold text-slate-800 leading-tight">{{ $stats['status_hari_ini']['hadir'] ?? 0 }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 rounded-lg bg-slate-50 px-3 py-2">
                            <span class="flex-shrink-0 w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <div>
                                <p class="text-[10px] text-slate-500 leading-none">Izin</p>
                                <p class="text-base font-bold text-slate-800 leading-tight">{{ $stats['status_hari_ini']['izin'] ?? 0 }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 rounded-lg bg-slate-50 px-3 py-2">
                            <span class="flex-shrink-0 w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <div>
                                <p class="text-[10px] text-slate-500 leading-none">Sakit</p>
                                <p class="text-base font-bold text-slate-800 leading-tight">{{ $stats['status_hari_ini']['sakit'] ?? 0 }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 rounded-lg bg-slate-50 px-3 py-2">
                            <span class="flex-shrink-0 w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <div>
                                <p class="text-[10px] text-slate-500 leading-none">Alpa</p>
                                <p class="text-base font-bold text-slate-800 leading-tight">{{ $stats['status_hari_ini']['alpa'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Pengumuman Terbaru --}}
                <section class="surface-card">
                    <h2>Pengumuman Terbaru</h2>
                    <p class="section-note">Broadcast terbaru yang perlu segera dilihat oleh tim.</p>

                    <div class="surface-list mt-4">
                        @forelse ($announcements as $announcement)
                        <div class="surface-list-item">
                            <div class="flex gap-3">
                                <div class="mt-1 flex-shrink-0">
                                    <div class="h-7 w-1 rounded-full {{ $announcement->is_global ? 'bg-blue-500' : 'bg-emerald-500' }}"></div>
                                </div>
                                <div class="flex flex-1 flex-col gap-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="text-sm font-semibold text-slate-800 leading-tight">{{ $announcement->title }}</h3>
                                        <x-pill
                                            :text="$announcement->is_global ? 'Global' : ($announcement->unit?->jenjang ?? 'Unit')"
                                            color="blue" />
                                    </div>
                                    <p class="text-xs leading-5 text-slate-500">
                                        {{ \Illuminate\Support\Str::limit($announcement->content, 80) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <x-empty-state message="Belum ada pengumuman terbaru." />
                        @endforelse
                    </div>
                </section>

            </div>
        </div>
    </div>

    {{-- Chart.js langsung di slot agar pasti dirender --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ── Pie Chart Kehadiran per Unit ──────────────────────────
            const ctxUnit = document.getElementById('unitPieChart');
            if (ctxUnit) {
                const unitLabels = {!! json_encode(($stats['kehadiran_per_unit'] ?? collect())->pluck('unit')->pluck('name')->values()) !!};
                const unitData   = {!! json_encode(($stats['kehadiran_per_unit'] ?? collect())->pluck('hadir')->values()) !!};
                const unitColors = ['#0d9488', '#3b82f6', '#f59e0b', '#f43f5e', '#8b5cf6'];

                new Chart(ctxUnit.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: unitLabels,
                        datasets: [{
                            data: unitData,
                            backgroundColor: unitColors.slice(0, unitLabels.length),
                            borderColor: '#fff',
                            borderWidth: 3,
                            hoverOffset: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleColor: '#f1f5f9',
                                bodyColor: '#94a3b8',
                                borderColor: '#334155',
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const pct   = total > 0
                                            ? ((context.raw / total) * 100).toFixed(1)
                                            : 0;
                                        return '  ' + context.label + ': ' + context.raw + ' sesi (' + pct + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // ── Pie Chart Status Hari Ini ─────────────────────────────
            const ctxPie = document.getElementById('statusPieChart');
            if (ctxPie) {
                const hadir = {{ $stats['status_hari_ini']['hadir'] ?? 0 }};
                const izin  = {{ $stats['status_hari_ini']['izin'] ?? 0 }};
                const sakit = {{ $stats['status_hari_ini']['sakit'] ?? 0 }};
                const alpa  = {{ $stats['status_hari_ini']['alpa'] ?? 0 }};
                const total = hadir + izin + sakit + alpa;

                new Chart(ctxPie.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: ['Hadir', 'Izin', 'Sakit', 'Alpa'],
                        datasets: [{
                            data: [hadir, izin, sakit, alpa],
                            backgroundColor: [
                                'rgba(16, 185, 129, 0.9)',
                                'rgba(59, 130, 246, 0.9)',
                                'rgba(245, 158, 11, 0.9)',
                                'rgba(244, 63, 94, 0.9)'
                            ],
                            borderColor: '#fff',
                            borderWidth: 2,
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                titleColor: '#f1f5f9',
                                bodyColor: '#94a3b8',
                                borderColor: '#334155',
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        const pct = total > 0
                                            ? ((context.raw / total) * 100).toFixed(1)
                                            : 0;
                                        return '  ' + context.label + ': ' + context.raw + ' sesi (' + pct + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-layouts.admin>