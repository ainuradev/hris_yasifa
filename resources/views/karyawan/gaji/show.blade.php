<x-layouts.app title="Slip Gaji">
    <x-flash-message />
    @include('payroll._print-styles')

    @php
        $status = $payroll->status->value ?? $payroll->status;
        $type = $payroll->employee?->type->value ?? $payroll->employee?->type;
        $scheme = $type === 'guru' ? 'Per jam mengajar' : 'Bulanan';
        $allowances = $payroll->payrollDetails->where('category', 'tunjangan');
        $deductions = $payroll->payrollDetails->where('category', 'potongan');
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">
        
        {{-- Toolbar Actions --}}
        <div class="flex items-center justify-between no-print mb-6">
            <a href="{{ route('karyawan.gaji.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Daftar Slip
            </a>
            <button onclick="window.print()" class="btn-primary inline-flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak / Download Slip
            </button>
        </div>

        {{-- Slip Gaji Paper Form --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-10 print:p-0 print:border-none print:shadow-none" id="slip-gaji">
            
            {{-- Header --}}
            <div class="border-b-2 border-slate-900 pb-6 mb-6 flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight">Slip Gaji Karyawan</h1>
                    <p class="text-slate-500 font-medium mt-1">HRIS Sirojul Falah</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Periode</p>
                    <p class="text-xl font-bold text-slate-800">{{ \Carbon\Carbon::createFromDate($payroll->year, $payroll->month)->translatedFormat('F Y') }}</p>
                    <p class="text-sm font-semibold text-slate-500 mt-1">Status: <span class="uppercase tracking-wider {{ $status === 'dibayar' ? 'text-green-600' : 'text-amber-600' }}">{{ $status }}</span></p>
                </div>
            </div>

            {{-- Employee Info --}}
            <div class="grid grid-cols-2 gap-8 mb-8 bg-slate-50 rounded-2xl p-6 print-grid-2 print:bg-transparent print:p-0 print:gap-4 print-section">
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Nama Lengkap</p>
                        <p class="text-base font-bold text-slate-900">{{ $payroll->employee?->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Nomor Identitas (NIK/NUPTK)</p>
                        <p class="text-base font-bold text-slate-900">{{ $payroll->employee?->identifier_number ?? '-' }}</p>
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Unit Kerja</p>
                        <p class="text-base font-bold text-slate-900">{{ $payroll->employee?->unit?->name ?? 'Pusat' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Posisi / Jabatan</p>
                        <p class="text-base font-bold text-slate-900">{{ ucfirst(str_replace('_', ' ', $payroll->employee?->type->value ?? $payroll->employee?->type)) }}</p>
                    </div>
                </div>
            </div>

            {{-- Salary Details --}}
            <div class="grid md:grid-cols-2 gap-8 print-grid-2 print-section">
                
                {{-- Pendapatan --}}
                <div>
                    <h3 class="text-sm font-black uppercase tracking-widest text-green-700 mb-4 border-b border-green-100 pb-2">Pendapatan</h3>
                    <div class="space-y-3">
                        @forelse($allowances as $item)
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-medium text-slate-600">{{ $item->description }}</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                        </div>
                        @empty
                        <p class="text-sm text-slate-400 italic">Tidak ada pendapatan.</p>
                        @endforelse
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-sm font-bold text-slate-900">Total Pendapatan</span>
                        <span class="text-sm font-black text-green-600">Rp {{ number_format($allowances->sum('amount'), 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Potongan --}}
                <div class="print:mt-8">
                    <h3 class="text-sm font-black uppercase tracking-widest text-rose-700 mb-4 border-b border-rose-100 pb-2">Potongan</h3>
                    <div class="space-y-3">
                        @forelse($deductions as $item)
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-medium text-slate-600">{{ $item->description }}</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                        </div>
                        @empty
                        <p class="text-sm text-slate-400 italic">Tidak ada potongan.</p>
                        @endforelse
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-sm font-bold text-slate-900">Total Potongan</span>
                        <span class="text-sm font-black text-rose-600">Rp {{ number_format($deductions->sum('amount'), 0, ',', '.') }}</span>
                    </div>
                </div>

            </div>

            {{-- Take Home Pay --}}
            <div class="mt-10 bg-[#1a2744] rounded-2xl p-6 text-white flex flex-col md:flex-row justify-between items-center print:bg-transparent print:text-slate-900 print:border-2 print:border-slate-900 print:rounded-none print-section">
                <div>
                    <h2 class="text-lg font-bold uppercase tracking-widest text-slate-300 print:text-slate-900">Penerimaan Bersih</h2>
                    <p class="text-sm text-slate-400 mt-1 print:text-slate-600">Take Home Pay</p>
                </div>
                <div class="text-3xl font-black tracking-tight mt-4 md:mt-0">
                    Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                </div>
            </div>

            {{-- Footer Signatures --}}
            <div class="mt-16 pt-8 grid grid-cols-2 gap-8 text-center print-grid-2 print:mt-24 print-section">
                <div>
                    <p class="text-sm text-slate-500 mb-20">Penerima</p>
                    <p class="text-sm font-bold text-slate-900 underline underline-offset-4">{{ $payroll->employee?->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-20">Mengetahui,</p>
                    <p class="text-sm font-bold text-slate-900 underline underline-offset-4">HR/Finance</p>
                </div>
            </div>

        </div>

    </div>
</x-layouts.app>
