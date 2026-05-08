<x-layouts.admin title="Pengajuan Karyawan">
    <x-flash-message />

    <div class="space-y-6">
        <section class="page-hero">
            <div class="page-hero-grid lg:grid-cols-[1.3fr_0.7fr] lg:items-end">
                <div>
                    <span class="app-eyebrow">Employee Requests</span>
                    <h1>{{ auth()->user()->isAdminPusat() ? 'Tinjau dan setujui pengajuan dari masing-masing unit.' : 'Pantau status pengajuan karyawan dari unit Anda.' }}</h1>
                    <p>Setiap pengajuan dilampiri surat izin dan catatan. Admin pusat dapat menyetujui atau menolak langsung dari tabel ini.</p>
                </div>
                @if (auth()->user()->isAdminUnit())
                    <div class="flex flex-wrap gap-3 lg:justify-end">
                        <a href="{{ route('admin.employee-requests.create') }}" class="btn-primary">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Ajukan karyawan
                        </a>
                    </div>
                @endif
            </div>
        </section>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Nama</th>
                        <th class="px-4 py-3 text-left font-semibold">Unit</th>
                        <th class="px-4 py-3 text-left font-semibold">Tipe</th>
                        <th class="px-4 py-3 text-left font-semibold">Jabatan</th>
                        <th class="px-4 py-3 text-left font-semibold">Pemohon</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-left font-semibold">Lampiran</th>
                        <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($employeeRequests as $employeeRequest)
                        @php
                            $typeValue = $employeeRequest->type->value ?? $employeeRequest->type;
                            $statusValue = $employeeRequest->status->value ?? $employeeRequest->status;
                            $pillColor = match ($statusValue) {
                                'approved' => 'green',
                                'rejected' => 'red',
                                default => 'amber',
                            };
                        @endphp
                        <tr>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-800">{{ $employeeRequest->name }}</p>
                                <p class="text-xs text-slate-500">{{ $employeeRequest->email }}</p>
                            </td>
                            <td class="px-4 py-4 text-slate-600">{{ $employeeRequest->unit?->jenjang }}</td>
                            <td class="px-4 py-4"><x-pill :text="str($typeValue)->replace('_', ' ')->title()" :color="$typeValue === 'guru' ? 'blue' : 'teal'" /></td>
                            <td class="px-4 py-4 text-slate-600">{{ $employeeRequest->jabatan }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $employeeRequest->requester?->name }}</td>
                            <td class="px-4 py-4">
                                @php
                                    $badgeClass = match($statusValue) {
                                        'approved' => 'bg-emerald-500/15 text-emerald-400',
                                        'rejected' => 'bg-slate-500/15 text-slate-400',
                                        default => 'bg-amber-500/15 text-amber-400',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize {{ $badgeClass }}">
                                    {{ ucfirst($statusValue) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <a href="{{ asset('storage/'.$employeeRequest->approval_document_path) }}" target="_blank" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold">Lihat Surat</a>
                            </td>
                            <td class="px-4 py-4">
                                @if (auth()->user()->isAdminPusat() && $statusValue === 'pending')
                                    <div class="flex flex-col gap-2">
                                        <form method="POST" action="{{ route('admin.employee-requests.approve', $employeeRequest) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="w-full rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.employee-requests.reject', $employeeRequest) }}" class="space-y-2">
                                            @csrf
                                            @method('PATCH')
                                            <textarea name="rejection_reason" rows="2" placeholder="Alasan penolakan" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs"></textarea>
                                            <button type="submit" class="w-full rounded-xl bg-rose-600 px-3 py-2 text-xs font-semibold text-white">Reject</button>
                                        </form>
                                    </div>
                                @else
                                    <div class="space-y-1 text-xs text-slate-500">
                                        <p>{{ $employeeRequest->approver?->name ? 'Diproses oleh '.$employeeRequest->approver->name : 'Menunggu review' }}</p>
                                        @if ($employeeRequest->rejection_reason)
                                            <p>Alasan: {{ $employeeRequest->rejection_reason }}</p>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8"><x-empty-state message="Belum ada pengajuan karyawan." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $employeeRequests->links() }}
    </div>
</x-layouts.admin>
