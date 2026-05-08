@props(['title' => 'Karyawan HRIS Yayasan Sirojul Falah'])

<x-layouts.app>
    @slot('header')
        {{ $title }}
    @endslot

    {{ $slot }}
</x-layouts.app>
