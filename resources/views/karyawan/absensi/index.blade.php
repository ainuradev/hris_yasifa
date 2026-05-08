<x-layouts.karyawan title="{{ auth()->user()->type->value === 'guru' ? 'Kehadiran Mengajar' : 'Absensi Saya' }}">
    <x-flash-message />

    <div class="space-y-6">
        <section class="page-hero">
            <div class="page-hero-grid lg:grid-cols-[1.15fr_0.85fr] lg:items-end">
                <div>
                    <span class="app-eyebrow">Attendance Console</span>
                    <h1>{{ auth()->user()->type->value === 'guru' ? 'Kehadiran Mengajar' : 'Absensi Harian' }}</h1>
                    <p>{{ auth()->user()->type->value === 'guru' ? 'Rekap hadir guru dihitung per sesi mengajar pada periode ' . $attendancePeriodLabel . '.' : 'Riwayat absensi Anda untuk periode ' . $attendancePeriodLabel . '.' }}</p>
                </div>

                <div class="surface-card bg-slate-900 border-slate-800" x-data="{ time: '' }" x-init="time = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'}); setInterval(() => { time = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'}) }, 1000)">
                    <span class="app-eyebrow text-slate-400">Realtime Clock</span>
                    <div class="mt-3 text-4xl font-black text-blue-400" x-text="time"></div>
                    <p class="mt-3 text-sm text-slate-400">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>
        </section>

        <section class="surface-card">
            <h2>Status Hari Ini</h2>
            <p class="section-note">Jam absensi masuk wajib pukul 07:30 dan absen pulang pukul 15:00.</p>

            @if (! $schedule)
                <div class="flash-alert mt-5" data-tone="error"><div class="flash-alert-head"><div><h3>No Schedule</h3><p>Tidak ada jadwal hari ini.</p></div></div></div>
            @elseif (($schedule->day_type->value ?? $schedule->day_type) === 'libur')
                <div class="flash-alert mt-5" data-tone="success"><div class="flash-alert-head"><div><h3>Holiday</h3><p>Hari ini adalah hari libur.</p></div></div></div>
            @elseif ($attendanceToday && $attendanceToday->checked_out_at)
                <div class="flash-alert mt-5" data-tone="success"><div class="flash-alert-head"><div><h3>Attendance Complete</h3><p>Absensi hari ini selesai. Masuk {{ $attendanceToday->checked_in_at?->format('H:i') ?? '-' }}, pulang {{ $attendanceToday->checked_out_at?->format('H:i') ?? '-' }}.</p></div></div></div>
            @else
                <form method="POST" action="{{ route('karyawan.absensi.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div><label>Keterangan</label><textarea name="notes" rows="3"></textarea></div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="submit" class="btn-primary w-full sm:w-auto">
                            {{ $attendanceToday ? 'Tandai pulang' : 'Tandai hadir' }}
                        </button>
                    </div>
                </form>
            @endif

            {{-- Fitur Koreksi Absen --}}
            <div class="mt-8 border-t border-slate-100 pt-6" x-data="{ showCorrection: false }">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Lupa Absen?</h3>
                        <p class="text-xs text-slate-500">Ajukan koreksi jika Anda hadir tapi gagal mencatat absensi.</p>
                    </div>
                    <button @click="showCorrection = !showCorrection" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all">
                        <span x-show="!showCorrection">Ajukan Koreksi</span>
                        <span x-show="showCorrection">Batal</span>
                    </button>
                </div>

                <div x-show="showCorrection" class="mt-4 rounded-3xl border border-blue-100 bg-blue-50/50 p-6" style="display: none;">
                    <form action="{{ route('karyawan.absensi.koreksi') }}" method="POST" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
                        @csrf
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600 uppercase">Tanggal Kehadiran</label>
                            <input type="date" name="date" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600 uppercase">Bukti Foto (Max 2MB)</label>
                            <input type="file" name="proof" accept="image/*" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-bold text-slate-600 uppercase">Alasan Koreksi</label>
                            <textarea name="reason" rows="2" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Misal: Kendala sistem, Lupa tap kartu, dll..."></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="w-full rounded-2xl bg-blue-600 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 hover:scale-[1.01] transition-all">Kirim Pengajuan Koreksi</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mini-grid mt-6">
                <div class="mini-panel">
                    <span>Jam Masuk</span>
                    <strong>
                        {{ $attendanceToday?->checked_in_at?->format('H:i') ?? '-' }}
                    </strong>
                </div>
                <div class="mini-panel">
                    <span>Jam Pulang</span>
                    <strong>
                        {{ $attendanceToday?->checked_out_at?->format('H:i') ?? '-' }}
                    </strong>
                </div>
            </div>
        </section>

        <section class="table-container">
            <div class="px-6 py-6 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-900">Riwayat Kehadiran</h2>
                <p class="text-sm text-slate-500 mt-1">Riwayat sesi atau absensi harian pada periode aktif.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="table-auto-full">
                    <thead>
                        <tr class="table-header">
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Hari</th>
                            <th class="px-6 py-4 text-center">Jam Masuk</th>
                            <th class="px-6 py-4 text-center">Jam Pulang</th>
                            <th class="px-6 py-4 text-center">Durasi</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendances as $attendance)
                            @php $duration = ($attendance->checked_in_at && $attendance->checked_out_at) ? $attendance->checked_in_at->diff($attendance->checked_out_at)->format('%H:%I') : '-'; @endphp
                            <tr class="table-row">
                                <td class="px-6 py-4 font-medium text-slate-900">{{ $attendance->schedule?->work_date?->format('d M Y') }}</td>
                                <td class="px-6 py-4">{{ $attendance->schedule?->work_date?->translatedFormat('l') }}</td>
                                <td class="px-6 py-4 text-center">{{ $attendance->checked_in_at?->format('H:i') ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">{{ $attendance->checked_out_at?->format('H:i') ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">{{ $duration }}</td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $status = $attendance->status->value ?? $attendance->status;
                                        $badgeClass = match($status) {
                                            'hadir' => 'badge-success',
                                            'terlambat' => 'badge-warning',
                                            'izin', 'sakit' => 'badge-info',
                                            'alpa' => 'badge-danger',
                                            default => 'badge-gray',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">{{ $attendance->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div class="bg-slate-100 p-4 rounded-full mb-4">
                                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <h3 class="text-slate-900 font-semibold">Belum Ada Riwayat</h3>
                                        <p class="text-slate-500 text-sm mt-1">Belum ada riwayat absensi pada periode ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $attendances->links() }}
    </div>
</x-layouts.karyawan>
