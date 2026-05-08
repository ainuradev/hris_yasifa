<x-layouts.karyawan title="Pengajuan Cuti">
    <x-flash-message />

    <div class="space-y-6" x-data="{ leaveType: '{{ old('leave_type', 'tahunan') }}', startDate: '{{ old('start_date') }}', endDate: '{{ old('end_date') }}', totalDays: 0 }" x-init="$watch('startDate', value => { if(startDate && endDate){ totalDays = Math.floor((new Date(endDate) - new Date(startDate)) / 86400000) + 1 } }); $watch('endDate', value => { if(startDate && endDate){ totalDays = Math.floor((new Date(endDate) - new Date(startDate)) / 86400000) + 1 } })">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

            {{-- Card: Jatah Tahunan --}}
            <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg"
                 style="background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-6 -right-6 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-blue-100">Jatah Tahunan</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">12</p>
                    <p class="mt-1 text-xs text-blue-200">Hari cuti per tahun</p>
                </div>
            </div>

            {{-- Card: Sudah Dipakai --}}
            <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg"
                 style="background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-6 -right-6 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-amber-100">Sudah Dipakai</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $usedAnnualLeave ?? 0 }}</p>
                    <p class="mt-1 text-xs text-amber-200">Cuti tahunan terpakai</p>
                </div>
            </div>

            {{-- Card: Sisa --}}
            <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg"
                 style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-6 -right-6 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-emerald-100">Sisa Cuti</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $remainingAnnualLeave }}</p>
                    <p class="mt-1 text-xs text-emerald-200">Hari tersisa tahun ini</p>
                </div>
            </div>

        </div>

        <form method="POST" action="{{ route('karyawan.cuti.store') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-5 md:grid-cols-2">
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Jenis cuti</label><select name="leave_type" x-model="leaveType" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"><option value="tahunan">Tahunan</option><option value="sakit">Sakit</option><option value="penting">Penting</option><option value="melahirkan">Melahirkan</option></select></div>
                <div x-show="leaveType === 'tahunan'" class="rounded-2xl bg-blue-50 p-4 text-sm text-blue-700">Sisa jatah cuti tahunan Anda: {{ $remainingAnnualLeave }} hari.</div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Tanggal mulai</label><input type="date" name="start_date" x-model="startDate" value="{{ old('start_date') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"></div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Tanggal selesai</label><input type="date" name="end_date" x-model="endDate" value="{{ old('end_date') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"></div>
                <div class="md:col-span-2 rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">Durasi pengajuan: <span class="font-semibold text-slate-900" x-text="totalDays > 0 ? totalDays + ' hari' : '-' "></span></div>
                <div class="md:col-span-2"><label class="mb-2 block text-sm font-medium text-slate-700">Alasan</label><textarea name="reason" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">{{ old('reason') }}</textarea></div>
            </div>
            <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">Cuti tahunan mengurangi jatah. Cuti sakit dan penting tidak mengurangi jatah tahunan.</div>
            <div class="mt-6"><button class="rounded-2xl bg-[#1a2744] px-5 py-3 text-sm font-semibold text-white">Kirim</button></div>
        </form>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100 text-sm"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left font-semibold text-slate-500">Jenis</th><th class="px-4 py-3 text-left font-semibold text-slate-500">Tanggal</th><th class="px-4 py-3 text-left font-semibold text-slate-500">Durasi</th><th class="px-4 py-3 text-left font-semibold text-slate-500">Alasan</th><th class="px-4 py-3 text-left font-semibold text-slate-500">Status</th></tr></thead><tbody class="divide-y divide-slate-100 bg-white">@forelse ($leaveRequests as $leave)
                @php
                    $leaveStatusVal = $leave->status->value ?? $leave->status;
                    $leaveBadgeClass = match($leaveStatusVal) {
                        'disetujui', 'approved' => 'bg-emerald-500/15 text-emerald-400',
                        'ditolak', 'rejected' => 'bg-slate-500/15 text-slate-400',
                        default => 'bg-amber-500/15 text-amber-400',
                    };
                @endphp
                <tr><td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $leave->leave_type->value ?? $leave->leave_type) }}</td><td class="px-4 py-3">{{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}</td><td class="px-4 py-3">{{ $leave->total_days }} hari</td><td class="px-4 py-3">{{ $leave->reason }}</td><td class="px-4 py-3"><div class="flex items-center gap-2"><span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize {{ $leaveBadgeClass }}">{{ ucfirst($leaveStatusVal) }}</span>@if($leaveStatusVal === 'pending')<span class="text-xs text-slate-500">Menunggu persetujuan admin</span>@endif</div></td></tr>@empty<tr><td colspan="5" class="px-4 py-8"><x-empty-state message="Belum ada riwayat pengajuan cuti." /></td></tr>@endforelse</tbody></table>
        </div>

        {{ $leaveRequests->links() }}
    </div>
</x-layouts.karyawan>
