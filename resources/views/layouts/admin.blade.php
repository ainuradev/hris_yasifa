{{--
    admin.blade.php
    ───────────────
    Layout khusus admin (admin_pusat / admin_unit).
    Extends app.blade.php yang sudah punya sidebar, topbar, dan slot content.

    Usage di Livewire component / Blade view:
        <x-layouts.admin :title="'Data Karyawan'">
            ... page content ...
        </x-layouts.admin>

    Atau di blade biasa:
        @extends('layouts.admin')
        @section('content') ... @endsection
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin · HRIS Sirojul Falah' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* ── Sidebar: hide on mobile, show on desktop ── */
        #desktop-sidebar { display: none; }
        @media (min-width: 1024px) {
            #desktop-sidebar { display: flex; flex-direction: column; width: 16rem; flex-shrink: 0; }
        }

        /* ── Bottom nav: show on mobile, hide on desktop ── */
        #bottom-nav { display: block; }
        @media (min-width: 1024px) {
            #bottom-nav { display: none !important; }
        }

        /* ── Dashboard pie chart heights: smaller on mobile ── */
        @media (max-width: 768px) {
            #unitPieChart, #statusPieChart {
                max-height: 140px;
            }
        }

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

        /* ── Bottom nav item styles ── */
        .bnav-link {
            display: flex; flex: 1; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 2px; padding: 4px 2px; position: relative;
            color: #94a3b8; text-decoration: none;
            transition: color 0.15s;
        }
        .bnav-link:hover { color: #475569; }
        .bnav-link.active { color: #0d9488; }
        .bnav-link svg { stroke-width: 1.8; }
        .bnav-link.active svg { stroke-width: 2.5; }
        .bnav-label { font-size: 9px; font-weight: 500; line-height: 1; margin-top: 2px; }
        .bnav-link.active .bnav-label { font-weight: 700; }
        .bnav-dot {
            position: absolute; bottom: 2px; left: 50%;
            transform: translateX(-50%);
            width: 4px; height: 4px; border-radius: 99px;
            background: #0d9488;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

<div class="flex h-screen overflow-hidden">


    <aside id="desktop-sidebar"
           class="bg-teal-800 text-teal-50 flex-shrink-0"
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

        {{-- Nav --}}
        <nav class="sidebar-scroll flex-1 overflow-y-auto px-3 py-4">
            <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-teal-400">Menu Admin</p>

            @php
                $adminLinks = [
                    ['label'=>'Dashboard',      'route'=>'admin.dashboard',         'match'=>'admin.dashboard',      'icon'=>'grid'],
                    ['label'=>'Data Karyawan',   'route'=>'admin.karyawan.index',    'match'=>'admin.karyawan.*',     'icon'=>'users'],
                    ['label'=>'Absensi',         'route'=>'admin.absensi.index',     'match'=>'admin.absensi.*',      'icon'=>'cal-check'],
                    ['label'=>'Data Rombel',     'route'=>'admin.rombel.index',      'match'=>'admin.rombel.*',       'icon'=>'cal'],
                    ['label'=>'Mata Pelajaran',  'route'=>'admin.subjects.index',    'match'=>'admin.subjects.*',     'icon'=>'bookmark'],
                    ['label'=>'Penggajian',      'route'=>'admin.penggajian.index',  'match'=>'admin.penggajian.*',   'icon'=>'wallet'],
                    ['label'=>'Pengumuman',      'route'=>'admin.pengumuman.index',  'match'=>'admin.pengumuman.*',   'icon'=>'bell'],
                ];
            @endphp

            <div class="space-y-0.5">
            @foreach($adminLinks as $link)
                @php $active = request()->routeIs($link['match']); @endphp
                <a href="{{ route($link['route']) }}"
                   @class(['group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-150',
                           'nav-active' => $active,
                           'text-teal-100 hover:bg-white/10 hover:text-white' => !$active])>

                    <span class="nav-ic shrink-0 text-teal-300 transition-colors group-hover:text-teal-200 {{ $active ? 'text-teal-200' : '' }}">
                        @include('layouts._nav-icon', ['icon' => $link['icon']])
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
                    {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-white leading-tight">{{ auth()->user()?->name }}</p>
                    <p class="truncate text-[10px] text-teal-300 leading-tight mt-0.5">
                        {{ auth()->user()?->isAdminPusat() ? 'Admin Pusat' : 'Admin Unit' }}
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

    {{-- ── MAIN ─────────────────────────────────────────── --}}

    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

        {{-- Topbar --}}
        <header class="flex h-16 shrink-0 items-center justify-between
                       border-b border-slate-200 bg-white px-4 lg:px-6">
            <div class="flex items-center gap-3">
                {{-- Hamburger: hanya tampil di desktop saja (lg+) jika diperlukan --}}
                <div>
                    <h1 class="text-[15px] font-bold text-slate-800 leading-tight tracking-tight">
                        {{ $title ?? 'Dashboard' }}
                    </h1>
                    <p class="hidden sm:block text-[11px] text-slate-400 mt-0.5 leading-none">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1.5">
                <button class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg
                               text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                </button>

                <div class="mx-1 h-6 w-px bg-slate-200"></div>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50
                               px-2.5 py-1.5 hover:bg-slate-100 hover:border-slate-300 transition-all">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-teal-600 text-[11px] font-bold text-white">
                            {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
                        </div>
                        <span class="hidden sm:block max-w-[110px] truncate text-sm font-medium text-slate-700">
                            {{ auth()->user()?->name }}
                        </span>
                        <svg class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'"
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
                            <a href="{{ route('admin.profile.edit') }}"
                               class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-teal-700 transition-colors">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profil Saya
                            </a>
                        </div>
                        <div class="border-t border-slate-100 py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 px-4 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50 transition-colors">
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
        <main class="page-content flex-1 overflow-y-auto bg-slate-100 p-4 pb-20 md:p-6 lg:p-8 lg:pb-8">
            <div class="mx-auto max-w-6xl">
                {{ $slot }}
            </div>
        </main>
    </div>
</div>

{{-- ── BOTTOM NAV (mobile only) ──────────────────────────── --}}
<style>
    /* Bottom nav CSS already in <head> */
</style>

<nav id="bottom-nav"
     style="position:fixed; bottom:0; left:0; right:0; z-index:9999;
            background:#fff; border-top:1px solid #e2e8f0;
            padding-bottom: env(safe-area-inset-bottom, 0);">
    @php
        $bottomLinks = [
            ['label' => 'Home',     'route' => 'admin.dashboard',         'match' => 'admin.dashboard'],
            ['label' => 'Karyawan', 'route' => 'admin.karyawan.index',    'match' => 'admin.karyawan.*'],
            ['label' => 'Absensi',  'route' => 'admin.absensi.index',     'match' => 'admin.absensi.*'],
            ['label' => 'Rombel',   'route' => 'admin.rombel.index',      'match' => 'admin.rombel.*'],
            ['label' => 'Gaji',     'route' => 'admin.penggajian.index',  'match' => 'admin.penggajian.*'],
            ['label' => 'Info',     'route' => 'admin.pengumuman.index',  'match' => 'admin.pengumuman.*'],
        ];
        $bottomIcons = [
            'admin.dashboard'     => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            'admin.karyawan.*'    => 'M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8zm6 0a3 3 0 100-6 3 3 0 000 6zM3 14a3 3 0 100-6 3 3 0 000 6z',
            'admin.absensi.*'     => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            'admin.rombel.*'      => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'admin.penggajian.*'  => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
            'admin.pengumuman.*'  => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        ];
    @endphp
    <div style="display:flex; align-items:stretch; height:56px;">
        @foreach ($bottomLinks as $link)
            @php $active = request()->routeIs($link['match']); @endphp
            <a href="{{ route($link['route']) }}" class="bnav-link {{ $active ? 'active' : '' }}">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $bottomIcons[$link['match']] }}"/>
                </svg>
                <span class="bnav-label">{{ $link['label'] }}</span>
                @if ($active)<span class="bnav-dot"></span>@endif
            </a>
        @endforeach
    </div>
</nav>

@stack('scripts')
@livewireScripts
</body>
</html>
