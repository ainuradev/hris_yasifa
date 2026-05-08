<x-layouts.karyawan title="Jadwal Mengajar">
    <x-flash-message />

    @php
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    @endphp

    {{-- @click on outer div closes cards when clicking outside tab/card area --}}
    <div class="space-y-8" x-data="{ activeDay: '{{ $todayName }}' }" @click="activeDay = null">

        {{-- Hero Section --}}
        <section class="page-hero">
            <div class="page-hero-grid lg:grid-cols-[1.5fr_0.5fr] lg:items-end">
                <div>
                    <span class="app-eyebrow">Interactive Matrix</span>
                    <h1>Jadwal Mengajar & Presensi Sesi</h1>
                    <p>Status kehadiran per mata pelajaran otomatis terupdate setiap hari. Pilih hari untuk melihat detail.</p>
                </div>
                <div class="mini-grid">
                    <div class="mini-panel">
                        <span>Periode</span>
                        <strong>{{ $attendancePeriodLabel }}</strong>
                    </div>
                </div>
            </div>
        </section>

        {{-- Day Selector Tabs --}}
        {{-- Stop click propagation so tabs & cards don't trigger the outer close --}}
        <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6" @click.stop>
            @foreach ($days as $day)
                @php
                    $isToday       = $todayName === $day;
                    $itemsCount    = $groupedTeacherSubjectUnits->get($day, collect())->count();
                    $daySchedule   = $weekSchedules->get($day);
                    $dayIsHoliday  = $daySchedule && ($daySchedule->day_type->value ?? $daySchedule->day_type) === 'libur';
                    $dayDate       = $daySchedule?->work_date;
                    $dayIsPast     = $dayDate && $dayDate->lt(today());
                @endphp
                <button @click="activeDay = '{{ $day }}'"
                        :class="activeDay === '{{ $day }}'
                            ? '{{ $dayIsHoliday ? 'bg-rose-500 ring-rose-100' : 'bg-emerald-600 ring-emerald-50' }} text-white shadow-lg ring-4'
                            : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200'"
                        class="group relative flex flex-col items-center justify-center rounded-[2rem] py-6 px-4 transition-all duration-300">

                    <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-60">
                        {{ $isToday ? 'Hari Ini' : ($dayIsPast ? 'Lewat' : 'Mendatang') }}
                    </span>
                    <span class="mt-1 text-xl font-extrabold leading-none">{{ $day }}</span>
                    <span class="mt-3 text-[10px] font-bold uppercase opacity-60">
                        {{ $dayIsHoliday ? 'Libur' : $itemsCount . ' Sesi' }}
                    </span>

                </button>
            @endforeach
        </section>

        {{-- Subjects Matrix --}}
        <section class="relative min-h-[400px]" @click.stop>
            @foreach ($days as $day)
                @php
                    $isToday      = $todayName === $day;
                    $items        = $groupedTeacherSubjectUnits->get($day, collect());
                    $daySchedule  = $weekSchedules->get($day);
                    $dayDate      = $daySchedule?->work_date;
                    $dayIsHoliday = $daySchedule && ($daySchedule->day_type->value ?? $daySchedule->day_type) === 'libur';
                    $dayIsPast    = $dayDate && $dayDate->lt(today());
                    $dayIsFuture  = $dayDate && $dayDate->gt(today());

                    // Check if teacher has an approved daily leave for this date
                    $dailyLeave = $daySchedule ? $dailyLeaves->first(function ($leave) use ($daySchedule) {
                        return $daySchedule->work_date->between($leave->start_date, $leave->end_date);
                    }) : null;
                @endphp

                <div x-show="activeDay === '{{ $day }}'"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">

                    {{-- Holiday Banner --}}
                    @if($dayIsHoliday && $items->isEmpty())
                        <div class="col-span-full flex flex-col items-center justify-center py-20 text-center">
                            <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-rose-50 text-rose-300">
                                <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <h3 class="text-xl font-black text-slate-900">Hari Libur</h3>
                            <p class="mt-2 text-slate-500">Tidak ada kegiatan mengajar pada hari ini.</p>
                        </div>
                    @elseif($items->isEmpty())
                        <div class="col-span-full flex flex-col items-center justify-center py-20 text-center">
                            <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-slate-50 text-slate-200">
                                <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h3 class="text-xl font-black text-slate-900">Tidak Ada Jadwal</h3>
                            <p class="mt-2 text-slate-500">Anda tidak memiliki jadwal mengajar di hari {{ $day }}.</p>
                        </div>
                    @else
                        @foreach ($items as $item)
                            @php
                                $dayDailyAttendance = $daySchedule ? $dailyAttendances->get($daySchedule->id) : null;
                                $dailyStatus = strtolower($dayDailyAttendance?->status->value ?? $dayDailyAttendance?->status ?? '');
                                $sessionAttendance = $daySchedule
                                    ? $sessionAttendances->first(fn($a) =>
                                        ($a->jadwal_id ?? null) === $item->jadwal_id &&
                                        $a->schedule_id == $daySchedule->id
                                    )
                                    : null;
                                $subjectPermission = $daySchedule
                                    ? $subjectPermissions->first(fn($permission) =>
                                        (($permission->jadwal_id ?? null) === $item->jadwal_id || $permission->teacher_subject_unit_id == $item->id) &&
                                        $permission->date?->toDateString() === $daySchedule->work_date->toDateString()
                                    )
                                    : null;

                                $status = null;
                                if ($dayIsHoliday || $dailyLeave) {
                                    $status = $dailyLeave ? 'izin_harian' : 'libur';
                                } elseif (($subjectPermission->status ?? null) === 'approved') {
                                    $status = 'izin_disetujui';
                                } elseif (($subjectPermission->status ?? null) === 'pending') {
                                    $status = 'izin_pending';
                                } elseif ($dailyStatus === 'hadir') {
                                    $status = 'hadir';
                                } elseif ($sessionAttendance && strtolower($sessionAttendance->status->value ?? $sessionAttendance->status) === 'hadir') {
                                    $status = 'hadir';
                                } elseif ($dayIsPast) {
                                    $status = 'tidak_terpenuhi';
                                } elseif ($isToday) {
                                    $status = 'today_action';
                                } else {
                                    $status = 'future_action';
                                }

                                // Check-in window for today
                                $dateStr    = $daySchedule ? $daySchedule->work_date->toDateString() : today()->toDateString();
                                $windowStart = $item->start_time ? \Carbon\Carbon::parse($dateStr.' '.$item->start_time->format('H:i:s')) : null;
                                $windowEnd   = $item->end_time   ? \Carbon\Carbon::parse($dateStr.' '.$item->end_time->format('H:i:s'))   : null;
                                $canCheckIn  = $status === 'today_action'
                                    && $windowStart && $windowEnd
                                    && now()->between($windowStart, $windowEnd);
                                $canRequestPermission = ! $dayIsHoliday
                                    && ! $dailyLeave
                                    && ($dayIsFuture || ($isToday && $windowStart && now()->lt($windowStart)))
                                    && $dailyStatus !== 'alpa';

                                // Style map
                                $cardBorder = match($status) {
                                    'hadir'           => 'border-emerald-200 bg-emerald-50/30',
                                    'izin_disetujui'  => 'border-amber-200 bg-amber-50/30',
                                    'izin_pending'    => 'border-slate-200 bg-slate-50',
                                    'tidak_terpenuhi' => 'border-rose-200 bg-rose-50/30',
                                    'libur'           => 'border-rose-200 bg-rose-50/20',
                                    'izin_harian'     => 'border-amber-200 bg-amber-50/30',
                                    default           => 'border-slate-200 bg-white',
                                };
                            @endphp

                            <div class="relative overflow-hidden rounded-[2rem] border p-7 shadow-xl shadow-slate-200/50 transition-all hover:shadow-2xl {{ $cardBorder }}"
                                 x-data="{ showIzinModal: false }">

                                {{-- Card Header --}}
                                <div class="mb-6 flex items-start gap-4">
                                    <div class="h-12 w-12 shrink-0 rounded-2xl bg-white flex items-center justify-center text-emerald-600 shadow-sm border border-slate-100">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-extrabold text-slate-900 leading-tight">{{ $item->subject?->name }}</h3>
                                        <p class="mt-1 text-xs font-bold uppercase tracking-widest text-emerald-600">{{ $item->class?->name ?? '-' }}</p>
                                    </div>
                                </div>

                                {{-- Info --}}
                                <div class="mb-6 space-y-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/80 text-slate-400 border border-slate-100">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Waktu Sesi</p>
                                            <p class="text-sm font-bold text-slate-700">{{ $item->start_time?->format('H:i') }} – {{ $item->end_time?->format('H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/80 text-slate-400 border border-slate-100">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-7h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Unit</p>
                                            <p class="text-sm font-bold text-slate-700">{{ $item->unit?->name }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Status / Action Section --}}
                                <div class="pt-5 border-t border-slate-100/80">

                                    {{-- LIBUR --}}
                                    @if($status === 'libur')
                                        <div class="flex items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 py-4 text-sm font-black uppercase tracking-wider text-rose-600">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Hari Libur
                                        </div>

                                    {{-- HADIR --}}
                                    @elseif($status === 'hadir')
                                        <div class="flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 py-4 text-sm font-black uppercase tracking-wider text-emerald-700">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Hadir
                                        </div>

                                    {{-- IZIN DISETUJUI (session) --}}
                                    @elseif($status === 'izin_disetujui')
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 py-4 text-sm font-black uppercase tracking-wider text-amber-700">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Izin (Disetujui)
                                            </div>
                                            @if($subjectPermission?->reason)
                                                <p class="text-center text-xs text-amber-600 italic">"{{ $subjectPermission->reason }}"</p>
                                            @endif
                                        </div>

                                    {{-- IZIN HARIAN DISETUJUI --}}
                                    @elseif($status === 'izin_harian')
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 py-4 text-sm font-black uppercase tracking-wider text-amber-700">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Izin Harian (Disetujui)
                                            </div>
                                            @if($dailyLeave->reason ?? null)
                                                <p class="text-center text-xs text-amber-600 italic">"{{ $dailyLeave->reason }}"</p>
                                            @endif
                                        </div>

                                    {{-- IZIN PENDING --}}
                                    @elseif($status === 'izin_pending')
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-100 py-4 text-sm font-black uppercase tracking-wider text-slate-500">
                                                <svg class="h-5 w-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Menunggu ACC Admin
                                            </div>
                                            @if($subjectPermission?->reason)
                                                <p class="text-center text-xs text-slate-500 italic">"{{ $subjectPermission->reason }}"</p>
                                            @endif
                                        </div>

                                    {{-- TIDAK TERPENUHI (past, no record) --}}
                                    @elseif($status === 'tidak_terpenuhi')
                                        <div class="flex items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 py-4 text-sm font-black uppercase tracking-wider text-rose-600">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Tidak Terpenuhi
                                        </div>

                                    {{-- TODAY — Show action buttons --}}
                                    @elseif($status === 'today_action')
                                        <div class="grid grid-cols-1 gap-3">
                                            <button @click="showIzinModal = true"
                                                    @disabled(! $canRequestPermission)
                                                    class="w-full rounded-2xl border-2 border-slate-100 py-4 text-xs font-black uppercase tracking-widest text-slate-600 transition-all hover:bg-amber-50 hover:border-amber-200 hover:text-amber-700 disabled:cursor-not-allowed disabled:opacity-50">
                                                Izin
                                            </button>
                                        </div>
                                        @if(! $canCheckIn)
                                            <p class="mt-3 text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                                Hadir: {{ $item->start_time?->format('H:i') }} – {{ $item->end_time?->format('H:i') }}
                                            </p>
                                        @endif

                                    {{-- FUTURE — Only Izin available --}}
                                    @elseif($status === 'future_action')
                                        <div class="space-y-3">
                                            <button @click="showIzinModal = true"
                                                    @disabled(! $canRequestPermission)
                                                    class="w-full rounded-2xl border-2 border-amber-100 bg-amber-50 py-4 text-xs font-black uppercase tracking-widest text-amber-700 transition-all hover:bg-amber-100 hover:border-amber-300 disabled:cursor-not-allowed disabled:opacity-50">
                                                Ajukan Izin
                                            </button>
                                            <p class="text-center text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                                Presensi Hadir hanya tersedia pada hari H
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Izin Modal --}}
                                <template x-if="showIzinModal">
                                    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showIzinModal = false"></div>
                                        <div class="relative w-full max-w-lg overflow-hidden rounded-[2.5rem] bg-white shadow-2xl">
                                            <div class="absolute top-0 right-0 p-6">
                                                <button @click="showIzinModal = false" class="text-slate-400 hover:text-slate-600">
                                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <form method="POST" action="{{ route('karyawan.jadwal.sesi.izin', $item) }}" class="p-10">
                                                @csrf
                                                <input type="hidden" name="schedule_date" value="{{ $daySchedule?->work_date?->toDateString() ?? today()->toDateString() }}">
                                                <div class="mb-8 text-center">
                                                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-3xl bg-amber-50 text-amber-600 shadow-inner">
                                                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    </div>
                                                    <h2 class="text-2xl font-black text-slate-900">Pengajuan Izin Sesi</h2>
                                                    <p class="mt-2 text-slate-500">Sesi <strong>{{ $item->subject?->name }}</strong> — {{ $day }}, {{ $daySchedule?->work_date?->translatedFormat('d M Y') ?? '-' }}</p>
                                                </div>
                                                <div class="space-y-6">
                                                    <div>
                                                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">Alasan Izin</label>
                                                        <textarea name="notes" rows="4" required
                                                                  class="w-full rounded-[1.5rem] border-slate-200 bg-slate-50 p-5 text-sm font-medium focus:border-amber-400 focus:ring-amber-400"
                                                                  placeholder="Contoh: Ada keperluan keluarga mendadak..."></textarea>
                                                    </div>
                                                    <button type="submit" class="w-full rounded-[1.5rem] bg-amber-500 py-5 text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-amber-200 hover:bg-amber-600 hover:-translate-y-1 transition-all">
                                                        Kirim Permintaan Izin
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endforeach
        </section>
    </div>
</x-layouts.karyawan>
