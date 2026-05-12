<?php

namespace App\Http\Controllers\Karyawan;

use App\Enums\AttendanceStatus;
use App\Enums\DayType;
use App\Enums\EmployeeType;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Support\AttendanceLocation;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AbsensiController extends Controller
{
    public function index(ScheduleService $scheduleService): View
    {
        $employee = auth()->user();
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfDay();
        $attendanceChallenge = Str::random(40);

        session(['attendance_challenge' => $attendanceChallenge]);
        
        $schedule = $scheduleService->ensureScheduleExists($employee->unit_id, today());

        $attendanceQuery = Attendance::query()
            ->with(['schedule', 'teacherSubjectUnit.subject', 'teacherSubjectUnit.unit'])
            ->where('employee_id', $employee->id)
            ->whereHas('schedule', function ($query) use ($periodStart, $periodEnd): void {
                $query->whereBetween('work_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);
            });

        $attendanceToday = $schedule
            ? Attendance::where('employee_id', $employee->id)
                ->where('schedule_id', $schedule->id)
                ->dailyRecords()
                ->first()
            : null;

        $attendances = $attendanceQuery
            ->dailyRecords()
            ->latest('checked_in_at')
            ->paginate(20);


        $attendancePeriodLabel = sprintf(
            '%s - %s',
            $periodStart->translatedFormat('d M Y'),
            $periodEnd->translatedFormat('d M Y')
        );

        return view('karyawan.absensi.index', compact(
            'schedule',
            'attendanceToday',
            'attendances',
            'attendancePeriodLabel',
            'attendanceChallenge'
        ));
    }

    public function store(Request $request, ScheduleService $scheduleService): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
            'face_image' => ['required', 'string', 'max:7000000'],
            'face_detected' => ['nullable', 'boolean'],
            'attendance_challenge' => ['required', 'string'],
        ]);

        $employee = auth()->user();

        if (! hash_equals((string) session('attendance_challenge'), $validated['attendance_challenge'])) {
            throw ValidationException::withMessages([
                'attendance_challenge' => 'Sesi verifikasi presensi tidak valid. Muat ulang halaman lalu coba lagi.',
            ]);
        }

        $schedule = $scheduleService->ensureScheduleExists($employee->unit_id, today());

        if ($schedule->day_type === DayType::Libur) {
            session()->flash('error', 'Hari ini adalah hari libur.');

            return back();
        }

        $now = now();
        $currentTime = $now->format('H:i');

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('schedule_id', $schedule->id)
            ->dailyRecords()
            ->first();

        if ($attendance && $attendance->checked_out_at) {
            session()->flash('error', 'Absensi hari ini sudah selesai.');

            return back();
        }

        $locationCheck = $this->validateGeofence((float) $validated['latitude'], (float) $validated['longitude']);
        $facePath = $this->storeFaceImage($validated['face_image'], $employee->id, $attendance ? 'check-out' : 'check-in');
        $challengeHash = Hash::make($validated['attendance_challenge']);

        if ($attendance) {
            // Perform check-out
            $attendance->update([
                'checked_out_at' => $now,
                'check_out_latitude' => $validated['latitude'],
                'check_out_longitude' => $validated['longitude'],
                'check_out_distance_meters' => $locationCheck['distance'],
                'face_check_out_path' => $facePath,
                'face_verified' => true,
                'attendance_challenge_hash' => $challengeHash,
                'attendance_ip' => $request->ip(),
                'attendance_user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
                'notes' => $validated['notes'] ?? $attendance->notes,
            ]);

            session()->forget('attendance_challenge');
            session()->flash('success', 'Absensi pulang berhasil disimpan.');

            return back();
        }

        // Check-in hanya dibuka pada jam masuk. Check-out tetap boleh lebih sore
        // karena rapat atau kegiatan sekolah bisa membuat karyawan pulang terlambat.
        if ($currentTime < '07:00' || $currentTime > '14:30') {
            session()->flash('error', 'Di luar jam absensi masuk (07:00 - 14:30).');

            return back();
        }

        // Perform check-in (Tepat waktu <= 07:10)
        $status = $currentTime > '07:10' ? AttendanceStatus::Terlambat->value : AttendanceStatus::Hadir->value;

        Attendance::create([
            'employee_id' => $employee->id,
            'schedule_id' => $schedule->id,
            'checked_in_at' => $now,
            'checked_out_at' => null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'check_in_distance_meters' => $locationCheck['distance'],
            'face_check_in_path' => $facePath,
            'face_verified' => true,
            'attendance_challenge_hash' => $challengeHash,
            'attendance_ip' => $request->ip(),
            'attendance_user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'status' => $status,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Auto-attendance untuk jadwal mengajar (sesi)
        $employee->load('teacherDetail.teacherSubjectUnits');
        if ($employee->teacherDetail) {
            $dayNameMap = [
                1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
                4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
            ];
            $todayName = $dayNameMap[$now->dayOfWeekIso] ?? 'Senin';

            $sessions = $employee->teacherDetail->teacherSubjectUnits()->where('day_name', $todayName)->get();

            foreach ($sessions as $session) {
                // Determine status based on session start time
                $sessionStart = \Carbon\Carbon::parse($session->start_time)->format('H:i');
                $sessionStatus = ($currentTime > $sessionStart) ? AttendanceStatus::Alpa->value : AttendanceStatus::Hadir->value;

                // Check if existing record is there (e.g., Izin)
                $existingSession = Attendance::where('employee_id', $employee->id)
                    ->where('schedule_id', $schedule->id)
                    ->where('jadwal_id', $session->jadwal_id)
                    ->first();

                if (!$existingSession) {
                    Attendance::create([
                        'employee_id' => $employee->id,
                        'schedule_id' => $schedule->id,
                        'teacher_subject_unit_id' => $session->id,
                        'jadwal_id' => $session->jadwal_id,
                        'checked_in_at' => $now,
                        'latitude' => $validated['latitude'],
                        'longitude' => $validated['longitude'],
                        'check_in_distance_meters' => $locationCheck['distance'],
                        'face_check_in_path' => $facePath,
                        'face_verified' => true,
                        'attendance_ip' => $request->ip(),
                        'attendance_user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
                        'status' => $sessionStatus,
                        'is_approved' => true,
                    ]);
                }
            }
        }

        session()->forget('attendance_challenge');
        session()->flash('success', 'Absensi berhasil disimpan.');

        return back();
    }

    private function validateGeofence(float $latitude, float $longitude): array
    {
        $target = AttendanceLocation::coordinates();

        if (! $target) {
            throw ValidationException::withMessages([
                'latitude' => 'Koordinat yayasan belum dikonfigurasi. Hubungi admin HRIS.',
            ]);
        }

        $distance = (int) round(AttendanceLocation::distanceInMeters(
            $latitude,
            $longitude,
            $target['latitude'],
            $target['longitude']
        ));
        $radius = AttendanceLocation::radiusMeters();

        if ($distance > $radius) {
            throw ValidationException::withMessages([
                'latitude' => "Anda berada di luar area presensi. Jarak saat ini {$distance} meter, batas maksimal {$radius} meter.",
            ]);
        }

        return [
            'distance' => $distance,
            'radius' => $radius,
        ];
    }

    private function storeFaceImage(string $imageData, int $employeeId, string $action): string
    {
        if (! preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $imageData, $matches)) {
            throw ValidationException::withMessages([
                'face_image' => 'Foto wajah tidak valid. Ambil ulang foto dari kamera.',
            ]);
        }

        $binary = base64_decode(substr($imageData, strpos($imageData, ',') + 1), true);

        if ($binary === false || strlen($binary) < 1024) {
            throw ValidationException::withMessages([
                'face_image' => 'Foto wajah gagal dibaca. Ambil ulang foto dari kamera.',
            ]);
        }

        if (! @getimagesizefromstring($binary)) {
            throw ValidationException::withMessages([
                'face_image' => 'File yang dikirim bukan gambar valid.',
            ]);
        }

        $extension = $matches[1] === 'jpg' ? 'jpeg' : $matches[1];
        $path = sprintf(
            'attendance-faces/%s/%s-%s-%s.%s',
            now()->format('Y/m/d'),
            $employeeId,
            $action,
            Str::uuid(),
            $extension
        );

        $storageRoot = storage_path('app/public');

        if (! is_dir($storageRoot) || ! is_writable($storageRoot)) {
            return sprintf(
                'face-capture-hash/%s/%s-%s-%s.%s',
                now()->format('Y/m/d'),
                $employeeId,
                $action,
                hash('sha256', $binary),
                $extension
            );
        }

        $directory = storage_path('app/public/'.dirname($path));

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw ValidationException::withMessages([
                'face_image' => 'Folder penyimpanan foto presensi tidak bisa dibuat.',
            ]);
        }

        $fullPath = storage_path('app/public/'.$path);

        if (file_put_contents($fullPath, $binary) === false) {
            throw ValidationException::withMessages([
                'face_image' => 'Foto wajah gagal disimpan. Coba lagi atau hubungi admin.',
            ]);
        }

        return $path;
    }
    public function koreksi(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'proof' => ['required', 'image', 'max:2048'], // Max 2MB
        ]);

        $employee = auth()->user();
        $proofPath = $request->file('proof')->store('corrections', 'public');

        \App\Models\AttendanceCorrection::create([
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'reason' => $validated['reason'],
            'proof_path' => $proofPath,
            'status' => 'pending',
        ]);

        session()->flash('success', 'Pengajuan koreksi absensi berhasil dikirim. Menunggu persetujuan Admin.');

        return back();
    }
}
