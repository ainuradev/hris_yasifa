<x-layouts.admin title="Tambah Sesi: {{ $class->name }}">
    <x-flash-message />

    <div class="max-w-4xl mx-auto space-y-8">
        <section class="page-hero">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.jadwal-kelas.show', $class) }}" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-slate-600 transition-all">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <span class="app-eyebrow">Input Sesi Jadwal</span>
                    <h1>Tambah Jadwal: {{ $class->name }}</h1>
                    <p class="text-slate-500">Unit {{ $class->unit->name }} • {{ $class->academic_year }}</p>
                </div>
            </div>
        </section>

        <form action="{{ route('admin.jadwal-kelas.store-session', $class) }}" 
              method="POST" 
              class="rounded-[2.5rem] border border-slate-200 bg-white p-10 shadow-xl">
            @csrf

            <div class="grid gap-8">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">Mata Pelajaran</label>
                        <select name="subject_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">Guru Pengampu</label>
                        <select name="teacher_detail_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="">Pilih Guru</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('teacher_detail_id') == $teacher->id)>{{ $teacher->employee->name }}</option>
                            @endforeach
                        </select>
                        @error('teacher_detail_id') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">Hari</label>
                        <select name="day_name" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="">Pilih Hari</option>
                            @foreach ($days as $day)
                                <option value="{{ $day }}" @selected(old('day_name') == $day)>{{ $day }}</option>
                            @endforeach
                        </select>
                        @error('day_name') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">Waktu Mulai</label>
                        <input type="time" name="start_time" value="{{ old('start_time') }}" 
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-blue-500 focus:ring-blue-500" required>
                        @error('start_time') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">Waktu Selesai</label>
                        <input type="time" name="end_time" value="{{ old('end_time') }}" 
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-blue-500 focus:ring-blue-500" required>
                        @error('end_time') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">Jumlah Jam per Minggu</label>
                    <input type="number" step="0.5" name="hours_per_week" value="{{ old('hours_per_week', 2) }}" 
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-blue-500 focus:ring-blue-500" 
                           placeholder="Contoh: 2">
                    <p class="mt-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Digunakan untuk perhitungan honor mengajar.</p>
                    @error('hours_per_week') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div class="pt-6 border-t border-slate-100 flex justify-end gap-4">
                    <a href="{{ route('admin.jadwal-kelas.show', $class) }}" class="rounded-2xl border border-slate-200 px-8 py-4 text-sm font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 transition-all">Batal</a>
                    <button type="submit" class="rounded-2xl bg-[#1a2744] px-8 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all">
                        Simpan Sesi Jadwal
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>
