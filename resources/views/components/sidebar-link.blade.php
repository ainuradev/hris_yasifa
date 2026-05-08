@props(['label', 'route', 'match', 'icon'])

@php
    $active = request()->routeIs($match);
@endphp

<a href="{{ route($route) }}"
   @class([
       'group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition-all duration-150',
       'bg-white/15 text-white font-semibold shadow-sm ring-1 ring-white/10' => $active,
       'text-teal-100 hover:bg-white/10 hover:text-white' => !$active
   ])>

    <span @class([
        'shrink-0 transition-colors',
        'text-teal-200' => $active,
        'text-teal-300 group-hover:text-teal-200' => !$active
    ])>
        @switch($icon)
            @case('grid')
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/><rect x="14" y="14" width="7" height="7" rx="1.2"/></svg>
                @break
            @case('users')
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                @break
            @case('cal-check')
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></svg>
                @break
            @case('cal')
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                @break
            @case('book')
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                @break
            @case('inbox')
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-6l-2 3h-4l-2-3H2"/><path stroke-linecap="round" stroke-linejoin="round" d="M5.45 5.11L2 12v6a2 2 0 002 2h16a2 2 0 002-2v-6l-3.45-6.89A2 2 0 0016.76 4H7.24a2 2 0 00-1.79 1.11z"/></svg>
                @break
            @case('wallet')
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="2" y="6" width="20" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 14a1 1 0 110-2 1 1 0 010 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 10h20M6 6V4a1 1 0 011-1h10a1 1 0 011 1v2"/></svg>
                @break
            @case('bell')
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                @break
            @case('check')
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @break
        @endswitch
    </span>

    <span class="flex-1 truncate">{{ $label }}</span>

    @if($active)
        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-teal-300 shadow-[0_0_8px_rgba(153,246,228,0.6)]"></span>
    @endif
</a>
