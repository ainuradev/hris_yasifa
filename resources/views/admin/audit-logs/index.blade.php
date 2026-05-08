<x-layouts.admin title="System Audit Log">
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">System Audit Log</h1>
            <p class="text-sm text-slate-500">Catatan riwayat aktivitas penting pada sistem HRIS.</p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Waktu</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Aktor</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Deskripsi</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Model</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                                {{ $log->created_at->format('d M Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="h-7 w-7 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-600">
                                        {{ substr($log->actor?->name ?? 'Sys', 0, 2) }}
                                    </div>
                                    <span class="text-sm font-semibold text-slate-700">{{ $log->actor?->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-bold text-blue-600 uppercase tracking-wider">
                                    {{ str($log->action)->replace('.', ' ')->title() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $log->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400">
                                {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <x-empty-state message="Belum ada catatan log aktivitas." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </div>
</x-layouts.admin>
