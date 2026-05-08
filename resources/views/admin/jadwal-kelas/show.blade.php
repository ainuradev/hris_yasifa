<x-layouts.admin title="Data Rombel — {{ $class->name }}">
    <x-flash-message />

    {{-- Pre-load teachers data for Alpine.js --}}
    <script>
        window.ROMBEL_TEACHERS = @json($teachers);
        window.ROMBEL_SAVE_URL = "{{ route('admin.rombel.slot.save', $class) }}";
        window.CSRF_TOKEN = "{{ csrf_token() }}";
        window.ALLOW_TEAM_TEACHING = @json($class->allow_team_teaching);
    </script>

    <div class="max-w-full mx-auto space-y-8">

        {{-- Hero --}}
        <section class="page-hero">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.rombel.index') }}"
                   class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm border border-slate-100 hover:text-slate-600 transition-all">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <span class="app-eyebrow">Unified Schedule Management</span>
                    <h1>Detail Rombel — {{ $class->name }}</h1>
                </div>
            </div>
        </section>

        {{-- Class Info Card --}}
        <section class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-wrap items-center gap-6">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-teal-600 text-white text-3xl font-black shadow-lg">
                        {{ $class->name }}
                    </span>
                    <div>
                        <span class="inline-block rounded-full bg-teal-50 px-3 py-1 text-xs font-black uppercase tracking-wider text-teal-700">
                            {{ $class->unit?->name }}
                        </span>
                        @if($class->major)
                            <span class="ml-1 inline-block rounded-full bg-blue-50 px-3 py-1 text-xs font-black uppercase tracking-wider text-blue-700">{{ $class->major }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex gap-8 ml-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Wali Kelas</p>
                        <p class="font-bold text-slate-800">{{ $class->homeroomTeacher?->employee?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total JP/Minggu</p>
                        <p class="font-bold text-slate-800">{{ $stats['total_hours'] }} Jam Pelajaran</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Mapel</p>
                        <p class="font-bold text-slate-800">{{ $stats['subject_count'] }} Mata Pelajaran</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Guru</p>
                        <p class="font-bold text-slate-800">{{ $stats['teacher_count'] }} Guru</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Mode Slot</p>
                        <p class="font-bold text-slate-800">{{ $class->allow_team_teaching ? 'Team Teaching' : 'Single Teacher' }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Timetable --}}
        <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm overflow-hidden"
                 x-data="rombelTimetable()">

            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Grid Jadwal Mengajar</h2>
                    <p class="text-sm text-slate-500 mt-1">Klik sel untuk mengisi atau mengubah jadwal.</p>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.172-8.172z"/></svg>
                    {{ $class->allow_team_teaching ? 'Mode Team Teaching Aktif' : 'Mode Edit Aktif' }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-sm">
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
                                        $primarySession = $sessions->first();
                                    @endphp
                                    <td class="px-3 py-2 text-center">
                                        <button
                                            type="button"
                                            @click="openModal({{ $jpNum }}, '{{ $day }}', {{ $primarySession ? $primarySession->subject_id : 'null' }}, {{ $primarySession ? $primarySession->teacher_detail_id : 'null' }}, '{{ $primarySession?->subject?->name ?? '' }}', '{{ $primarySession?->teacherDetail?->employee?->name ?? '' }}')"
                                            class="w-full min-h-[60px] rounded-2xl border transition-all text-left px-3 py-2 group/cell
                                                {{ $sessions->isNotEmpty()
                                                    ? 'border-teal-200 bg-teal-50 hover:border-teal-400 hover:bg-teal-100'
                                                    : 'border-dashed border-slate-200 bg-slate-50 hover:border-teal-300 hover:bg-teal-50' }}">
                                            @if($sessions->isNotEmpty())
                                                <div class="space-y-2">
                                                    @foreach($sessions as $session)
                                                        <div class="{{ $sessions->count() > 1 && ! $loop->last ? 'border-b border-teal-100 pb-2' : '' }}">
                                                            <p class="text-[11px] font-black text-teal-800 leading-tight line-clamp-2">{{ $session->subject?->name }}</p>
                                                            <p class="text-[10px] text-teal-600 mt-1 leading-tight">{{ $session->teacherDetail?->employee?->name }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="flex items-center justify-center h-full gap-1 text-slate-300 group-hover/cell:text-teal-400 transition-colors">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    <span class="text-[10px] font-bold">Isi Jadwal</span>
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
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
                 @click.self="closeModal()"
                 style="display: none;">

                <div class="w-full max-w-md rounded-[2rem] bg-white shadow-2xl p-8"
                     @click.stop>

                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-black text-slate-900">Edit Slot Jadwal</h3>
                            <p class="text-sm text-slate-500 mt-0.5">
                                <span x-text="'JP ' + currentJp + ' — ' + currentDay"></span>
                            </p>
                        </div>
                        <button @click="closeModal()" class="h-9 w-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-slate-200 transition-all">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Subject Dropdown --}}
                    <div class="mb-5">
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">Mata Pelajaran</label>
                        <select x-model="selectedSubjectId"
                                @change="onSubjectChange()"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-teal-500 focus:ring-teal-500">
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Teacher Dropdown (filtered by subject) --}}
                    <div class="mb-6">
                        <label class="mb-2 block text-xs font-black uppercase tracking-widest text-slate-400">
                            Guru Pengajar
                            <span x-show="filteredTeachers.some(t => t.preferred)" class="ml-2 inline-block rounded-full bg-amber-100 px-2 py-0.5 text-[9px] text-amber-700">★ = Pengampu Mapel</span>
                        </label>
                        <select x-model="selectedTeacherId"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-bold focus:border-teal-500 focus:ring-teal-500">
                            <option value="">— Pilih Guru —</option>
                            <template x-for="teacher in filteredTeachers" :key="teacher.id">
                                <option :value="teacher.id" x-text="teacher.name"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Error message --}}
                    <p x-show="errorMsg" x-text="errorMsg" class="mb-4 rounded-xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-600"></p>

                    {{-- Actions --}}
                    <div class="flex gap-3">
                        <button @click="saveSlot()"
                                :disabled="loading || !selectedSubjectId || !selectedTeacherId"
                                class="flex-1 rounded-2xl bg-teal-600 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-teal-200 hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                            <span x-show="!loading">Simpan Slot</span>
                            <span x-show="loading">Menyimpan...</span>
                        </button>
                        <button @click="clearSlot()"
                                x-show="hasExisting"
                                :disabled="loading"
                                class="rounded-2xl border-2 border-rose-100 px-5 py-4 text-sm font-black uppercase tracking-widest text-rose-500 hover:bg-rose-50 transition-all disabled:opacity-50">
                            Hapus
                        </button>
                        <button @click="closeModal()"
                                class="rounded-2xl border-2 border-slate-100 px-5 py-4 text-sm font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 transition-all">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Alpine.js component --}}
    <script>
    function rombelTimetable() {
        return {
            modalOpen: false,
            currentJp: null,
            currentDay: null,
            selectedSubjectId: '',
            selectedTeacherId: '',
            hasExisting: false,
            loading: false,
            errorMsg: '',
            allTeachers: window.ROMBEL_TEACHERS || [],
            filteredTeachers: [],

            openModal(jp, day, subjectId, teacherDetailId, subjectName, teacherName) {
                this.currentJp = jp;
                this.currentDay = day;
                this.selectedSubjectId = subjectId ? String(subjectId) : '';
                this.selectedTeacherId = teacherDetailId ? String(teacherDetailId) : '';
                this.hasExisting = !!subjectId;
                this.errorMsg = '';
                this.filterTeachers();
                this.modalOpen = true;
            },

            closeModal() {
                this.modalOpen = false;
                this.loading = false;
                this.errorMsg = '';
            },

            onSubjectChange() {
                this.selectedTeacherId = '';
                this.filterTeachers();
            },

            filterTeachers() {
                if (!this.selectedSubjectId) {
                    // Show all teachers, no preference
                    this.filteredTeachers = this.allTeachers.map(t => ({ ...t, name: t.name }));
                    return;
                }
                const subjectId = parseInt(this.selectedSubjectId);
                // Preferred: teachers who have this subject in their competency
                const preferred = this.allTeachers
                    .filter(t => t.subject_ids && t.subject_ids.includes(subjectId))
                    .map(t => ({ ...t, name: t.name + ' ★', preferred: true }));
                // Others
                const others = this.allTeachers
                    .filter(t => !t.subject_ids || !t.subject_ids.includes(subjectId))
                    .map(t => ({ ...t, preferred: false }));
                this.filteredTeachers = [...preferred, ...others];
            },

            async saveSlot() {
                if (!this.selectedSubjectId || !this.selectedTeacherId) return;
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
                            subject_id: this.selectedSubjectId,
                            teacher_detail_id: this.selectedTeacherId,
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
                            subject_id: null,
                            teacher_detail_id: null,
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
