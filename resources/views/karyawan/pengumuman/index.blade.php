<x-layouts.karyawan title="Pengumuman">
    <x-flash-message />

    <div class="space-y-6" x-data="{ filter: 'semua' }">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pengumuman</h1>
            <p class="text-sm text-slate-500">Informasi terbaru yang relevan untuk Anda.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" class="rounded-full px-4 py-2 text-sm font-semibold" :class="filter === 'semua' ? 'bg-[#1a2744] text-white' : 'bg-white border border-slate-200 text-slate-700'" @click="filter = 'semua'">Semua</button>
            <button type="button" class="rounded-full px-4 py-2 text-sm font-semibold" :class="filter === 'umum' ? 'bg-[#1a2744] text-white' : 'bg-white border border-slate-200 text-slate-700'" @click="filter = 'umum'">Umum</button>
            <button type="button" class="rounded-full px-4 py-2 text-sm font-semibold" :class="filter === 'penggajian' ? 'bg-[#1a2744] text-white' : 'bg-white border border-slate-200 text-slate-700'" @click="filter = 'penggajian'">Penggajian</button>
            <button type="button" class="rounded-full px-4 py-2 text-sm font-semibold" :class="filter === 'absensi' ? 'bg-[#1a2744] text-white' : 'bg-white border border-slate-200 text-slate-700'" @click="filter = 'absensi'">Absensi</button>
            <button type="button" class="rounded-full px-4 py-2 text-sm font-semibold" :class="filter === 'kegiatan' ? 'bg-[#1a2744] text-white' : 'bg-white border border-slate-200 text-slate-700'" @click="filter = 'kegiatan'">Kegiatan</button>
        </div>

        <div class="space-y-4">
            @forelse ($announcements as $announcement)
                @php
                    $category = $announcement->category->value ?? $announcement->category;
                    $color = $category === 'penggajian' ? 'green' : ($category === 'absensi' ? 'amber' : ($category === 'kegiatan' ? 'blue' : 'teal'));
                @endphp
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm" x-show="filter === 'semua' || filter === '{{ $category }}'">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $color === 'green' ? 'bg-green-100 text-green-700' : ($color === 'amber' ? 'bg-amber-100 text-amber-700' : ($color === 'blue' ? 'bg-blue-100 text-blue-700' : 'bg-teal-100 text-teal-700')) }}">&#9679;</div>
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">{{ $announcement->title }}</h2>
                                <p class="mt-2 text-sm leading-7 text-slate-600">{{ $announcement->content }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <x-pill :text="$announcement->is_global ? 'Semua unit' : ($announcement->unit?->name ?? '-')" color="blue" />
                            <x-pill :text="ucfirst($category)" :color="$color" />
                        </div>
                    </div>
                    <p class="mt-4 text-xs text-slate-400">{{ $announcement->created_at?->translatedFormat('d F Y H:i') }}</p>
                </div>
            @empty
                <x-empty-state message="Belum ada pengumuman yang relevan untuk Anda." />
            @endforelse
        </div>

        {{ $announcements->links() }}
    </div>
</x-layouts.karyawan>
