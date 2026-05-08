<table>
    <thead>
        <tr>
            <th colspan="9" style="text-align: center; font-size: 14px; font-weight: bold;">
                REKAP ABSENSI KARYAWAN - BULAN {{ str_pad($month, 2, '0', STR_PAD_LEFT) }} TAHUN {{ $year }}
            </th>
        </tr>
        <tr>
            <th>No</th>
            <th>Nama Lengkap</th>
            <th>Unit</th>
            <th>Tipe Karyawan</th>
            <th>Hadir</th>
            <th>JTM (Jam)</th>
            <th>Terlambat</th>
            <th>Izin</th>
            <th>Alpa / Tdk Hadir</th>
        </tr>
    </thead>
    <tbody>
        @foreach($employees as $i => $employee)
            @php
                $empAtt = $attendances->get($employee->id, collect());
                $hadir = $empAtt->where('status', 'hadir')->count();
                $terlambat = $empAtt->where('status', 'terlambat')->count();
                $izin = $empAtt->where('status', 'izin')->count();
                $alpa = $empAtt->where('status', 'alpa')->count();
            @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $employee->name }}</td>
            <td>{{ $employee->unit?->name ?? 'Pusat' }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $employee->type->value ?? $employee->type)) }}</td>
            <td>{{ $hadir }}</td>
            <td>{{ $employee->type->value === 'guru' ? ($jtms[$employee->id] ?? 0) : '-' }}</td>
            <td>{{ $terlambat }}</td>
            <td>{{ $izin }}</td>
            <td>{{ $alpa }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
