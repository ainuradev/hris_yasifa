@props([
    'label',
    'value',
    'sub' => null,
    'subColor' => 'blue',
])

@php
    $subClasses = match ($subColor) {
        'green' => 'status-pill--green',
        'amber' => 'status-pill--amber',
        'red' => 'status-pill--red',
        default => 'status-pill--blue',
    };
@endphp

<div class="stat-card">
    <div class="stat-card-label">
        <span>{{ $label }}</span>
        <span>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 14l4-4 4 4 8-8" />
            </svg>
        </span>
    </div>
    <div class="stat-card-value">{{ $value }}</div>
    @if ($sub)
        <div class="mt-4">
            <span class="status-pill {{ $subClasses }}">{{ $sub }}</span>
        </div>
    @endif
</div>
