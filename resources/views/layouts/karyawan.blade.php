<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Karyawan · HRIS Sirojul Falah' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        .nav-active {
            background: rgba(255,255,255,0.14);
            color: #fff;
            font-weight: 600;
        }
        .nav-active .nav-ic { color: #99f6e4; }

        .sidebar-scroll::-webkit-scrollbar { width: 3px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 99px; }

        @keyframes pageFadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .page-content { animation: pageFadeIn 0.2s ease both; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

<div class="flex h-screen overflow-hidden">

    {{-- ── SIDEBAR ──────────────────────────────────────── --}}

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
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-teal-300 mt-0.5">Portal Karyawan</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="sidebar-scroll flex-1 overflow-y-auto px-3 py-4">
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-teal-400">Menu Karyawan</p>

            @php
                $karyawanLinks = [
                    ['label'=>'Dashboard',        'route'=>'karyawan.dashboard',         'match'=>'karyawan.dashboard',         'icon'=>'grid'],
                    ['label'=>'Absensi',          'route'=>'karyawan.absensi.index',     'match'=>'karyawan.absensi.*',         'icon'=>'cal-check'],
                    ['label'=>'Jadwal Mengajar',  'route'=>'karyawan.jadwal.index',      'match'=>'karyawan.jadwal.*',          'icon'=>'cal'],
                    ['label'=>'Slip Gaji',        'route'=>'karyawan.gaji.index',        'match'=>'karyawan.gaji.*',            'icon'=>'wallet'],
                    ['label'=>'Pengajuan Cuti',   'route'=>'karyawan.cuti.index',        'match'=>'karyawan.cuti.*',            'icon'=>'inbox'],
                    ['label'=>'Pengumuman',       'route'=>'karyawan.pengumuman.index',  'match'=>'karyawan.pengumuman.*',      'icon'=>'bell'],
                ];
            @endphp

            <div class="space-y-0.5">
            @foreach($karyawanLinks as $link)
                @php $active = request()->routeIs($link['match']); @endphp
                <a href="{{ route($link['route']) }}"
                   @class(['group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150',
                           'nav-active' => $active,
                           'text-teal-100 hover:bg-white/10 hover:text-white' => !$active])>

                    <span class="nav-ic shrink-0 text-teal-300 transition-colors group-hover:text-teal-200 {{ $active ? 'text-teal-200' : '' }}">
                        @switch($link['icon'])
                            @case('grid')
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/><rect x="14" y="14" width="7" height="7" rx="1.2"/></svg>
                                @break
                            @case('cal-check')
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></svg>
                                @break
                            @case('cal')
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                                @break
                            @case('wallet')
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="6" width="20" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 14a1 1 0 110-2 1 1 0 010 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 10h20M6 6V4a1 1 0 011-1h10a1 1 0 011 1v2"/></svg>
                                @break
                            @case('inbox')
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-6l-2 3h-4l-2-3H2"/><path stroke-linecap="round" stroke-linejoin="round" d="M5.45 5.11L2 12v6a2 2 0 002 2h16a2 2 0 002-2v-6l-3.45-6.89A2 2 0 0016.76 4H7.24a2 2 0 00-1.79 1.11z"/></svg>
                                @break
                            @case('bell')
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                @break
                        @endswitch
                    </span>
                    <span class="flex-1 truncate font-medium">{{ $link['label'] }}</span>
                    @if($active)<span class="h-1.5 w-1.5 shrink-0 rounded-full bg-teal-300"></span>@endif
                </a>
            @endforeach
            </div>
        </nav>

        {{-- User chip --}}
        <div class="shrink-0 border-t border-white/10 px-3 py-3">
            <div class="flex items-center gap-3 rounded-xl px-2 py-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full
                            bg-teal-600 text-xs font-bold text-white ring-2 ring-teal-500/70">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'K', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-white leading-tight">{{ auth()->user()?->name }}</p>
                    <p class="truncate text-[10px] text-teal-300 leading-tight mt-0.5">Karyawan</p>
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

    {{-- ── MAIN ─────────────────────────────────────────── --}}

    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

        {{-- Topbar --}}
        <header class="flex h-16 shrink-0 items-center justify-between
                       border-b border-slate-200 bg-white px-4 lg:px-6">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg
                           border border-slate-200 text-slate-500
                           hover:bg-slate-50 hover:text-slate-700 transition-colors lg:hidden">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div>
                    <p class="text-[11px] text-slate-400 leading-none">
                        Selamat datang kembali,
                    </p>
                    <h1 class="text-[15px] font-bold text-slate-800 leading-tight tracking-tight mt-0.5">
                        {{ auth()->user()?->name ?? 'Karyawan' }}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-1.5">
                {{-- Notification --}}
                <button class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg
                               text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                </button>

                <div class="mx-1 h-6 w-px bg-slate-200"></div>

                {{-- Quick logout pill --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50
                               px-3 py-1.5 text-sm font-medium text-slate-600
                               hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600 transition-all">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-teal-600 text-[11px] font-bold text-white">
                            {{ strtoupper(substr(auth()->user()?->name ?? 'K', 0, 1)) }}
                        </div>
                        <span class="hidden sm:block">Logout</span>
                    </button>
                </form>
            </div>
        </header>

        {{-- Page content --}}
        <main class="page-content flex-1 overflow-y-auto bg-slate-100 p-4 sm:p-6">
            <div class="mx-auto max-w-5xl">
                {{ $slot }}
            </div>
        </main>
    </div>
</div>

</body>
</html>