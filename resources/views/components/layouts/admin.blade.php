@props(['title' => 'Admin HRIS Yayasan Sirojul Falah'])

<x-layouts.app>
    @slot('header')
        {{ $title }}
    @endslot

    {{ $slot }}
</x-layouts.app>
