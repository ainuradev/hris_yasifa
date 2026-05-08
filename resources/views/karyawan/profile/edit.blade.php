<x-layouts.karyawan title="Profil Saya">
    @include('profile.form', ['employee' => $employee, 'action' => route('karyawan.profile.update')])
</x-layouts.karyawan>
