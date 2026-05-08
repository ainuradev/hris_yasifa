<x-layouts.admin title="Jadwal Guru — {{ $teacherDetail->employee->name }}">
    <x-flash-message />

    {{-- Pre-load data for Alpine.js --}}
    <script>
        window.ROMBEL_SAVE_URL = "{{ route('admin.rombel.guru.slot.save', $teacherDetail) }}";
        window.CSRF_TOKEN = "{{ csrf_token() }}";
    </script>

    @php
        function getSubjectColor($name) {
            $colors = [
                ['bg' => 'bg-indigo-50',  'border' => 'border-indigo-100', 'text' => 'text-indigo-700', 'sub' => 'text-indigo-500'],
                ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-100','text' => 'text-emerald-700','sub' => 'text-emerald-500'],
                ['bg' => 'bg-amber-50',   'border' => 'border-amber-100',  'text' => 'text-amber-700',  'sub' => 'text-amber-500'],
                ['bg' => 'bg-rose-50',    'border' => 'border-rose-100',   'text' => 'text-rose-700',   'sub' => 'text-rose-500'],
                ['bg' => 'bg-sky-50',     'border' => 'border-sky-100',    'text' => 'text-sky-700',    'sub' => 'text-sky-500'],
                ['bg' => 'bg-violet-50',  'border' => 'border-violet-100', 'text' => 'text-violet-700', 'sub' => 'text-violet-500'],
            ];
            $hash = crc32($name);
            return $colors[abs($hash) % count($colors)];
        }
    @endphp

    <div class="max-w-full mx-auto space-y-10 pb-20">

        {{-- Header --}}
        <section class="page-hero">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.rombel.index', ['view_type' => 'guru']) }}"
                   class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-slate-600 transition-all">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <span class="app-eyebrow">Unified Schedule Management</span>
                    <h1>Jadwal Mengajar — {{ $teacherDetail->employee->name }}</h1>
                    <p class="mt-1 flex flex-wrap items-center gap-3 text-sm text-slate-500 font-medium">
                        <span>NUPTK: <strong>{{ $teacherDetail->employee->nuptk ?? '—' }}</strong></span>
                        <span>•</span>
                        <span>Unit: <strong>{{ $teacherDetail->employee->unit->name ?? '—' }}</strong></span>
                        <span>•</span>
                        <span>Jabatan: <strong>{{ $teacherDetail->jabatan }}</strong></span>
                    </p>
                </div>
            </div>
        </section>

        {{-- Timetable Grid --}}
        <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm overflow-hidden"
                 x-data="guruTimetable()">

            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Grid Jadwal Guru</h2>
                    <p class="text-sm text-slate-500 mt-1">Klik sel untuk menentukan kelas dan mata pelajaran.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-4 py-4 text-left text-xs font-black uppercase tracking-widest text-slate-500 w-28">Waktu \ Hari</th>
                            @foreach($days as $day)
                                <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-widest text-slate-500">{{ $day }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($jpTimes as $jpNum => $jpTime)
                            <tr class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-black text-slate-800">JP {{ $jpNum }}</p>
                                    <p class="text-[11px] text-slate-400 font-medium">{{ $jpTime['label'] }}</p>
                                </td>
                                @foreach($days as $day)
                                    @php
                                        $sessions = $timetable[$jpNum][$day] ?? collect();
                                        $primary = $sessions->first();
                                    @endphp
                                    <td class="px-3 py-2 text-center">
                                        <button
                                            type="button"
                                            @click="openModal({{ $jpNum }}, '{{ $day }}', {{ $primary ? $primary->class_id : 'null' }}, {{ $primary ? $primary->subject_id : 'null' }})"
                                            class="w-full min-h-[85px] rounded-2xl border transition-all text-left px-4 py-3 group/cell subject-card
                                                {{ $sessions->isNotEmpty()
                                                    ? 'shadow-sm'
                                                    : 'border-dashed border-slate-200 bg-slate-50/50 hover:border-accent/40 hover:bg-accent/5' }}">
                                            @if($sessions->isNotEmpty())
                                                <div class="space-y-2">
                                                    @foreach($sessions as $session)
                                                        @php $c = getSubjectColor($session->subject?->name); @endphp
                                                        <div class="rounded-xl border {{ $c['border'] }} {{ $c['bg'] }} p-2 {{ $sessions->count() > 1 && ! $loop->last ? 'mb-2' : '' }}">
                                                            <p class="text-[10px] font-black uppercase tracking-wider {{ $c['text'] }} leading-tight">{{ $session->class?->name }}</p>
                                                            <p class="text-[11px] {{ $c['sub'] }} mt-0.5 leading-tight font-bold">{{ $session->subject?->name }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="flex items-center justify-center h-full gap-1 text-slate-300 group-hover/cell:text-indigo-400 transition-colors">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    <span class="text-[10px] font-bold uppercase tracking-wider">Isi Jadwal</span>
                                                </span>
                                            @endif
                                        </button>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ===== MODAL ===== --}}
            <div x-show="modalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 scale-95"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-md p-4"
                 @click.self="closeModal()"
                 style="display: none;">

                <div class="w-full max-w-md rounded-[3rem] bg-white shadow-2xl p-10 relative overflow-hidden"
                     @click.stop>
                    
                    <div class="absolute top-0 right-0 w-32 h-32 bg-accent/5 rounded-full -mr-16 -mt-16 blur-2xl"></div>

                    <div class="flex items-center justify-between mb-10 relative z-10">
                        <div>
                            <h3 class="text-xl font-black text-slate-900">Atur Slot Mengajar</h3>
                            <p class="text-sm text-slate-500 mt-1">
                                <span class="font-bold text-indigo-600" x-text="currentDay"></span>
                                <span class="mx-1">•</span>
                                <span x-text="'JP ' + currentJp"></span>
                            </p>
                        </div>
                        <button @click="closeModal()" class="h-10 w-10 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-slate-200 transition-all">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Class Selection --}}
                    <div class="mb-6">
                        <label class="mb-2.5 block text-xs font-black uppercase tracking-widest text-slate-400">Rombongan Belajar (Kelas)</label>
                        <select x-model="selectedClassId"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-indigo-500 focus:ring-indigo-500 transition-all">
                            <option value="">— Pilih Kelas —</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Subject Selection --}}
                    <div class="mb-8">
                        <label class="mb-2.5 block text-xs font-black uppercase tracking-widest text-slate-400">Mata Pelajaran</label>
                        <select x-model="selectedSubjectId"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-indigo-500 focus:ring-indigo-500 transition-all">
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <p x-show="errorMsg" x-text="errorMsg" class="mb-6 rounded-2xl bg-rose-50 px-5 py-4 text-sm font-bold text-rose-600 border border-rose-100"></p>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <button @click="saveSlot()"
                                :disabled="loading || !selectedClassId || !selectedSubjectId"
                                class="flex-1 btn-vibrant-accent">
                            <span x-show="!loading">Simpan Jadwal</span>
                            <span x-show="loading" class="flex items-center justify-center">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                        <button @click="clearSlot()"
                                x-show="hasExisting"
                                :disabled="loading"
                                class="btn-vibrant-rose sm:px-8">
                            Hapus
                        </button>
                        <button @click="closeModal()"
                                class="rounded-2xl border-2 border-slate-200 px-6 py-4 text-sm font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 transition-all cursor-pointer">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
    function guruTimetable() {
        return {
            modalOpen: false,
            currentJp: null,
            currentDay: null,
            selectedClassId: '',
            selectedSubjectId: '',
            hasExisting: false,
            loading: false,
            errorMsg: '',

            openModal(jp, day, classId, subjectId) {
                this.currentJp = jp;
                this.currentDay = day;
                this.selectedClassId = classId ? String(classId) : '';
                this.selectedSubjectId = subjectId ? String(subjectId) : '';
                this.hasExisting = !!classId;
                this.errorMsg = '';
                this.modalOpen = true;
            },

            closeModal() {
                this.modalOpen = false;
                this.loading = false;
            },

            async saveSlot() {
                if (!this.selectedClassId || !this.selectedSubjectId) return;
                this.loading = true;
                this.errorMsg = '';
                try {
                    const resp = await fetch(window.ROMBEL_SAVE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.CSRF_TOKEN,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            jp: this.currentJp,
                            day: this.currentDay,
                            class_id: this.selectedClassId,
                            subject_id: this.selectedSubjectId,
                        }),
                    });
                    const data = await resp.json();
                    if (!resp.ok) {
                        this.errorMsg = data.message || 'Terjadi kesalahan.';
                    } else {
                        window.location.reload();
                    }
                } catch (e) {
                    this.errorMsg = 'Gagal terhubung ke server.';
                } finally {
                    this.loading = false;
                }
            },

            async clearSlot() {
                this.loading = true;
                this.errorMsg = '';
                try {
                    const resp = await fetch(window.ROMBEL_SAVE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.CSRF_TOKEN,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            jp: this.currentJp,
                            day: this.currentDay,
                            class_id: null,
                            subject_id: null,
                        }),
                    });
                    if (resp.ok) {
                        window.location.reload();
                    }
                } catch (e) {
                    this.errorMsg = 'Gagal menghapus slot.';
                } finally {
                    this.loading = false;
                }
            },
        };
    }
    </script>
</x-layouts.admin>
