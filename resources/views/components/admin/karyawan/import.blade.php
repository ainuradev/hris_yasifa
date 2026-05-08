<div>
    @slot('header')
        Smart Import Karyawan
    @endslot

    <x-flash-message />

    <div class="mx-auto max-w-5xl space-y-6">
        
        {{-- Progress Bar --}}
        <div class="mb-8">
            <div class="flex items-center justify-between relative">
                <div class="absolute left-0 top-1/2 -z-10 h-0.5 w-full -translate-y-1/2 bg-slate-200"></div>
                <div class="absolute left-0 top-1/2 -z-10 h-0.5 -translate-y-1/2 bg-indigo-600 transition-all duration-500" style="width: {{ ($step - 1) * 50 }}%"></div>
                
                <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 {{ $step >= 1 ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300 bg-white text-slate-500' }} font-bold">1</div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 {{ $step >= 2 ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300 bg-white text-slate-500' }} font-bold">2</div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 {{ $step >= 3 ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300 bg-white text-slate-500' }} font-bold">3</div>
            </div>
            <div class="mt-2 flex justify-between text-xs font-medium text-slate-500">
                <span>Upload File</span>
                <span>Mapping Kolom</span>
                <span>Hasil Import</span>
            </div>
        </div>

        {{-- STEP 1: UPLOAD --}}
        @if ($step === 1)
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-slate-800">Langkah 1: Upload Data Excel</h2>
                    <p class="mt-1 text-sm text-slate-500">Unggah file Excel (.xlsx) atau CSV yang berisi data karyawan. Baris pertama harus berupa nama kolom (Header).</p>
                </div>

                @if(!auth()->user()->isAdminUnit())
                    <div class="mb-6 rounded-xl bg-slate-50 p-5 border border-slate-200">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Unit Penempatan (Opsional)</label>
                        <select wire:model="defaultUnitId" class="block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Otomatis dari Excel (Jika Ada) --</option>
                            @foreach($availableUnits as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500">Jika Anda memilih Unit di sini, semua karyawan dalam file Excel akan otomatis dimasukkan ke Unit ini (mengabaikan kolom Unit di Excel).</p>
                    </div>
                @else
                    <div class="mb-6 rounded-xl bg-slate-50 p-5 border border-slate-200">
                        <p class="text-sm font-medium text-slate-700">Unit Penempatan: <span class="font-bold text-indigo-700">{{ auth()->user()->unit->name }}</span></p>
                        <p class="mt-1 text-xs text-slate-500">Karena Anda adalah Admin Unit, seluruh data yang diimport akan otomatis masuk ke unit Anda.</p>
                    </div>
                @endif

                <form wire:submit="processUpload">
                    <div class="mt-2 flex justify-center rounded-xl border border-dashed border-slate-300 px-6 py-10">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                            </svg>
                            <div class="mt-4 flex text-sm leading-6 text-slate-600 justify-center">
                                <label for="file-upload" class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-600 focus-within:ring-offset-2 hover:text-indigo-500">
                                    <span>Pilih file Excel</span>
                                    <input id="file-upload" wire:model="file" type="file" class="sr-only" accept=".xlsx,.xls,.csv">
                                </label>
                            </div>
                            <p class="text-xs leading-5 text-slate-500 mt-1">XLSX, XLS, CSV up to 10MB</p>
                        </div>
                    </div>
                    @error('file') <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span> @enderror

                    @if ($file)
                        <div class="mt-4 flex items-center justify-between rounded-lg bg-slate-50 p-4 border border-slate-200">
                            <div class="flex items-center">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-sm font-medium text-slate-700">{{ $file->getClientOriginalName() }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" {{ !$file ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="processUpload">Lanjut ke Mapping</span>
                            <span wire:loading wire:target="processUpload">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- STEP 2: MAPPING --}}
        @if ($step === 2)
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Langkah 2: Cocokkan Kolom (Mapping)</h2>
                        <p class="mt-1 text-sm text-slate-500">Pilih kolom Excel yang sesuai dengan kolom Database. Kolom dengan tanda (*) wajib diisi.</p>
                    </div>
                    <button type="button" wire:click="$set('step', 1)" class="text-sm font-medium text-slate-500 hover:text-slate-800">← Kembali</button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-800 w-1/3">Kolom Database Sistem</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-800 w-2/3">Diambil Dari Kolom Excel</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach($dbFields as $key => $label)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-slate-700">
                                        {{ $label }}
                                        @if(in_array($key, ['name', 'nik', 'email']))
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <select wire:model="mapping.{{ $key }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">-- Abaikan (Kosongkan) --</option>
                                            @foreach($headers as $index => $header)
                                                <option value="{{ $index }}">{{ $header }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(count($previewData) > 0)
                    <div class="mt-8">
                        <h3 class="text-sm font-bold text-slate-700 mb-3">Preview Data (3 Baris Pertama):</h3>
                        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <table class="min-w-full text-xs">
                                <thead>
                                    <tr>
                                        @foreach($headers as $header)
                                            <th class="px-2 py-1 text-left font-semibold text-slate-500 whitespace-nowrap">{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($previewData as $row)
                                        <tr>
                                            @foreach($headers as $index => $header)
                                                <td class="px-2 py-1 text-slate-600 whitespace-nowrap">{{ Str::limit($row[$index] ?? '-', 30) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="mt-8 flex justify-end">
                    <button type="button" wire:click="executeImport" class="btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="executeImport">Mulai Proses Import</span>
                        <span wire:loading wire:target="executeImport">Sedang Mengimpor...</span>
                    </button>
                </div>
            </div>
        @endif

        {{-- STEP 3: RESULT --}}
        @if ($step === 3)
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 mb-4">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-800">Proses Import Selesai!</h2>
                <p class="mt-2 text-slate-500">Berikut adalah ringkasan hasil import data karyawan Anda.</p>

                <div class="mt-8 grid grid-cols-2 gap-4 max-w-md mx-auto">
                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-200">
                        <p class="text-sm font-medium text-slate-500">Berhasil Disimpan</p>
                        <p class="mt-1 text-3xl font-bold text-green-600">{{ $importResults['success'] }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-200">
                        <p class="text-sm font-medium text-slate-500">Gagal / Ditolak</p>
                        <p class="mt-1 text-3xl font-bold text-red-600">{{ $importResults['failed'] }}</p>
                    </div>
                </div>

                @if(count($importResults['errors']) > 0)
                    <div class="mt-8 text-left max-w-2xl mx-auto rounded-xl border border-red-200 bg-red-50 p-4">
                        <h3 class="text-sm font-bold text-red-800 flex items-center mb-2">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Log Error (Kenapa ada yang gagal?)
                        </h3>
                        <ul class="text-xs text-red-700 list-disc pl-5 space-y-1 max-h-40 overflow-y-auto">
                            @foreach($importResults['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-10">
                    <a href="{{ route('admin.karyawan.index') }}" class="btn-primary">Kembali ke Data Karyawan</a>
                </div>
            </div>
        @endif

    </div>
</div>
