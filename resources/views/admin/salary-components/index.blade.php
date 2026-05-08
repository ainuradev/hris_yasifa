<x-layouts.admin title="Master Komponen Gaji">
    <x-flash-message />

    <div class="space-y-6">
        <section class="page-hero">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="app-eyebrow">Master Data</span>
                    <h1>Komponen Gaji</h1>
                    <p class="text-slate-500">Kelola daftar tunjangan dan potongan gaji karyawan.</p>
                </div>
                <a href="{{ route('admin.salary-components.create') }}" class="btn-primary">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Komponen
                </a>
            </div>
        </section>

        <div class="table-container">
            <div class="overflow-x-auto">
                <table class="table-auto-full">
                    <thead class="table-header">
                        <tr>
                            <th class="table-cell">Nama Komponen</th>
                            <th class="table-cell">Unit</th>
                            <th class="table-cell">Tipe</th>
                            <th class="table-cell text-right">Nominal Default</th>
                            <th class="table-cell text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($components as $component)
                            <tr class="table-row">
                                <td class="table-cell font-semibold text-slate-900">{{ $component->name }}</td>
                                <td class="table-cell">
                                    @if($component->unit_id)
                                        <span class="badge badge-info">{{ $component->unit->name }}</span>
                                    @else
                                        <span class="badge" style="background:#f1f5f9;color:#64748b">Global (Semua Unit)</span>
                                    @endif
                                </td>
                                <td class="table-cell">
                                    @if($component->type === 'tunjangan')
                                        <span class="badge badge-success">Tunjangan</span>
                                    @else
                                        <span class="badge badge-danger">Potongan</span>
                                    @endif
                                </td>
                                <td class="table-cell text-right font-medium text-slate-900">
                                    Rp {{ number_format($component->default_amount, 0, ',', '.') }}
                                </td>
                                <td class="table-cell">
                                    @if(auth()->user()->isAdminPusat() || $component->unit_id === auth()->user()->unit_id)
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.salary-components.edit', $component) }}" class="btn-secondary py-1.5 px-3 text-xs">Edit</a>
                                        <form action="{{ route('admin.salary-components.destroy', $component) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus komponen ini? Semua karyawan yang terhubung akan kehilangan komponen ini.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger py-1.5 px-3 text-xs bg-red-50 text-red-600 hover:bg-red-100 border-none">Hapus</button>
                                        </form>
                                    </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic text-center block">Hanya View</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="table-cell py-12 text-center">
                                    <x-empty-state message="Belum ada komponen gaji yang ditambahkan." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.admin>
