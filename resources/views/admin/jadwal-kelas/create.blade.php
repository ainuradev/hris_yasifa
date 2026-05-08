<x-layouts.admin title="Tambah Kelas Baru">
    <x-flash-message />

    <script>
        window.UNIT_JENJANG = @json($units->pluck('jenjang', 'id'));
        window.LEVEL_RANGES = @json($levelRanges);
    </script>

    <div class="max-w-4xl mx-auto space-y-8" x-data="kelasForm()">
        <section class="page-hero">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.rombel.index') }}" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-slate-600 transition-all">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <span class="app-eyebrow">Unified Schedule Management</span>
                    <h1>Tambah Kelas Baru</h1>
                </div>
            </div>
        </section>

        <form action="{{ route('admin.rombel.store') }}" method="POST"
              class="rounded-[2.5rem] border border-slate-200 bg-white p-10 shadow-xl">
            @csrf

            <div class="grid gap-8">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">Unit Pendidikan</label>
                        <select name="unit_id" x-model="selectedUnitId" @change="onUnitChange()"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-teal-500 focus:ring-teal-500"
                                @disabled(! auth()->user()->isAdminPusat())>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        @if (! auth()->user()->isAdminPusat())
                            <input type="hidden" name="unit_id" value="{{ auth()->user()->unit_id }}">
                        @endif
                        @error('unit_id') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">Nama Kelas / Rombel</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-teal-500 focus:ring-teal-500"
                               placeholder="Contoh: 7A, X IPA 1">
                        @error('name') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">
                            Tingkat / Level
                            <span x-show="levelHint" x-text="'(' + levelHint + ')'" class="text-teal-600 normal-case font-bold ml-1"></span>
                        </label>
                        <select name="level" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-teal-500 focus:ring-teal-500">
                            <option value="">Pilih Tingkat</option>
                            <template x-for="level in availableLevels" :key="level">
                                <option :value="level" x-text="'Tingkat ' + level"></option>
                            </template>
                        </select>
                        @error('level') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">Jurusan (Opsional)</label>
                        <select name="major" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-teal-500 focus:ring-teal-500">
                            <option value="">Tanpa Jurusan</option>
                            <option value="IPA" @selected(old('major') === 'IPA')>IPA (MIPA)</option>
                            <option value="IPS" @selected(old('major') === 'IPS')>IPS (IIS)</option>
                            <option value="Bahasa" @selected(old('major') === 'Bahasa')>Bahasa</option>
                            <option value="Agama" @selected(old('major') === 'Agama')>Keagamaan</option>
                        </select>
                        @error('major') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">Tahun Ajaran</label>
                        <input type="text" name="academic_year" value="{{ old('academic_year', '2025/2026') }}"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-teal-500 focus:ring-teal-500">
                        @error('academic_year') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">Wali Kelas (Opsional)</label>
                    <select name="homeroom_teacher_id" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-teal-500 focus:ring-teal-500">
                        <option value="">Pilih Wali Kelas</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('homeroom_teacher_id') == $teacher->id)>{{ $teacher->employee->name }}</option>
                        @endforeach
                    </select>
                    @error('homeroom_teacher_id') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
                    <input type="checkbox" name="allow_team_teaching" value="1" @checked(old('allow_team_teaching')) class="mt-1 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span>
                        <span class="block text-sm font-black text-slate-800">Aktifkan Team Teaching</span>
                        <span class="mt-1 block text-xs font-medium text-slate-500">Izinkan lebih dari satu guru mengajar pada jam dan rombel yang sama.</span>
                    </span>
                </label>

                <div class="pt-6 border-t border-slate-100 flex justify-end gap-4">
                    <a href="{{ route('admin.rombel.index') }}" class="rounded-2xl border border-slate-200 px-8 py-4 text-sm font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 transition-all">Batal</a>
                    <button type="submit" class="rounded-2xl bg-[#1a2744] px-8 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg hover:bg-slate-800 transition-all">Buat Kelas</button>
                </div>
            </div>
        </form>
    </div>

    <script>
    function kelasForm() {
        const unitJenjang = window.UNIT_JENJANG || {};
        const levelRanges = window.LEVEL_RANGES || {};
        const defaultUnitId = '{{ auth()->user()->isAdminPusat() ? '' : auth()->user()->unit_id }}';
        const firstUnitId = '{{ $units->first()?->id ?? '' }}';

        return {
            selectedUnitId: defaultUnitId || firstUnitId,
            availableLevels: [],
            levelHint: '',

            init() { this.onUnitChange(); },

            onUnitChange() {
                const jenjang = unitJenjang[this.selectedUnitId];
                const range = levelRanges[jenjang];
                if (range) {
                    this.levelHint = jenjang + ' Kelas ' + range.min + '–' + range.max;
                    const lvls = [];
                    for (let i = range.min; i <= range.max; i++) lvls.push(i);
                    this.availableLevels = lvls;
                } else {
                    this.levelHint = '';
                    const lvls = [];
                    for (let i = 1; i <= 12; i++) lvls.push(i);
                    this.availableLevels = lvls;
                }
            },
        };
    }
    </script>
</x-layouts.admin>
