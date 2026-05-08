<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Lengkap</th>
            <th>NIK</th>
            <th>NUPTK</th>
            <th>Email</th>
            <th>No. Telepon</th>
            <th>Unit</th>
            <th>Jabatan</th>
            <th>Tipe</th>
            <th>Status Kepegawaian</th>
            <th>Status</th>
            <th>Tanggal Lahir</th>
            <th>Jenis Kelamin</th>
            <th>Pendidikan Terakhir</th>
            <th>Kontrak Berakhir</th>
            <th>Tanggal Bergabung (TMT)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($employees as $i => $employee)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $employee->name }}</td>
            <td>{{ $employee->nik }}</td>
            <td>{{ $employee->nuptk ?? '-' }}</td>
            <td>{{ $employee->email }}</td>
            <td>{{ $employee->phone ?? '-' }}</td>
            <td>{{ $employee->unit?->name ?? 'Pusat' }}</td>
            <td>{{ $employee->teacherDetail?->jabatan ?? $employee->nonTeacherDetail?->jabatan ?? '-' }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $employee->type->value ?? $employee->type)) }}</td>
            <td>{{ $employee->status_kepegawaian ?? '-' }}</td>
            <td>{{ ucfirst($employee->status->value ?? $employee->status) }}</td>
            <td>{{ $employee->date_of_birth?->format('d/m/Y') ?? '-' }}</td>
            <td>{{ $employee->gender === 'laki_laki' ? 'Laki-laki' : 'Perempuan' }}</td>
            <td>{{ $employee->pendidikan_terakhir ?? '-' }}</td>
            <td>{{ $employee->contract_end_date?->format('d/m/Y') ?? '-' }}</td>
            <td>{{ $employee->tmt_pegawai?->format('d/m/Y') ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
