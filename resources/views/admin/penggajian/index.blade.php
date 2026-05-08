<x-layouts.admin title="Penggajian">
    <x-flash-message />

    <div class="space-y-6">
        <section class="page-hero">
            <div class="page-hero-grid lg:grid-cols-[1.3fr_0.7fr] lg:items-end">
                <div>
                    <span class="app-eyebrow">Payroll Stream</span>
                    <h1>Monitoring slip gaji dengan tampilan yang lebih bersih dan cepat diproses.</h1>
                    <p>{{ auth()->user()->isAdminPusat() ? 'Pantau payroll seluruh unit, finalisasi, dan tandai pembayaran dari satu command surface.' : 'Pantau payroll untuk unit Anda dengan filter yang lebih nyaman.' }}</p>
                </div>
                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <a href="{{ route('admin.penggajian.generate.form') }}" class="btn-primary">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Generate payroll
                    </a>
                </div>
            </div>
        </section>

        <section class="surface-form">
            <h2>Filter Payroll</h2>
            <p class="section-note">Tampilkan slip berdasarkan bulan, tahun, unit, dan status payroll.</p>

            <form method="GET" class="mt-6 grid gap-4 md:grid-cols-4">
                <div>
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-select" @change="$el.closest('form').submit()">
                        <option value="">Semua</option>
                        @for($i=1;$i<=12;$i++)
                            <option value="{{ $i }}" @selected((string) request('month') === (string) $i)>{{ sprintf('%02d', $i) }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select" @change="$el.closest('form').submit()">
                        <option value="">Semua</option>
                        @for($i=2024;$i<=2028;$i++)
                            <option value="{{ $i }}" @selected((string) request('year') === (string) $i)>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="form-label">Unit</label>
                    <select name="unit_id" class="form-select" @change="$el.closest('form').submit()" @disabled(! auth()->user()->isAdminPusat())>
                        @if (auth()->user()->isAdminPusat())
                            <option value="">Semua unit</option>
                        @endif
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) ($selectedUnitId ?? request('unit_id')) === (string) $unit->id)>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    @if (! auth()->user()->isAdminPusat())
                        <input type="hidden" name="unit_id" value="{{ $selectedUnitId }}">
                    @endif
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" @change="$el.closest('form').submit()">
                        <option value="">Semua status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="final" @selected(request('status') === 'final')>Final</option>
                        <option value="dibayar" @selected(request('status') === 'dibayar')>Dibayar</option>
                    </select>
                </div>
            </form>
        </section>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Card: Total Slip --}}
            <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg"
                 style="background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-6 -right-6 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-blue-100">Total Slip</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $stats['total_slip'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-blue-200">Hasil filter aktif</p>
                </div>
            </div>

            {{-- Card: Draft --}}
            <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg"
                 style="background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-6 -right-6 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-amber-100">Draft</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $stats['draft'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-amber-200">Belum final</p>
                </div>
            </div>

            {{-- Card: Final --}}
            <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg"
                 style="background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-6 -right-6 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-blue-100">Final</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $stats['final'] ?? 0 }}</p>
                    <p class="mt-1 text-xs text-blue-200">Siap dibayar</p>
                </div>
            </div>

            {{-- Card: Dibayar --}}
            <div class="relative overflow-hidden rounded-2xl p-5 shadow-lg"
                 style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-6 -right-6 h-32 w-32 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-emerald-100">Dibayar</span>
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ ($stats['dibayar'] ?? 0) }} slip</p>
                    <p class="mt-1 text-xs text-emerald-200">Rp {{ number_format($stats['total_rp'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>

        </div>

        <div class="table-container">
            <div class="overflow-x-auto">
                <table class="table-auto-full">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-4">Nama</th>
                            <th class="px-6 py-4">Unit</th>
                            <th class="px-6 py-4">Tipe</th>
                            <th class="px-6 py-4">Skema</th>
                            <th class="px-6 py-4">Gaji Dasar</th>
                            <th class="px-6 py-4">Tunjangan</th>
                            <th class="px-6 py-4">Potongan</th>
                            <th class="px-6 py-4">Take Home</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payrolls as $payroll)
                            @php
                                $employee = $payroll->employee;
                                $initials = strtoupper(substr($employee->name, 0, 1)) . strtoupper(substr(explode(' ', $employee->name)[1] ?? $employee->name, 0, 1));
                                $status = $payroll->status->value ?? $payroll->status;
                                $type = $employee->type->value ?? $employee->type;
                                $scheme = $type === 'guru' ? 'Per jam mengajar' : 'Bulanan';
                            @endphp
                            <tr class="table-row">
                                <td class="table-cell">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold shrink-0">{{ $initials }}</div>
                                        <div>
                                            <div class="font-medium text-slate-900">{{ $employee->name }}</div>
                                            <div class="text-xs text-slate-500">{{ sprintf('%02d', $payroll->month) }}/{{ $payroll->year }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-cell text-slate-600">{{ $employee->unit?->jenjang }}</td>
                                <td class="table-cell"><span class="badge {{ $type === 'guru' ? 'badge-info' : 'badge-teal' }}">{{ str($type)->replace('_',' ')->title() }}</span></td>
                                <td class="table-cell text-slate-600">{{ $scheme }}</td>
                                <td class="table-cell text-slate-600">Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}</td>
                                <td class="table-cell text-emerald-600">+Rp {{ number_format($payroll->total_allowance, 0, ',', '.') }}</td>
                                <td class="table-cell text-red-600">-Rp {{ number_format($payroll->total_deduction, 0, ',', '.') }}</td>
                                <td class="table-cell font-bold text-slate-900">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</td>
                                <td class="table-cell"><span class="badge {{ $status === 'dibayar' ? 'badge-success' : ($status === 'final' ? 'badge-info' : 'badge-warning') }}">{{ ucfirst($status) }}</span></td>
                                <td class="table-cell">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('admin.penggajian.show', $payroll) }}" class="btn-secondary px-3 py-1.5 text-xs">Detail</a>
                                        @if ($status === 'draft')
                                            <form method="POST" action="{{ route('admin.penggajian.finalize', $payroll) }}" class="inline-block">@csrf @method('PATCH')<button class="btn-warning px-3 py-1.5 text-xs">Final</button></form>
                                        @elseif ($status === 'final')
                                            <form method="POST" action="{{ route('admin.penggajian.markPaid', $payroll) }}" class="inline-block">@csrf @method('PATCH')<button class="btn-success px-3 py-1.5 text-xs">Bayar</button></form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="px-6 py-10 text-center"><x-empty-state message="Belum ada data payroll." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $payrolls->links() }}
    </div>
</x-layouts.admin>
