<x-layouts.karyawan title="Dashboard Karyawan">
    <x-flash-message />

    @php $employee = auth()->user(); @endphp

    <div class="space-y-6">
        <section class="page-hero">
            <div class="page-hero-grid lg:grid-cols-[1.3fr_0.7fr] lg:items-end">
                <div>
                    <span class="app-eyebrow">Personal Overview</span>
                    <h1>Selamat datang, {{ $employee->name }}.</h1>
                    <p>Berikut adalah ringkasan informasi kehadiran, jadwal, slip gaji, dan pemberitahuan terbaru Anda.</p>

                    <div class="page-hero-meta">
                        <div class="hero-chip">
                            <div>
                                <strong>Peran</strong>
                                <span>{{ str($employee->type->value ?? $employee->type)->replace('_', ' ')->title() }}</span>
                            </div>
                        </div>
                        <div class="hero-chip">
                            <div>
                                <strong>Periode</strong>
                                <span>{{ $attendanceSummary['period_label'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mini-grid">
                    <div class="mini-panel">
                        <span>Hadir</span>
                        <strong>{{ $attendanceSummary['hadir_count'] }}</strong>
                    </div>
                    <div class="mini-panel">
                        <span>Non-Hadir</span>
                        <strong>{{ $attendanceSummary['non_hadir_count'] }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="surface-card">
                <h2>{{ ($employee->type->value ?? $employee->type) === 'guru' ? 'Kehadiran Mengajar Hari Ini' : 'Status Absensi Hari Ini' }}</h2>
                <p class="section-note">Update aktivitas masuk kerja atau sesi mengajar tanpa harus pindah-pindah halaman.</p>

                @if (($employee->type->value ?? $employee->type) === 'guru')
                    @if (! $todaySchedule)
                        <div class="flash-alert mt-5" data-tone="error"><div class="flash-alert-head"><div><h3>No Schedule</h3><p>Anda belum memiliki jadwal hari ini.</p></div></div></div>
                    @elseif ($todayAttendance)
                        <div class="flash-alert mt-5" data-tone="success"><div class="flash-alert-head"><div><h3>Attendance Logged</h3><p>Kehadiran sudah tercatat pada {{ $todayAttendance->checked_in_at?->format('H:i') ?? '-' }}@if ($todayAttendance->checked_out_at), pulang tercatat pukul {{ $todayAttendance->checked_out_at->format('H:i') }}@endif.</p></div></div></div>
                        <div class="mt-4"><a href="{{ route('karyawan.absensi.index') }}" class="btn-primary">Lihat Presensi</a></div>
                    @else
                        <div class="flash-alert mt-5" data-tone="error"><div class="flash-alert-head"><div><h3>Ready to Check-In</h3><p>Anda belum absen hari ini. Jam absensi berlaku {{ $todaySchedule->check_in_start }} - {{ $todaySchedule->check_in_end }}.</p></div></div></div>
                        <div class="mt-4"><a href="{{ route('karyawan.absensi.index') }}" class="btn-primary">Buka Presensi</a></div>
                    @endif
                @else
                    @if (! $todaySchedule)
                        <div class="flash-alert mt-5" data-tone="error"><div class="flash-alert-head"><div><h3>No Schedule</h3><p>Anda belum memiliki jadwal hari ini.</p></div></div></div>
                    @elseif ($todayAttendance)
                        <div class="flash-alert mt-5" data-tone="success"><div class="flash-alert-head"><div><h3>Attendance Logged</h3><p>Anda sudah absen pada {{ $todayAttendance->checked_in_at?->format('H:i') ?? '-' }}@if ($todayAttendance->checked_out_at), pulang tercatat pukul {{ $todayAttendance->checked_out_at->format('H:i') }}@endif.</p></div></div></div>
                        @if (! $todayAttendance->checked_out_at)
                            <div class="mt-4"><a href="{{ route('karyawan.absensi.index') }}" class="btn-primary">Buka Presensi</a></div>
                        @endif
                    @else
                        <div class="flash-alert mt-5" data-tone="error"><div class="flash-alert-head"><div><h3>Ready to Check-In</h3><p>Anda belum absen hari ini. Jam absensi berlaku {{ $todaySchedule->check_in_start }} - {{ $todaySchedule->check_in_end }}.</p></div></div></div>
                        <div class="mt-4"><a href="{{ route('karyawan.absensi.index') }}" class="btn-primary">Buka Presensi</a></div>
                    @endif
                @endif

                <div class="mini-grid mt-6">
                    <div class="mini-panel">
                        <span>Periode</span>
                        <strong>{{ $attendanceSummary['period_label'] }}</strong>
                    </div>
                    <div class="mini-panel">
                        <span>Hadir</span>
                        <strong>{{ $attendanceSummary['hadir_count'] }} {{ ($employee->type->value ?? $employee->type) === 'guru' ? 'sesi' : 'catatan' }}</strong>
                    </div>
                    <div class="mini-panel">
                        <span>{{ ($employee->type->value ?? $employee->type) === 'guru' ? 'Tidak Hadir' : 'Non-Hadir' }}</span>
                        <strong>{{ $attendanceSummary['non_hadir_count'] }} {{ ($employee->type->value ?? $employee->type) === 'guru' ? 'sesi' : 'catatan' }}</strong>
                    </div>
                </div>
            </section>

            <section class="surface-card">
                <h2>{{ ($employee->type->value ?? $employee->type) === 'guru' ? 'Jadwal Mengajar Hari Ini' : 'Info Karyawan' }}</h2>
                <p class="section-note">Informasi cepat yang paling sering Anda butuhkan selama hari kerja.</p>

                @if (($employee->type->value ?? $employee->type) === 'guru')
                    <div class="surface-list mt-5" x-data="{ activePermissionId: null, reason: '' }">
                        @forelse ($jadwalMengajarHariIni as $item)
                            <div class="surface-list-item">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <p class="font-semibold text-slate-900">{{ $item->subject?->name }}</p>
                                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500">
                                            <span><i class="fa-solid fa-users mr-1"></i> {{ $item->class?->name ?? '-' }}</span>
                                            <span><i class="fa-solid fa-school mr-1"></i> {{ $item->unit?->name }}</span>
                                        </div>
                                        
                                        {{-- Status Izin --}}
                                        @if ($item->current_permission)
                                            <div class="mt-3">
                                                @php
                                                    $status = $item->current_permission->status;
                                                    $pillColor = match($status) {
                                                        'approved' => 'green',
                                                        'rejected' => 'red',
                                                        default => 'amber'
                                                    };
                                                @endphp
                                                <x-pill :text="'Izin ' . ucfirst($status)" :color="$pillColor" />
                                                <p class="mt-1 text-[10px] text-slate-400 italic">"{{ $item->current_permission->reason }}"</p>
                                            </div>
                                        @elseif ($item->start_time?->isPast())
                                            <div class="mt-3">
                                                <x-pill text="Selesai/Berjalan" color="slate" />
                                            </div>
                                        @else
                                            {{-- Tombol Ajukan Izin --}}
                                            <div class="mt-3" x-show="activePermissionId !== {{ $item->id }}">
                                                <button @click="activePermissionId = {{ $item->id }}" class="text-xs font-bold text-blue-600 hover:underline">
                                                    Ajukan Izin Jam Ini
                                                </button>
                                            </div>
                                            
                                            {{-- Form Izin --}}
                                            <div class="mt-3 rounded-2xl bg-slate-50 p-4 border border-slate-100" x-show="activePermissionId === {{ $item->id }}" style="display: none;">
                                                <form action="{{ route('karyawan.jadwal.sesi.izin', $item->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="schedule_date" value="{{ today()->toDateString() }}">
                                                    <div class="space-y-3">
                                                        <div>
                                                            <label class="text-[10px] font-bold uppercase text-slate-500">Alasan Izin</label>
                                                            <textarea name="notes" x-model="reason" class="mt-1 w-full rounded-xl border border-slate-200 p-2 text-xs focus:border-blue-500 focus:ring-blue-500" placeholder="Misal: Sakit, Tugas Dinas..." required></textarea>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" @click="activePermissionId = null" class="rounded-xl px-3 py-1.5 text-[10px] font-bold text-slate-500 bg-white border border-slate-200">Batal</button>
                                                            <button type="submit" class="rounded-xl px-3 py-1.5 text-[10px] font-bold text-white bg-blue-600 shadow-sm" :disabled="!reason">Kirim Izin</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <x-pill :text="($item->start_time?->format('H:i') ?? '-') . ' - ' . ($item->end_time?->format('H:i') ?? '-')" color="blue" />
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $item->hours_per_week }} JP</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <x-empty-state message="Tidak ada jadwal mengajar yang tampil hari ini." />
                        @endforelse
                    </div>
                @else
                    <div class="mini-grid mt-5">
                        <div class="mini-panel">
                            <span>Jabatan</span>
                            <strong>{{ $employee->nonTeacherDetail?->jabatan ?? '-' }}</strong>
                        </div>
                        <div class="mini-panel">
                            <span>Unit</span>
                            <strong>{{ $employee->unit?->name }}</strong>
                        </div>
                    </div>
                @endif
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="surface-card">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2>Slip Gaji Terbaru</h2>
                        <p class="section-note">Akses slip terbaru tanpa membuka menu panjang.</p>
                    </div>
                </div>

                <div class="surface-list mt-5">
                    @forelse ($latestPayrolls as $payroll)
                        @php($payrollStart = \Illuminate\Support\Carbon::create($payroll->year, $payroll->month, 1))
                        @php($payrollEnd = $payroll->paid_at ? $payroll->paid_at->copy() : $payrollStart->copy()->endOfMonth())
                        <a href="{{ route('karyawan.gaji.show', $payroll) }}" class="surface-list-item block hover:border-slate-300">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900">Periode {{ $payrollStart->translatedFormat('d M Y') }} - {{ $payrollEnd->translatedFormat('d M Y') }}</p>
                                    <p class="mt-2 text-sm text-slate-500">{{ ucfirst($payroll->status->value ?? $payroll->status) }}</p>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="font-bold text-slate-900">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Lihat detail</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <x-empty-state message="Belum ada slip gaji terbaru." />
                    @endforelse
                </div>
            </section>

            <section class="surface-card">
                <h2>Pengumuman Terbaru</h2>
                <p class="section-note">Informasi yayasan yang relevan untuk Anda.</p>

                <div class="surface-list mt-5">
                    @forelse ($announcements as $announcement)
                        <div class="surface-list-item">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $announcement->title }}</p>
                                    <p class="mt-2 text-sm text-slate-500">{{ $announcement->created_at?->format('d M Y') }}</p>
                                </div>
                                <x-pill :text="$announcement->is_global ? 'Semua unit' : ($announcement->unit?->jenjang ?? 'Unit')" color="blue" />
                            </div>
                        </div>
                    @empty
                        <x-empty-state message="Belum ada pengumuman untuk Anda." />
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-layouts.karyawan>
