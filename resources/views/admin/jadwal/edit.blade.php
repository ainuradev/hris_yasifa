<x-layouts.admin title="Kelola Jadwal Guru">
    <x-flash-message />

    @php
        $teacher = $teacherDetail->employee;
        $sessions = $teacherDetail->teacherSubjectUnits
            ->sortBy(fn ($item) => sprintf('%s-%s', $item->day_name ?? 'Zzz', $item->start_time?->format('H:i') ?? '99:99'))
            ->values();
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Kelola Jadwal Guru</h1>
                <p class="text-sm text-slate-500">Atur sesi mengajar, jam pelajaran, dan kelas untuk {{ $teacher->name }}.</p>
            </div>
            <a href="{{ route('admin.jadwal.index') }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700">Kembali ke Jadwal Guru</a>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Guru</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $teacher->name }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-400">NIK</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $teacher->nik }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Unit</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $teacher->unit?->name ?? '-' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Rate</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">Rp {{ number_format($teacherDetail->salaryRate?->rate ?? 0, 0, ',', '.') }}/jam</p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-900">Sesi Mengajar Aktif</h2>
                <p class="text-sm text-slate-500">Edit tiap sesi secara langsung. Perubahan di sini akan memengaruhi kehadiran mengajar dan rekap jam pelajaran guru.</p>
            </div>

            <div class="space-y-4">
                @forelse ($sessions as $session)
                    <form method="POST" action="{{ route('admin.jadwal.sessions.update', $session) }}" class="rounded-2xl border border-slate-200 p-5">
                        @csrf
                        @method('PATCH')

                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-600">Unit</label>
                                <select name="unit_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" @disabled(! auth()->user()->isAdminPusat())>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}" @selected((int) old('unit_id', $session->unit_id) === (int) $unit->id)>{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                @if (! auth()->user()->isAdminPusat())
                                    <input type="hidden" name="unit_id" value="{{ auth()->user()->unit_id }}">
                                @endif
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-600">Mata Pelajaran</label>
                                <select name="subject_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}" @selected((int) old('subject_id', $session->subject_id) === (int) $subject->id)>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-600">Hari</label>
                                <select name="day_name" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $dayName)
                                        <option value="{{ $dayName }}" @selected(old('day_name', $session->day_name) === $dayName)>{{ $dayName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-600">Kelas</label>
                                <select name="class_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}" @selected((int) old('class_id', $session->class_id) === (int) $class->id)>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-600">Mulai</label>
                                <input type="time" name="start_time" value="{{ old('start_time', $session->start_time?->format('H:i')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-600">Selesai</label>
                                <input type="time" name="end_time" value="{{ old('end_time', $session->end_time?->format('H:i')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-600">Rekap Jam Pelajaran / Minggu</label>
                                <input type="number" min="1" max="40" name="hours_per_week" value="{{ old('hours_per_week', $session->hours_per_week) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-3">
                            <button class="rounded-2xl bg-[#1a2744] px-5 py-3 text-sm font-semibold text-white">Simpan Perubahan</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.jadwal.sessions.destroy', $session) }}" onsubmit="return confirm('Hapus sesi mengajar ini?')" class="mt-[-0.5rem]">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-2xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-600">Hapus Sesi</button>
                    </form>
                @empty
                    <x-empty-state message="Belum ada sesi mengajar untuk guru ini." />
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-900">Tambah Sesi Mengajar</h2>
                <p class="text-sm text-slate-500">Gunakan form ini untuk menambah jadwal mengajar baru bagi guru.</p>
            </div>

            <form method="POST" action="{{ route('admin.jadwal.sessions.store', $teacherDetail) }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Unit</label>
                    <select name="unit_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" @disabled(! auth()->user()->isAdminPusat())>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected((int) old('unit_id', $teacher->unit_id) === (int) $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    @if (! auth()->user()->isAdminPusat())
                        <input type="hidden" name="unit_id" value="{{ auth()->user()->unit_id }}">
                    @endif
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Mata Pelajaran</label>
                    <select name="subject_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Hari</label>
                    <select name="day_name" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                        @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $dayName)
                            <option value="{{ $dayName }}">{{ $dayName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Kelas</label>
                    <select name="class_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                        <option value="">Pilih Kelas</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected((int) old('class_id') === (int) $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Mulai</label>
                    <input type="time" name="start_time" value="{{ old('start_time') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Selesai</label>
                    <input type="time" name="end_time" value="{{ old('end_time') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-600">Rekap Jam Pelajaran / Minggu</label>
                    <input type="number" min="1" max="40" name="hours_per_week" value="{{ old('hours_per_week', 2) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-2 xl:col-span-3">
                    <button class="rounded-2xl bg-[#1a2744] px-5 py-3 text-sm font-semibold text-white">Tambah Sesi Mengajar</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
