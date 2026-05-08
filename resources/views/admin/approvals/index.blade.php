<x-layouts.admin title="Approval Center">
    <x-flash-message />

    <div class="space-y-6" x-data="{ tab: 'permissions' }">
        <section class="page-hero">
            <span class="app-eyebrow">Request Center</span>
            <h1>Approval Center</h1>
            <p>Pusat persetujuan izin mengajar dan koreksi absensi. Harap teliti sebelum menyetujui pengajuan.</p>

            <div class="mt-8 flex gap-2">
                <button @click="tab = 'permissions'" 
                        :class="tab === 'permissions' ? 'btn-vibrant-primary scale-105' : 'bg-slate-100 text-slate-500'"
                        class="rounded-2xl px-8 py-3 text-sm font-bold transition-all duration-200">
                    Izin Jam Pelajaran ({{ $permissions->count() }})
                </button>
                <button @click="tab = 'corrections'" 
                        :class="tab === 'corrections' ? 'btn-vibrant-primary scale-105' : 'bg-slate-100 text-slate-500'"
                        class="rounded-2xl px-8 py-3 text-sm font-bold transition-all duration-200">
                    Koreksi Absen ({{ $corrections->count() }})
                </button>
            </div>
        </section>

        {{-- Tab Izin --}}
        <div x-show="tab === 'permissions'" class="space-y-4">
            @forelse($permissions as $perm)
                <div class="surface-card flex flex-col md:flex-row md:items-center justify-between gap-6 hover:border-accent/30 transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div class="data-avatar bg-indigo-50 text-indigo-600 border-indigo-100">
                            {{ substr($perm->employee->name, 0, 2) }}
                        </div>
                        <div class="data-stack">
                            <strong>{{ $perm->employee->name }}</strong>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $perm->employee->unit?->name }}</span>
                            <span class="mt-1.5 text-indigo-600 font-bold capitalize">{{ $perm->teacherSubjectUnit?->subject?->name ?? 'Mata Pelajaran' }}</span>
                            <span class="text-[9px] text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($perm->date)->format('d M Y') }} • Kelas {{ $perm->teacherSubjectUnit?->class?->name }}</span>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 rounded-2xl p-4 flex-1 border border-slate-100">
                        <p class="text-xs text-slate-500 leading-relaxed italic">"{{ $perm->reason }}"</p>
                    </div>
 
                    <div class="flex items-center gap-3">
                        <form action="{{ route('admin.approvals.permission.reject', $perm) }}" method="POST" class="flex-1 md:flex-none">
                            @csrf @method('PATCH')
                            <button type="submit" class="w-full btn-secondary text-rose-600 border-rose-100 hover:bg-rose-50 px-6">Tolak</button>
                        </form>
                        <form action="{{ route('admin.approvals.permission.approve', $perm) }}" method="POST" class="flex-1 md:flex-none">
                            @csrf @method('PATCH')
                            <button type="submit" class="w-full btn-primary px-8 shadow-primary/20">Setujui</button>
                        </form>
                    </div>
                </div>
            @empty
                <x-empty-state message="Tidak ada pengajuan izin jam pelajaran yang pending." />
            @endforelse
        </div>

        {{-- Tab Koreksi --}}
        <div x-show="tab === 'corrections'" class="space-y-4">
            @forelse($corrections as $corr)
                <div class="surface-card flex flex-col md:flex-row md:items-center justify-between gap-6 hover:border-accent/30 transition-all duration-300">
                    <div class="flex items-center gap-4">
                        <div class="data-avatar bg-teal-50 text-teal-600 border-teal-100">
                            {{ substr($corr->employee->name, 0, 2) }}
                        </div>
                        <div class="data-stack">
                            <strong>{{ $corr->employee->name }}</strong>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $corr->employee->unit?->name }}</span>
                            <span class="mt-1.5 text-teal-600 font-bold capitalize">Koreksi: {{ \Carbon\Carbon::parse($corr->date)->format('d M Y') }}</span>
                            <span class="text-[9px] text-slate-400 mt-0.5">Alasan: {{ $corr->reason }}</span>
                        </div>
                    </div>
 
                    <div class="flex items-center gap-3">
                        @if($corr->proof_path)
                            <a href="{{ asset('storage/' . $corr->proof_path) }}" target="_blank" class="text-xs font-black uppercase tracking-widest text-indigo-600 hover:underline px-4">Bukti</a>
                        @endif
                        <form action="{{ route('admin.approvals.correction.reject', $corr) }}" method="POST" class="flex-1 md:flex-none">
                            @csrf @method('PATCH')
                            <button type="submit" class="w-full btn-secondary text-rose-600 border-rose-100 hover:bg-rose-50 px-6">Tolak</button>
                        </form>
                        <form action="{{ route('admin.approvals.correction.approve', $corr) }}" method="POST" class="flex-1 md:flex-none">
                            @csrf @method('PATCH')
                            <button type="submit" class="w-full btn-primary px-8 shadow-primary/20">Setujui</button>
                        </form>
                    </div>
                </div>
            @empty
                <x-empty-state message="Tidak ada pengajuan koreksi absensi yang pending." />
            @endforelse
        </div>
    </div>
</x-layouts.admin>
