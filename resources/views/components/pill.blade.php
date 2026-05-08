@props([
    'color' => 'gray',
    'text',
])

@php
    $classes = match ($color) {
        'green' => 'status-pill--green',
        'amber' => 'status-pill--amber',
        'red' => 'status-pill--red',
        'blue' => 'status-pill--blue',
        'teal' => 'status-pill--teal',
        default => 'status-pill--gray',
    };
@endphp

<span class="status-pill {{ $classes }}">{{ $text }}</span>
