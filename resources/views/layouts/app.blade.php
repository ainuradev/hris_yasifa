    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'HRIS Sirojul Falah') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-100 text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

<div class="flex h-screen overflow-hidden">

    {{-- ── SIDEBAR ─────────────────────────────────────────────── --}}

    {{-- Mobile backdrop --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
    ></div>

    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col
               bg-teal-800 text-teal-50 shadow-2xl shadow-teal-900/30
               transition-transform duration-300 ease-in-out
               lg:static lg:translate-x-0 lg:z-auto lg:shadow-none"
    >
        {{-- Logo --}}
        <div class="flex shrink-0 items-center gap-3 border-b border-white/10 px-5 py-[1.15rem]">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                        bg-white/15 text-xs font-bold text-white ring-1 ring-white/20">
                SF
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-white leading-tight tracking-tight">Sirojul Falah</p>
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-teal-300 mt-0.5">HRIS System</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="sidebar-scroll flex-1 overflow-y-auto px-3 py-4">
            @php
                $isAdmin    = auth()->user() && in_array(auth()->user()->role->value, ['admin_pusat','admin_unit']);
                $isKaryawan = auth()->user() && auth()->user()->role->value === 'karyawan';
            @endphp

            @if($isAdmin)
                {{-- GROUP: UTAMA --}}
                <div class="mb-6">
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-teal-400/80">Utama</p>
                    <div class="space-y-0.5">
                        <x-sidebar-link label="Dashboard" route="admin.dashboard" match="admin.dashboard" icon="grid" />
                        <x-sidebar-link label="Pengumuman" route="admin.pengumuman.index" match="admin.pengumuman.*" icon="bell" />
                    </div>
                </div>

                <div class="my-4 border-t border-white/5 mx-3"></div>

                {{-- GROUP: KEPEGAWAIAN --}}
                <div class="mb-6">
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-teal-400/80">Kepegawaian</p>
                    <div class="space-y-0.5">
                        <x-sidebar-link label="Data Karyawan" route="admin.karyawan.index" match="admin.karyawan.*" icon="users" />

                        <x-sidebar-link label="Absensi & Izin" route="admin.absensi.index" match="admin.absensi.*" icon="cal-check" />
                        <x-sidebar-link label="Approval Center" route="admin.approvals.index" match="admin.approvals.*" icon="check" />
                    </div>
                </div>

                <div class="my-4 border-t border-white/5 mx-3"></div>

                {{-- GROUP: AKADEMIK --}}
                <div class="mb-6">
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-teal-400/80">Akademik</p>
                    <div class="space-y-0.5">
                        <x-sidebar-link label="Data Rombel" route="admin.rombel.index" match="admin.rombel.*" icon="cal" />
                        <x-sidebar-link label="Mata Pelajaran" route="admin.subjects.index" match="admin.subjects.*" icon="book" />
                    </div>
                </div>

                <div class="my-4 border-t border-white/5 mx-3"></div>

                {{-- GROUP: KEUANGAN (DROPDOWN) --}}
                <div class="mb-6" x-data="{ open: {{ request()->routeIs('admin.penggajian.*', 'admin.salary-*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                            class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-teal-100 transition-all hover:bg-white/10 hover:text-white">
                        <span class="text-teal-300 group-hover:text-teal-200">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="6" width="20" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 14a1 1 0 110-2 1 1 0 010 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 10h20M6 6V4a1 1 0 011-1h10a1 1 0 011 1v2"/></svg>
                        </span>
                        <span class="flex-1 text-left font-medium">Keuangan</span>
                        <svg class="h-3.5 w-3.5 text-teal-400 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    
                    <div x-show="open" x-collapse class="mt-1 space-y-0.5 pl-9">
                        <a href="{{ route('admin.penggajian.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.penggajian.*') ? 'text-white font-bold' : 'text-teal-200/70 hover:text-white' }}">Proses Payroll</a>
                        <a href="{{ route('admin.salary-components.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.salary-components.*') ? 'text-white font-bold' : 'text-teal-200/70 hover:text-white' }}">Komponen Gaji</a>
                        <a href="{{ route('admin.salary-rates.index') }}" class="block py-2 text-sm {{ request()->routeIs('admin.salary-rates.*') ? 'text-white font-bold' : 'text-teal-200/70 hover:text-white' }}">Rate Gaji Master</a>
                    </div>
                </div>

                <div class="my-4 border-t border-white/5 mx-3"></div>

                {{-- GROUP: PENGATURAN SYSTEM --}}
                <div class="mb-6">
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-teal-400/80">Konfigurasi</p>
                    <div class="space-y-0.5">
                        <x-sidebar-link label="Kalender Libur" route="admin.holidays.index" match="admin.holidays.*" icon="cal" />
                        <x-sidebar-link label="Audit Log" route="admin.audit-logs.index" match="admin.audit-logs.*" icon="bell" />
                    </div>
                </div>
            @endif

            @if($isKaryawan)
                <div class="space-y-0.5">
                    <x-sidebar-link label="Dashboard" route="karyawan.dashboard" match="karyawan.dashboard" icon="grid" />
                    <x-sidebar-link label="Presensi Kehadiran" route="karyawan.absensi.index" match="karyawan.absensi.*" icon="cal-check" />
                    <x-sidebar-link label="Jadwal Mengajar" route="karyawan.jadwal.index" match="karyawan.jadwal.*" icon="cal" />
                    <x-sidebar-link label="Slip Gaji" route="karyawan.gaji.index" match="karyawan.gaji.*" icon="wallet" />
                    <x-sidebar-link label="Pengajuan Cuti" route="karyawan.cuti.index" match="karyawan.cuti.*" icon="inbox" />
                    <x-sidebar-link label="Pengumuman" route="karyawan.pengumuman.index" match="karyawan.pengumuman.*" icon="bell" />
                </div>
            @endif
        </nav>

        {{-- User chip --}}
        <div class="shrink-0 border-t border-white/10 px-3 py-3">
            <div class="flex items-center gap-3 rounded-xl px-2 py-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full
                            bg-teal-600 text-xs font-bold text-white ring-2 ring-teal-500/70">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-white leading-tight">
                        {{ auth()->user()?->name ?? 'User' }}
                    </p>
                    <p class="truncate text-[10px] text-teal-300 leading-tight mt-0.5">
                        {{ auth()->user() ? str(auth()->user()->role->value)->replace('_',' ')->title() : '' }}
                    </p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout"
                        class="shrink-0 rounded-lg p-1.5 text-teal-300 hover:bg-white/10 hover:text-white transition-colors">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ── MAIN ─────────────────────────────────────────────────── --}}

    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

        {{-- Topbar --}}
        <header class="flex h-16 shrink-0 items-center justify-between
                       border-b border-slate-200 bg-white px-4 lg:px-6">

            <div class="flex items-center gap-3">
                {{-- Hamburger (mobile) --}}
                <button @click="sidebarOpen = true"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg
                           border border-slate-200 text-slate-500
                           hover:bg-slate-50 hover:text-slate-700 transition-colors lg:hidden">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div>
                    <h1 class="text-[15px] font-bold text-slate-800 leading-tight tracking-tight">
                        {{ $title ?? $header ?? 'Dashboard' }}
                    </h1>
                    <p class="hidden sm:block text-[11px] text-slate-400 mt-0.5 leading-none">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1.5">

                {{-- Notification bell --}}
                <button class="relative inline-flex h-9 w-9 items-center justify-center
                               rounded-lg text-slate-400
                               hover:bg-slate-100 hover:text-slate-600 transition-colors">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                </button>

                <div class="mx-1 h-6 w-px bg-slate-200"></div>

                {{-- Profile dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50
                               px-2.5 py-1.5 text-sm font-medium text-slate-700
                               hover:bg-slate-100 hover:border-slate-300 transition-all">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full
                                    bg-teal-600 text-[11px] font-bold text-white">
                            {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                        </div>
                        <span class="hidden sm:block max-w-[110px] truncate text-sm leading-none">
                            {{ auth()->user()?->name ?? 'User' }}
                        </span>
                        <svg class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200"
                             :class="open && 'rotate-180'"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                        @click.away="open = false"
                        class="absolute right-0 top-full mt-2 w-52 origin-top-right
                               rounded-2xl border border-slate-200 bg-white
                               shadow-xl shadow-slate-200/60 overflow-hidden z-50"
                        style="display:none"
                    >
                        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ auth()->user()?->name }}</p>
                            <p class="truncate text-xs text-slate-500 mt-0.5">{{ auth()->user()?->email }}</p>
                        </div>

                        <div class="py-1">
                            @php
                                $profileRoute = auth()->user() && in_array(auth()->user()->role->value, ['admin_pusat','admin_unit'])
                                    ? route('admin.profile.edit')
                                    : (auth()->user() ? route('karyawan.profile.edit') : '#');
                            @endphp
                            <a href="{{ $profileRoute }}"
                               class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-teal-700 transition-colors">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profil Saya
                            </a>
                        </div>

                        <div class="border-t border-slate-100 py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-center gap-2.5 px-4 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50 transition-colors">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </header>

        {{-- Page content --}}
        <main class="page-content flex-1 overflow-y-auto bg-slate-100 p-4 md:p-6 lg:p-8">
            <div class="mx-auto max-w-7xl">
                @yield('content')
                @isset($slot){{ $slot }}@endisset
            </div>
        </main>

    </div>
</div>

@livewireScripts
@stack('scripts')
</body>
</html>
