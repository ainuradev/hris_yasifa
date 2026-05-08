<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherDetail;
use App\Models\Unit;
use App\Services\SchedulePlottingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JadwalGuruController extends Controller
{
    // Mapping JP number → start/end time
    public const JP_TIMES = [
        1 => ['start' => '07:00:00', 'end' => '07:45:00', 'label' => '07:00–07:45'],
        2 => ['start' => '07:45:00', 'end' => '08:30:00', 'label' => '07:45–08:30'],
        3 => ['start' => '08:30:00', 'end' => '09:15:00', 'label' => '08:30–09:15'],
        4 => ['start' => '09:15:00', 'end' => '10:00:00', 'label' => '09:15–10:00'],
        5 => ['start' => '10:15:00', 'end' => '11:00:00', 'label' => '10:15–11:00'],
        6 => ['start' => '11:00:00', 'end' => '11:45:00', 'label' => '11:00–11:45'],
        7 => ['start' => '13:00:00', 'end' => '13:45:00', 'label' => '13:00–13:45'],
    ];

    public const DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function index(Request $request): View
    {
        $admin = $request->user();
        $selectedUnitId = $admin->isAdminPusat()
            ? ($request->filled('unit_id') ? $request->integer('unit_id') : null)
            : (int) $admin->unit_id;

        $teacherDetails = TeacherDetail::query()
            ->with([
                'employee.unit',
                'teacherSubjectUnits.subject',
            ])
            ->whereHas('employee', function (Builder $q) use ($selectedUnitId) {
                $q->when($selectedUnitId, fn ($q) => $q->where('unit_id', $selectedUnitId));
            })
            ->when($request->filled('search'), function (Builder $q) use ($request) {
                $search = $request->string('search')->value();
                $q->whereHas('employee', fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%"));
            })
            ->paginate(20)
            ->withQueryString();

        $units = $admin->isAdminPusat()
            ? Unit::orderBy('name')->get()
            : Unit::whereKey($admin->unit_id)->get();

        return view('admin.jadwal.index', compact('teacherDetails', 'units', 'selectedUnitId'));
    }

    public function show(Request $request, TeacherDetail $teacherDetail): View
    {
        $admin = $request->user();
        $this->authorizeTeacherDetailAccess($teacherDetail, $admin);

        $teacherDetail->load(['employee.unit', 'teacherSubjectUnits.class', 'teacherSubjectUnits.subject']);

        $days = self::DAYS;
        $jpTimes = self::JP_TIMES;

        // Granular matrix: [jpNum][dayName] => collect of sessions (usually 1, unless team teaching elsewhere)
        $timetable = [];
        foreach ($teacherDetail->teacherSubjectUnits as $tsu) {
            $startTime = is_object($tsu->start_time) ? $tsu->start_time->format('H:i:s') : $tsu->start_time;
            $jpNum = collect($jpTimes)->search(fn($t) => $t['start'] === $startTime);

            if ($jpNum !== false) {
                $timetable[$jpNum][$tsu->day_name] ??= collect();
                $timetable[$jpNum][$tsu->day_name]->push($tsu);
            }
        }

        $teacherUnitId = (int) $teacherDetail->employee?->unit_id;

        $classes = SchoolClass::query()
            ->where('unit_id', $teacherUnitId)
            ->orderBy('level')->orderBy('name')->get();

        $subjects = Subject::query()
            ->where(fn($q) => $q->whereNull('unit_id')->orWhere('unit_id', $teacherUnitId))
            ->orderBy('name')->get();

        return view('admin.jadwal.show', [
            'teacherDetail' => $teacherDetail,
            'days' => $days,
            'jpTimes' => $jpTimes,
            'timetable' => $timetable,
            'classes' => $classes,
            'subjects' => $subjects,
        ]);
    }

    /**
     * AJAX Save slot for Teacher perspective
     */
    public function saveSlot(
        Request $request,
        TeacherDetail $teacherDetail,
        SchedulePlottingService $schedulePlottingService
    ): \Illuminate\Http\JsonResponse
    {
        $admin = $request->user();
        $this->authorizeTeacherDetailAccess($teacherDetail, $admin);

        $validated = $request->validate([
            'jp' => ['required', 'integer'],
            'day' => ['required', 'string', 'in:' . implode(',', self::DAYS)],
            'class_id' => ['nullable', 'exists:classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
        ]);

        $jpTimes = self::JP_TIMES;
        $jp = (int) $validated['jp'];
        $time = $jpTimes[$jp] ?? null;

        if (!$time) return response()->json(['message' => 'JP tidak valid.'], 422);

        if (filled($validated['class_id'] ?? null) xor filled($validated['subject_id'] ?? null)) {
            return response()->json(['message' => 'Rombel dan Mapel harus dipilih bersamaan.'], 422);
        }

        $record = null;
        if (!empty($validated['class_id']) && !empty($validated['subject_id'])) {
            $class = SchoolClass::findOrFail($validated['class_id']);
            $subject = Subject::findOrFail($validated['subject_id']);

            $records = [[
                'teacher_detail_id' => $teacherDetail->id,
                'unit_id' => $class->unit_id,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'day_name' => $validated['day'],
                'start_time' => $time['start'],
                'end_time' => $time['end'],
                'hours_per_week' => 1,
            ]];

            // We use syncTeacherSchedule or a more specific method?
            // Actually, replaceClassSlot is for CLASS perspective.
            // For Teacher, we want to replace the TEACHER'S slot at that time.
            
            // Let's create a temporary manual replacement logic or a new service method.
            // Since we want to replace the teacher's slot:
            try {
                DB::transaction(function() use ($teacherDetail, $validated, $time, $records) {
                    \App\Models\TeacherSubjectUnit::where('teacher_detail_id', $teacherDetail->id)
                        ->where('day_name', $validated['day'])
                        ->where('start_time', $time['start'])
                        ->delete();
                    
                    if ($records[0]['class_id']) {
                        \App\Models\TeacherSubjectUnit::create($records[0]);
                    }
                });
                return response()->json(['message' => 'Jadwal berhasil diperbarui.']);
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        } else {
            // Delete slot
            \App\Models\TeacherSubjectUnit::where('teacher_detail_id', $teacherDetail->id)
                ->where('day_name', $validated['day'])
                ->where('start_time', $time['start'])
                ->delete();
            return response()->json(['message' => 'Slot berhasil dihapus.']);
        }
    }

    public function save(
        Request $request,
        TeacherDetail $teacherDetail,
        SchedulePlottingService $schedulePlottingService
    ): RedirectResponse
    {
        $admin = $request->user();
        $this->authorizeTeacherDetailAccess($teacherDetail, $admin);

        $request->validate([
            'schedule' => ['nullable', 'array'],
            'schedule.*.class_id' => ['nullable', 'exists:classes,id'],
            'schedule.*.subject_id' => ['nullable', 'exists:subjects,id'],
            'schedule.*.days' => ['nullable', 'array'],
            'schedule.*.days.*' => ['string', 'in:' . implode(',', self::DAYS)],
        ]);

        $classes = SchoolClass::query()
            ->where('unit_id', $teacherDetail->employee?->unit_id)
            ->get()
            ->keyBy('id');

        $subjects = Subject::query()
            ->where(function (Builder $query) use ($teacherDetail): void {
                $query->whereNull('unit_id')
                    ->orWhere('unit_id', $teacherDetail->employee?->unit_id);
            })
            ->get()
            ->keyBy('id');

        $records = [];

        foreach (self::JP_TIMES as $jpNum => $time) {
            $jpData = $request->input("schedule.{$jpNum}", []);
            $classId = isset($jpData['class_id']) ? (int) $jpData['class_id'] : null;
            $subjectId = isset($jpData['subject_id']) ? (int) $jpData['subject_id'] : null;
            $days = collect($jpData['days'] ?? [])->filter()->unique()->values()->all();

            if (! $classId && ! $subjectId && $days === []) {
                continue;
            }

            if (! $classId || ! $subjectId || $days === []) {
                throw ValidationException::withMessages([
                    'schedule' => "JP {$jpNum} harus memiliki rombel, mata pelajaran, dan minimal satu hari.",
                ]);
            }

            $class = $classes->get($classId);
            $subject = $subjects->get($subjectId);

            if (! $class || ! $subject) {
                throw ValidationException::withMessages([
                    'schedule' => "Pilihan rombel atau mapel pada JP {$jpNum} tidak valid untuk unit guru ini.",
                ]);
            }

            foreach ($days as $day) {
                $records[] = [
                    'teacher_detail_id' => $teacherDetail->id,
                    'unit_id' => $class->unit_id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'day_name' => $day,
                    'start_time' => $time['start'],
                    'end_time' => $time['end'],
                    'hours_per_week' => 1,
                ];
            }
        }

        $schedulePlottingService->syncTeacherSchedule($teacherDetail, $records);

        session()->flash('success', 'Jadwal mengajar berhasil disimpan!');
        return redirect()->route('admin.rombel.guru.show', $teacherDetail)->with('success', 'Jadwal mengajar berhasil disimpan!');
    }

    private function authorizeTeacherDetailAccess(TeacherDetail $teacherDetail, $admin): void
    {
        $teacherDetail->loadMissing('employee');
        if ($admin->isAdminPusat()) return;
        abort_if((int) $teacherDetail->employee?->unit_id !== (int) $admin->unit_id, 403);
    }
}
