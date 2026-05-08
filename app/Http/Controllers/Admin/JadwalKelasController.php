<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherDetail;
use App\Models\Unit;
use App\Services\SchedulePlottingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JadwalKelasController extends Controller
{
    /** Level ranges per jenjang */
    public const LEVEL_RANGES = [
        'MI' => ['min' => 1, 'max' => 6],
        'MTs' => ['min' => 7, 'max' => 9],
        'MA' => ['min' => 10, 'max' => 12],
    ];

    public function index(Request $request): View
    {
        $admin = $request->user();
        $selectedUnitId = $admin->isAdminPusat()
            ? ($request->filled('unit_id') ? $request->integer('unit_id') : null)
            : (int) $admin->unit_id;

        $viewType = $request->get('view_type', 'kelas'); // 'kelas' or 'guru'

        $classes = collect();
        $teacherDetails = collect();

        if ($viewType === 'kelas') {
            $classes = SchoolClass::query()
                ->with(['unit', 'homeroomTeacher.employee'])
                ->withCount('teacherSubjectUnits')
                ->when($selectedUnitId, fn ($q) => $q->where('unit_id', $selectedUnitId))
                ->when($request->filled('search'), function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                })
                ->orderByRaw("FIELD(unit_id, " . implode(',', Unit::orderBy('name')->pluck('id')->toArray()) . ")")
                ->orderBy('level')
                ->orderBy('name')
                ->paginate(20, ['*'], 'class_page')
                ->withQueryString();
        } else {
            $teacherDetails = TeacherDetail::query()
                ->with(['employee.unit', 'teacherSubjectUnits.subject'])
                ->whereHas('employee', function ($q) use ($selectedUnitId) {
                    $q->where('status', 'aktif')
                      ->when($selectedUnitId, fn($q2) => $q2->where('unit_id', $selectedUnitId));
                })
                ->when($request->filled('search'), function ($q) use ($request) {
                    $q->whereHas('employee', fn($q2) => $q2->where('name', 'like', '%' . $request->search . '%')->orWhere('nik', 'like', '%' . $request->search . '%'));
                })
                ->paginate(20, ['*'], 'teacher_page')
                ->withQueryString();
        }

        $units = $admin->isAdminPusat()
            ? Unit::orderBy('name')->get()
            : Unit::whereKey($admin->unit_id)->get();

        return view('admin.jadwal-kelas.index', compact('classes', 'teacherDetails', 'units', 'selectedUnitId', 'viewType'));
    }

    public function create(Request $request): View
    {
        $admin = $request->user();
        $units = $admin->isAdminPusat()
            ? Unit::orderBy('name')->get()
            : Unit::whereKey($admin->unit_id)->get();

        $teachers = TeacherDetail::with('employee')->whereHas('employee', function ($q) use ($admin) {
            if (! $admin->isAdminPusat()) {
                $q->where('unit_id', $admin->unit_id);
            }
        })->doesntHave('homeroomClass')->get();

        $levelRanges = self::LEVEL_RANGES;

        return view('admin.jadwal-kelas.create', compact('units', 'teachers', 'levelRanges'));
    }

    public function store(Request $request): RedirectResponse
    {
        $admin = $request->user();
        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'name' => ['required', 'string', 'max:255'],
            'level' => ['nullable', 'integer', 'min:1', 'max:12'],
            'major' => ['nullable', 'string', 'max:100'],
            'academic_year' => ['required', 'string', 'max:20'],
            'allow_team_teaching' => ['nullable', 'boolean'],
            'homeroom_teacher_id' => ['nullable', 'exists:teacher_details,id', Rule::unique('classes', 'homeroom_teacher_id')],
        ]);

        if (! $admin->isAdminPusat()) {
            $validated['unit_id'] = $admin->unit_id;
        }
        $validated['allow_team_teaching'] = $request->boolean('allow_team_teaching');

        $class = SchoolClass::create($validated);

        if ($class->homeroom_teacher_id) {
            $this->handleHomeroomAllowance(null, $class->homeroom_teacher_id);
        }

        return redirect()->route('admin.rombel.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Request $request, SchoolClass $jadwal_kela): View
    {
        $admin = $request->user();
        $this->authorizeAccess($jadwal_kela, $admin);

        $units = $admin->isAdminPusat()
            ? Unit::orderBy('name')->get()
            : Unit::whereKey($admin->unit_id)->get();

        $teachers = TeacherDetail::with('employee')->whereHas('employee', function ($q) use ($jadwal_kela) {
            $q->where('unit_id', $jadwal_kela->unit_id);
        })->where(function ($query) use ($jadwal_kela) {
            $query->doesntHave('homeroomClass')
                ->orWhere('id', $jadwal_kela->homeroom_teacher_id);
        })->get();

        $levelRanges = self::LEVEL_RANGES;

        return view('admin.jadwal-kelas.edit', [
            'class' => $jadwal_kela,
            'units' => $units,
            'teachers' => $teachers,
            'levelRanges' => $levelRanges,
        ]);
    }

    public function update(Request $request, SchoolClass $jadwal_kela): RedirectResponse
    {
        $admin = $request->user();
        $this->authorizeAccess($jadwal_kela, $admin);

        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'name' => ['required', 'string', 'max:255'],
            'level' => ['nullable', 'integer', 'min:1', 'max:12'],
            'major' => ['nullable', 'string', 'max:100'],
            'academic_year' => ['required', 'string', 'max:20'],
            'allow_team_teaching' => ['nullable', 'boolean'],
            'homeroom_teacher_id' => ['nullable', 'exists:teacher_details,id', Rule::unique('classes', 'homeroom_teacher_id')->ignore($jadwal_kela->id)],
        ]);

        if (! $admin->isAdminPusat()) {
            $validated['unit_id'] = $admin->unit_id;
        }
        $validated['allow_team_teaching'] = $request->boolean('allow_team_teaching');

        $oldHomeroomTeacherId = $jadwal_kela->homeroom_teacher_id;
        $jadwal_kela->update($validated);
        $this->handleHomeroomAllowance($oldHomeroomTeacherId, $jadwal_kela->homeroom_teacher_id);

        return redirect()->route('admin.rombel.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Request $request, SchoolClass $jadwal_kela): RedirectResponse
    {
        $admin = $request->user();
        $this->authorizeAccess($jadwal_kela, $admin);
        $jadwal_kela->delete();

        return redirect()->route('admin.rombel.index')->with('success', 'Kelas berhasil dihapus.');
    }

    public function show(Request $request, SchoolClass $jadwal_kela): View
    {
        $admin = $request->user();
        $this->authorizeAccess($jadwal_kela, $admin);

        $jadwal_kela->load(['teacherSubjectUnits.subject', 'teacherSubjectUnits.teacherDetail.employee', 'unit', 'homeroomTeacher.employee']);

        $days = JadwalGuruController::DAYS;
        $jpTimes = JadwalGuruController::JP_TIMES;

        $timetable = [];
        foreach ($jadwal_kela->teacherSubjectUnits as $session) {
            $startTime = is_object($session->start_time)
                ? $session->start_time->format('H:i:s')
                : $session->start_time;

            $jpNum = collect($jpTimes)->search(fn ($t) => $t['start'] === $startTime);

            if ($jpNum !== false) {
                $timetable[$jpNum][$session->day_name] ??= collect();
                $timetable[$jpNum][$session->day_name]->push($session);
            }
        }

        $subjects = Subject::query()
            ->where(function ($query) use ($jadwal_kela): void {
                $query->whereNull('unit_id')
                    ->orWhere('unit_id', $jadwal_kela->unit_id);
            })
            ->orderBy('name')
            ->get();

        $teachers = TeacherDetail::with(['employee.subjects'])
            ->whereHas('employee', fn ($q) => $q->where('unit_id', $jadwal_kela->unit_id)->where('status', 'aktif'))
            ->get()
            ->map(fn ($td) => [
                'id' => $td->id,
                'name' => $td->employee->name,
                'subject_ids' => $td->employee->subjects->pluck('id')->toArray(),
            ]);

        $stats = [
            'total_hours' => $jadwal_kela->teacherSubjectUnits->sum('hours_per_week'),
            'subject_count' => $jadwal_kela->teacherSubjectUnits->unique('subject_id')->count(),
            'teacher_count' => $jadwal_kela->teacherSubjectUnits->unique('teacher_detail_id')->count(),
        ];

        return view('admin.jadwal-kelas.show', [
            'class' => $jadwal_kela,
            'days' => $days,
            'jpTimes' => $jpTimes,
            'timetable' => $timetable,
            'stats' => $stats,
            'subjects' => $subjects,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Save / clear a single JP slot from the class timetable (AJAX JSON).
     */
    public function saveSlot(
        Request $request,
        SchoolClass $jadwal_kela,
        SchedulePlottingService $schedulePlottingService
    ): JsonResponse
    {
        $admin = $request->user();
        $this->authorizeAccess($jadwal_kela, $admin);

        $validated = $request->validate([
            'jp' => ['required', 'integer'],
            'day' => ['required', 'string', 'in:' . implode(',', JadwalGuruController::DAYS)],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'teacher_detail_id' => ['nullable', 'exists:teacher_details,id'],
        ]);

        $jpTimes = JadwalGuruController::JP_TIMES;
        $jp = (int) $validated['jp'];

        if (! isset($jpTimes[$jp])) {
            return response()->json(['message' => 'JP tidak valid.'], 422);
        }

        $time = $jpTimes[$jp];

        if (filled($validated['subject_id'] ?? null) xor filled($validated['teacher_detail_id'] ?? null)) {
            throw ValidationException::withMessages([
                'slot' => 'Guru dan mata pelajaran harus dipilih bersamaan.',
            ]);
        }

        $record = null;

        if (! empty($validated['subject_id']) && ! empty($validated['teacher_detail_id'])) {
            $teacherDetail = TeacherDetail::with('employee')->findOrFail($validated['teacher_detail_id']);
            $subject = Subject::findOrFail($validated['subject_id']);

            if (! $admin->isAdminPusat() && (int) $teacherDetail->employee->unit_id !== (int) $admin->unit_id) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }

            if ((int) $teacherDetail->employee->unit_id !== (int) $jadwal_kela->unit_id) {
                throw ValidationException::withMessages([
                    'slot' => 'Guru harus berasal dari unit yang sama dengan rombel.',
                ]);
            }

            if ($subject->unit_id !== null && (int) $subject->unit_id !== (int) $jadwal_kela->unit_id) {
                throw ValidationException::withMessages([
                    'slot' => 'Mata pelajaran tidak tersedia untuk unit rombel ini.',
                ]);
            }

            $record = [
                'teacher_detail_id' => $teacherDetail->id,
                'unit_id' => $jadwal_kela->unit_id,
                'class_id' => $jadwal_kela->id,
                'subject_id' => $subject->id,
                'day_name' => $validated['day'],
                'start_time' => $time['start'],
                'end_time' => $time['end'],
                'hours_per_week' => 1,
            ];
        }

        $tsu = $schedulePlottingService->replaceClassSlot(
            $jadwal_kela,
            $validated['day'],
            $time['start'],
            $record
        );

        if ($tsu) {
            $tsu->load(['subject', 'teacherDetail.employee']);

            return response()->json([
                'message' => 'Slot berhasil disimpan.',
                'subject_name' => $tsu->subject->name,
                'teacher_name' => $tsu->teacherDetail->employee->name,
                'tsu_id' => $tsu->id,
                'jadwal_id' => $tsu->jadwal_id,
            ]);
        }

        return response()->json(['message' => 'Slot berhasil dihapus.']);
    }

    /**
     * Return JSON list of teachers prioritised by subject competency.
     */
    public function teachersBySubject(Request $request, SchoolClass $jadwal_kela): JsonResponse
    {
        $subjectId = $request->integer('subject_id');

        $preferred = TeacherDetail::with('employee')
            ->whereHas('employee', fn ($q) => $q
                ->where('unit_id', $jadwal_kela->unit_id)
                ->where('status', 'aktif')
                ->whereHas('subjects', fn ($q2) => $q2->where('subjects.id', $subjectId))
            )
            ->get()
            ->map(fn ($td) => ['id' => $td->id, 'name' => $td->employee->name . ' *', 'preferred' => true]);

        $preferredIds = $preferred->pluck('id');

        $others = TeacherDetail::with('employee')
            ->whereNotIn('id', $preferredIds)
            ->whereHas('employee', fn ($q) => $q
                ->where('unit_id', $jadwal_kela->unit_id)
                ->where('status', 'aktif')
            )
            ->get()
            ->map(fn ($td) => ['id' => $td->id, 'name' => $td->employee->name, 'preferred' => false]);

        return response()->json($preferred->merge($others)->values());
    }

    private function authorizeAccess(SchoolClass $class, $admin): void
    {
        if (! $admin->isAdminPusat() && (int) $class->unit_id !== (int) $admin->unit_id) {
            abort(403);
        }
    }

    private function handleHomeroomAllowance(?int $oldTeacherId, ?int $newTeacherId): void
    {
        if ($oldTeacherId === $newTeacherId) {
            return;
        }

        $allowanceComponent = \App\Models\SalaryComponent::firstOrCreate(
            ['name' => 'Tunjangan Wali Kelas'],
            ['type' => 'tunjangan']
        );

        if ($oldTeacherId) {
            $oldTeacher = \App\Models\TeacherDetail::find($oldTeacherId);
            $oldTeacher?->employee->salaryComponents()
                ->where('salary_component_id', $allowanceComponent->id)
                ->delete();
        }

        if ($newTeacherId) {
            $newTeacher = \App\Models\TeacherDetail::find($newTeacherId);
            $newTeacher?->employee->salaryComponents()->updateOrCreate(
                ['salary_component_id' => $allowanceComponent->id],
                ['amount' => $allowanceComponent->default_amount ?? 0, 'description' => 'Tunjangan Wali Kelas otomatis']
            );
        }
    }
}
