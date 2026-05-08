<x-layouts.admin title="Generate Payroll">
    <x-flash-message />

    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Generate Payroll</h1>
            <p class="text-sm text-slate-500">Buat draft slip gaji otomatis berdasarkan unit dan periode.</p>
        </div>

        <div class="rounded-3xl border border-blue-200 bg-blue-50 p-5 text-sm text-blue-800">
            Proses generate akan mengecek slip existing, menghitung honor guru berdasarkan total jam mengajar pada bulan terpilih,
            menghitung gaji non-guru secara bulanan sesuai rate yang disepakati, lalu menambahkan potongan alpa dari data absensi.
        </div>

        <form method="POST" action="{{ route('admin.penggajian.generate.store') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-5 md:grid-cols-3">
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Bulan</label><select name="month" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@for($i=1;$i<=12;$i++)<option value="{{ $i }}" @selected(old('month', now()->month) == $i)>{{ sprintf('%02d', $i) }}</option>@endfor</select></div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Tahun</label><select name="year" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">@for($i=2024;$i<=2028;$i++)<option value="{{ $i }}" @selected(old('year', now()->year) == $i)>{{ $i }}</option>@endfor</select></div>
                <div><label class="mb-2 block text-sm font-medium text-slate-700">Unit</label><select name="unit_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" @disabled(! auth()->user()->isAdminPusat())>@foreach ($units as $unit)<option value="{{ $unit->id }}" @selected((string) old('unit_id', $selectedUnitId) === (string) $unit->id)>{{ $unit->name }}</option>@endforeach</select>@if (! auth()->user()->isAdminPusat())<input type="hidden" name="unit_id" value="{{ $selectedUnitId }}">@endif</div>
            </div>
            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                {{ auth()->user()->isAdminPusat() ? 'Payroll akan dibuat hanya untuk unit yang dipilih di atas.' : 'Payroll hanya akan dibuat untuk unit Anda.' }}
            </div>
            <div class="mt-6 flex gap-3"><button class="rounded-2xl bg-[#1a2744] px-5 py-3 text-sm font-semibold text-white">Generate</button><a href="{{ route('admin.penggajian.index') }}" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700">Kembali</a></div>
        </form>
    </div>
</x-layouts.admin>
