<x-layouts.karyawan title="{{ auth()->user()->type->value === 'guru' ? 'Kehadiran Mengajar' : 'Absensi Saya' }}">
    <x-flash-message />

    <div class="space-y-6">
        <section class="page-hero">
            <div class="page-hero-grid lg:grid-cols-[1.15fr_0.85fr] lg:items-end">
                <div>
                    <span class="app-eyebrow">Attendance Console</span>
                    <h1>{{ auth()->user()->type->value === 'guru' ? 'Kehadiran Mengajar' : 'Absensi Harian' }}</h1>
                    <p>{{ auth()->user()->type->value === 'guru' ? 'Rekap hadir guru dihitung per sesi mengajar pada periode ' . $attendancePeriodLabel . '.' : 'Riwayat absensi Anda untuk periode ' . $attendancePeriodLabel . '.' }}</p>
                </div>

                <div class="surface-card bg-slate-900 border-slate-800" x-data="{ time: '' }" x-init="time = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'}); setInterval(() => { time = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'}) }, 1000)">
                    <span class="app-eyebrow text-slate-400">Realtime Clock</span>
                    <div class="mt-3 text-4xl font-black text-blue-400" x-text="time"></div>
                    <p class="mt-3 text-sm text-slate-400">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>
        </section>

        <section class="surface-card">
            <h2>Status Hari Ini</h2>
            <p class="section-note">Jam absensi masuk wajib pukul 07:30 dan absen pulang pukul 15:00. Presensi hanya bisa dilakukan di area yayasan dan wajib verifikasi kamera.</p>

            @php
                $attendanceCenter = \App\Support\AttendanceLocation::coordinates();
                $attendanceRadius = \App\Support\AttendanceLocation::radiusMeters();
            @endphp

            @if (! $schedule)
                <div class="flash-alert mt-5" data-tone="error"><div class="flash-alert-head"><div><h3>No Schedule</h3><p>Tidak ada jadwal hari ini.</p></div></div></div>
            @elseif (($schedule->day_type->value ?? $schedule->day_type) === 'libur')
                <div class="flash-alert mt-5" data-tone="success"><div class="flash-alert-head"><div><h3>Holiday</h3><p>Hari ini adalah hari libur.</p></div></div></div>
            @elseif ($attendanceToday && $attendanceToday->checked_out_at)
                <div class="flash-alert mt-5" data-tone="success"><div class="flash-alert-head"><div><h3>Attendance Complete</h3><p>Absensi hari ini selesai. Masuk {{ $attendanceToday->checked_in_at?->format('H:i') ?? '-' }}, pulang {{ $attendanceToday->checked_out_at?->format('H:i') ?? '-' }}.</p></div></div></div>
            @else
                <form method="POST" action="{{ route('karyawan.absensi.store') }}" class="mt-5 space-y-4" data-attendance-form data-geofence-latitude="{{ $attendanceCenter['latitude'] ?? '' }}" data-geofence-longitude="{{ $attendanceCenter['longitude'] ?? '' }}" data-geofence-radius="{{ $attendanceRadius }}">
                    @csrf
                    <input type="hidden" name="latitude" data-attendance-latitude>
                    <input type="hidden" name="longitude" data-attendance-longitude>
                    <input type="hidden" name="accuracy" data-attendance-accuracy>
                    <input type="hidden" name="face_image" data-attendance-face-image>
                    <input type="hidden" name="face_detected" value="0" data-attendance-face-detected>
                    <input type="hidden" name="attendance_challenge" value="{{ $attendanceChallenge }}">

                    @if ($errors->any())
                        <div class="flash-alert" data-tone="error">
                            <div class="flash-alert-head">
                                <div>
                                    <h3>Presensi belum bisa disimpan</h3>
                                    <p>{{ $errors->first() }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800">Geofencing Lokasi</h3>
                                    <p class="mt-1 text-xs text-slate-500">Pastikan GPS aktif dan izinkan akses lokasi.</p>
                                </div>
                                <span class="badge badge-gray" data-location-status>Menunggu</span>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div class="mini-panel">
                                    <span>Jarak</span>
                                    <strong data-location-distance>-</strong>
                                </div>
                                <div class="mini-panel">
                                    <span>Radius</span>
                                    <strong>{{ $attendanceRadius }} m</strong>
                                </div>
                            </div>
                            <button type="button" class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50" data-location-refresh>
                                Ambil Lokasi
                            </button>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-800">Web Auth Kamera</h3>
                                    <p class="mt-1 text-xs text-slate-500">Ambil foto wajah langsung dari kamera perangkat.</p>
                                </div>
                                <span class="badge badge-gray" data-face-status>Menunggu</span>
                            </div>
                            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-slate-900">
                                <video class="h-56 w-full object-cover" autoplay playsinline muted data-face-video></video>
                                <canvas class="hidden" width="640" height="480" data-face-canvas></canvas>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50" data-camera-start>
                                    Buka Kamera
                                </button>
                                <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50" data-face-capture>
                                    Ambil Foto
                                </button>
                            </div>
                        </div>
                    </div>

                    <div><label>Keterangan</label><textarea name="notes" rows="3"></textarea></div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="submit" class="btn-primary w-full sm:w-auto disabled:cursor-not-allowed disabled:opacity-60" data-attendance-submit disabled>
                            {{ $attendanceToday ? 'Tandai pulang' : 'Tandai hadir' }}
                        </button>
                    </div>
                </form>
            @endif

            {{-- Fitur Koreksi Absen --}}
            <div class="mt-8 border-t border-slate-100 pt-6" x-data="{ showCorrection: false }">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Lupa Absen?</h3>
                        <p class="text-xs text-slate-500">Ajukan koreksi jika Anda hadir tapi gagal mencatat absensi.</p>
                    </div>
                    <button @click="showCorrection = !showCorrection" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all">
                        <span x-show="!showCorrection">Ajukan Koreksi</span>
                        <span x-show="showCorrection">Batal</span>
                    </button>
                </div>

                <div x-show="showCorrection" class="mt-4 rounded-3xl border border-blue-100 bg-blue-50/50 p-6" style="display: none;">
                    <form action="{{ route('karyawan.absensi.koreksi') }}" method="POST" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
                        @csrf
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600 uppercase">Tanggal Kehadiran</label>
                            <input type="date" name="date" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-600 uppercase">Bukti Foto (Max 2MB)</label>
                            <input type="file" name="proof" accept="image/*" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-bold text-slate-600 uppercase">Alasan Koreksi</label>
                            <textarea name="reason" rows="2" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Misal: Kendala sistem, Lupa tap kartu, dll..."></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="w-full rounded-2xl bg-blue-600 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 hover:scale-[1.01] transition-all">Kirim Pengajuan Koreksi</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mini-grid mt-6">
                <div class="mini-panel">
                    <span>Jam Masuk</span>
                    <strong>
                        {{ $attendanceToday?->checked_in_at?->format('H:i') ?? '-' }}
                    </strong>
                </div>
                <div class="mini-panel">
                    <span>Jam Pulang</span>
                    <strong>
                        {{ $attendanceToday?->checked_out_at?->format('H:i') ?? '-' }}
                    </strong>
                </div>
            </div>
        </section>

        <section class="table-container">
            <div class="px-6 py-6 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-900">Riwayat Kehadiran</h2>
                <p class="text-sm text-slate-500 mt-1">Riwayat sesi atau absensi harian pada periode aktif.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="table-auto-full">
                    <thead>
                        <tr class="table-header">
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Hari</th>
                            <th class="px-6 py-4 text-center">Jam Masuk</th>
                            <th class="px-6 py-4 text-center">Jam Pulang</th>
                            <th class="px-6 py-4 text-center">Durasi</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendances as $attendance)
                            @php $duration = ($attendance->checked_in_at && $attendance->checked_out_at) ? $attendance->checked_in_at->diff($attendance->checked_out_at)->format('%H:%I') : '-'; @endphp
                            <tr class="table-row">
                                <td class="px-6 py-4 font-medium text-slate-900">{{ $attendance->schedule?->work_date?->format('d M Y') }}</td>
                                <td class="px-6 py-4">{{ $attendance->schedule?->work_date?->translatedFormat('l') }}</td>
                                <td class="px-6 py-4 text-center">{{ $attendance->checked_in_at?->format('H:i') ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">{{ $attendance->checked_out_at?->format('H:i') ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">{{ $duration }}</td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $status = $attendance->status->value ?? $attendance->status;
                                        $badgeClass = match($status) {
                                            'hadir' => 'badge-success',
                                            'terlambat' => 'badge-warning',
                                            'izin', 'sakit' => 'badge-info',
                                            'alpa' => 'badge-danger',
                                            default => 'badge-gray',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500">{{ $attendance->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div class="bg-slate-100 p-4 rounded-full mb-4">
                                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <h3 class="text-slate-900 font-semibold">Belum Ada Riwayat</h3>
                                        <p class="text-slate-500 text-sm mt-1">Belum ada riwayat absensi pada periode ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{ $attendances->links() }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-attendance-form]').forEach((form) => {
                const latitudeInput = form.querySelector('[data-attendance-latitude]');
                const longitudeInput = form.querySelector('[data-attendance-longitude]');
                const accuracyInput = form.querySelector('[data-attendance-accuracy]');
                const faceImageInput = form.querySelector('[data-attendance-face-image]');
                const faceDetectedInput = form.querySelector('[data-attendance-face-detected]');
                const submitButton = form.querySelector('[data-attendance-submit]');
                const locationStatus = form.querySelector('[data-location-status]');
                const locationDistance = form.querySelector('[data-location-distance]');
                const locationRefresh = form.querySelector('[data-location-refresh]');
                const faceStatus = form.querySelector('[data-face-status]');
                const cameraStart = form.querySelector('[data-camera-start]');
                const faceCapture = form.querySelector('[data-face-capture]');
                const video = form.querySelector('[data-face-video]');
                const canvas = form.querySelector('[data-face-canvas]');
                const centerLatitude = Number(form.dataset.geofenceLatitude);
                const centerLongitude = Number(form.dataset.geofenceLongitude);
                const radius = Number(form.dataset.geofenceRadius || 100);
                let hasValidLocation = false;
                let hasFaceImage = false;
                let cameraStream = null;

                const setStatus = (element, text, tone) => {
                    element.textContent = text;
                    element.className = `badge ${tone}`;
                };

                const syncSubmit = () => {
                    submitButton.disabled = !(hasValidLocation && hasFaceImage);
                };

                const distanceInMeters = (lat1, lon1, lat2, lon2) => {
                    const earthRadius = 6371000;
                    const toRad = (value) => value * Math.PI / 180;
                    const latitudeDelta = toRad(lat2 - lat1);
                    const longitudeDelta = toRad(lon2 - lon1);
                    const a = Math.sin(latitudeDelta / 2) ** 2
                        + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(longitudeDelta / 2) ** 2;

                    return Math.round(earthRadius * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
                };

                const requestLocation = () => {
                    if (!navigator.geolocation || !centerLatitude || !centerLongitude) {
                        setStatus(locationStatus, 'Gagal', 'badge-danger');
                        return;
                    }

                    setStatus(locationStatus, 'Memuat', 'badge-warning');
                    navigator.geolocation.getCurrentPosition((position) => {
                        const distance = distanceInMeters(position.coords.latitude, position.coords.longitude, centerLatitude, centerLongitude);
                        latitudeInput.value = position.coords.latitude.toFixed(7);
                        longitudeInput.value = position.coords.longitude.toFixed(7);
                        accuracyInput.value = Math.round(position.coords.accuracy || 0);
                        locationDistance.textContent = `${distance} m`;
                        hasValidLocation = distance <= radius;
                        setStatus(locationStatus, hasValidLocation ? 'Valid' : 'Di luar area', hasValidLocation ? 'badge-success' : 'badge-danger');
                        syncSubmit();
                    }, () => {
                        hasValidLocation = false;
                        setStatus(locationStatus, 'Ditolak', 'badge-danger');
                        syncSubmit();
                    }, {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 0,
                    });
                };

                const startCamera = async () => {
                    try {
                        cameraStream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                            audio: false,
                        });
                        video.srcObject = cameraStream;
                        setStatus(faceStatus, 'Kamera aktif', 'badge-warning');
                    } catch (error) {
                        setStatus(faceStatus, 'Kamera ditolak', 'badge-danger');
                    }
                };

                const captureFace = async () => {
                    if (!video.srcObject) {
                        await startCamera();
                    }

                    if (!video.srcObject || video.readyState < 2) {
                        setStatus(faceStatus, 'Belum siap', 'badge-warning');
                        return;
                    }

                    const context = canvas.getContext('2d');
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);

                    if ('FaceDetector' in window) {
                        try {
                            const detector = new FaceDetector({ fastMode: true, maxDetectedFaces: 1 });
                            const faces = await detector.detect(canvas);
                            if (faces.length < 1) {
                                hasFaceImage = false;
                                faceImageInput.value = '';
                                faceDetectedInput.value = '0';
                                setStatus(faceStatus, 'Wajah tidak terbaca', 'badge-danger');
                                syncSubmit();
                                return;
                            }
                            faceDetectedInput.value = '1';
                        } catch (error) {
                            faceDetectedInput.value = '0';
                        }
                    }

                    faceImageInput.value = canvas.toDataURL('image/jpeg', 0.82);
                    hasFaceImage = true;
                    setStatus(faceStatus, 'Foto siap', 'badge-success');
                    syncSubmit();
                };

                locationRefresh.addEventListener('click', requestLocation);
                cameraStart.addEventListener('click', startCamera);
                faceCapture.addEventListener('click', captureFace);
                form.addEventListener('submit', (event) => {
                    if (!hasValidLocation || !hasFaceImage) {
                        event.preventDefault();
                        requestLocation();
                        setStatus(faceStatus, hasFaceImage ? 'Foto siap' : 'Ambil foto dulu', hasFaceImage ? 'badge-success' : 'badge-warning');
                    }
                });

                requestLocation();
                startCamera();
            });
        });
    </script>
</x-layouts.karyawan>
