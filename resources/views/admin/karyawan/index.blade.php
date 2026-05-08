<x-layouts.app>
    @slot('header')
        Data Karyawan
    @endslot
    <x-flash-message />

    <div class="space-y-6">

        {{-- PAGE HERO --}}
        <section class="page-hero">
            <div class="page-hero-grid lg:grid-cols-[1.3fr_0.7fr] lg:items-end">
                <div>
                    <span class="app-eyebrow">Employee Directory</span>
                    <h1>Kelola data induk, unit penempatan, dan status aktif pegawai.</h1>
                    <p>{{ auth()->user()->isAdminPusat() ? 'Satu pintu untuk mengatur data personal, jabatan, dan akses sistem seluruh staf operasional yayasan.' : 'Lihat data pegawai untuk unit Anda dengan filter yang lebih nyaman dipakai.' }}</p>
                </div>

                <div class="flex flex-wrap gap-3 lg:justify-end" x-data="{ exportOpen: false }">

                    {{-- Export Button --}}
                    <div class="relative">
                        <button @click="exportOpen = !exportOpen"
                                class="btn-secondary inline-flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Export Excel
                        </button>

                        {{-- Export Dropdown Panel --}}
                        <div x-show="exportOpen"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             @click.outside="exportOpen = false"
                             class="absolute right-0 z-30 mt-2 w-72 rounded-2xl border border-slate-200 bg-white p-5 shadow-xl"
                             style="display:none;">
                            <h4 class="mb-4 text-sm font-black uppercase tracking-widest text-slate-700">Filter Export</h4>
                            <form action="{{ route('admin.karyawan.export') }}" method="GET" class="space-y-3">

                                @if(auth()->user()->isAdminPusat())
                                <div>
                                    <label class="mb-1 block text-xs font-bold text-slate-500 uppercase tracking-wider">Unit</label>
                                    <select name="unit_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold focus:border-teal-500 focus:ring-teal-500">
                                        <option value="">Semua Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <div>
                                    <label class="mb-1 block text-xs font-bold text-slate-500 uppercase tracking-wider">Tipe</label>
                                    <select name="type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold focus:border-teal-500 focus:ring-teal-500">
                                        <option value="">Semua Tipe</option>
                                        <option value="guru">Guru</option>
                                        <option value="non_guru">Non Guru</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1 block text-xs font-bold text-slate-500 uppercase tracking-wider">Status</label>
                                    <select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold focus:border-teal-500 focus:ring-teal-500">
                                        <option value="">Semua Status</option>
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Non-aktif</option>
                                    </select>
                                </div>

                                <button type="submit"
                                        class="w-full rounded-xl bg-teal-600 py-3 text-sm font-black uppercase tracking-widest text-white shadow-md hover:bg-teal-700 transition-all inline-flex items-center justify-center gap-2">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Download .xlsx
                                </button>
                            </form>
                        </div>
                    </div>

                    <a href="{{ route('admin.karyawan.import') }}" class="btn-secondary inline-flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Import
                    </a>
                    <a href="{{ route('admin.karyawan.create') }}" class="btn-primary inline-flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Karyawan
                    </a>
                </div>
            </div>
        </section>

        {{-- EMPLOYEE LIST (Livewire) --}}
        <livewire:admin.employee-list />

    </div>
</x-layouts.app>