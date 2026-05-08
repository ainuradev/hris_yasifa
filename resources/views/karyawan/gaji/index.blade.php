<x-layouts.karyawan title="Slip Gaji">
    <x-flash-message />

    <div class="space-y-6">
        <div><h1 class="text-2xl font-bold text-slate-900">Slip Gaji</h1><p class="text-sm text-slate-500">Semua payroll final dan dibayar.</p></div>
        <div class="space-y-4">@forelse ($payrolls as $payroll) @php($payrollStart = \Illuminate\Support\Carbon::create($payroll->year, $payroll->month, 1)) @php($payrollEnd = $payroll->paid_at ? $payroll->paid_at->copy() : $payrollStart->copy()->endOfMonth()) <a href="{{ route('karyawan.gaji.show', $payroll) }}" class="block rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-lg font-semibold text-slate-900">Periode {{ $payrollStart->translatedFormat('d M Y') }} - {{ $payrollEnd->translatedFormat('d M Y') }}</p><p class="text-sm text-slate-500">{{ $payroll->paid_at?->format('d M Y') ? 'Dibayar ' . $payroll->paid_at->format('d M Y') : 'Belum dibayar' }}</p></div><div class="flex items-center gap-3"><div class="text-right"><p class="font-bold text-slate-900">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</p><p class="text-xs text-slate-500">Take home pay</p></div><x-pill :text="ucfirst($payroll->status->value ?? $payroll->status)" :color="($payroll->status->value ?? $payroll->status) === 'dibayar' ? 'green' : 'blue'" /></div></div></a>@empty<x-empty-state message="Belum ada data penggajian" />@endforelse</div>
        {{ $payrolls->links() }}
    </div>
</x-layouts.karyawan>
