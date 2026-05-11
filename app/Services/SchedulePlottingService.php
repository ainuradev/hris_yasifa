<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherDetail;
use App\Models\TeacherSubjectUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class SchedulePlottingService
{
    public function syncTeacherSchedule(TeacherDetail $teacherDetail, array $records): void
    {
        $ignoreIds = $teacherDetail->teacherSubjectUnits()->pluck('id')->all();

        $this->validateRecords(collect($records), $ignoreIds);

        DB::transaction(function () use ($teacherDetail, $records): void {
            $teacherDetail->teacherSubjectUnits()->delete();

            foreach ($records as $record) {
                $teacherDetail->teacherSubjectUnits()->create($record);
            }
        });
    }

    public function replaceClassSlot(SchoolClass $class, string $dayName, string $startTime, ?array $record): ?TeacherSubjectUnit
    {
        $slotQuery = TeacherSubjectUnit::query()
            ->where('class_id', $class->id)
            ->where('day_name', $dayName)
            ->where('start_time', $startTime);

        $replaceQuery = $slotQuery;

        if ($class->allow_team_teaching && $record !== null) {
            $replaceQuery = (clone $slotQuery)
                ->where('teacher_detail_id', $record['teacher_detail_id'])
                ->where('subject_id', $record['subject_id']);
        }

        $ignoreIds = $replaceQuery->pluck('id')->all();

        if ($record !== null) {
            $this->validateRecords(collect([$record]), $ignoreIds);
        }

        try {
            return DB::transaction(function () use ($class, $slotQuery, $replaceQuery, $record): ?TeacherSubjectUnit {
                if ($record === null || ! $class->allow_team_teaching) {
                    $slotQuery->delete();
                } else {
                    $replaceQuery->delete();
                }

                if ($record === null) {
                    return null;
                }

                return TeacherSubjectUnit::create($record);
            });
        } catch (QueryException $e) {
            if ($e->errorInfo[1] === 1062
                && str_contains($e->errorInfo[2] ?? '', 'teacher_subject_unit_class_slot_unique')) {
                $this->fail('Jam Kelas sudah terisi');
            }

            throw $e;
        }
    }

    public function validateRecords(Collection $records, array $ignoreIds = []): void
    {
        if ($records->isEmpty()) {
            return;
        }

        $classIds = $records->pluck('class_id')->filter()->unique()->values();
        $subjectIds = $records->pluck('subject_id')->filter()->unique()->values();
        $teacherIds = $records->pluck('teacher_detail_id')->filter()->unique()->values();

        $classes = SchoolClass::query()
            ->whereIn('id', $classIds)
            ->get()
            ->keyBy('id');

        $subjects = Subject::query()
            ->whereIn('id', $subjectIds)
            ->get()
            ->keyBy('id');

        $teachers = TeacherDetail::query()
            ->with('employee')
            ->whereIn('id', $teacherIds)
            ->get()
            ->keyBy('id');

        $this->assertCrossUnitConsistency($records, $classes, $subjects, $teachers);
        $this->assertNoDuplicateTeacherSlotInPayload($records, $classes, $teachers);
        $this->assertNoDuplicateClassSlotInPayload($records, $classes, $subjects);
        $this->assertNoTeacherConflictInDatabase($records, $ignoreIds, $classes, $teachers);
        $this->assertNoClassConflictInDatabase($records, $ignoreIds, $classes, $subjects);
        $this->assertSubjectQuota($records, $ignoreIds, $classes, $subjects);
    }

    private function assertCrossUnitConsistency(Collection $records, Collection $classes, Collection $subjects, Collection $teachers): void
    {
        foreach ($records as $record) {
            $class = $classes->get($record['class_id']);
            $subject = $subjects->get($record['subject_id']);
            $teacher = $teachers->get($record['teacher_detail_id']);

            if (! $class || ! $subject || ! $teacher) {
                $this->fail('Data jadwal tidak lengkap. Silakan pilih guru, rombel, dan mata pelajaran yang valid.');
            }

            if ((int) $class->unit_id !== (int) $record['unit_id']) {
                $this->fail("Rombel {$class->name} tidak berada pada unit jadwal yang dipilih.");
            }

            // Guru sekarang bisa mengajar lintas unit (MI ngajar di MA)
            // Jadi pengecekan unit_id guru dihapus.

            if ($subject->unit_id !== null && (int) $subject->unit_id !== (int) $record['unit_id']) {
                $this->fail("Mapel {$subject->name} tidak tersedia untuk unit rombel {$class->name}.");
            }
        }
    }

    private function assertNoDuplicateTeacherSlotInPayload(Collection $records, Collection $classes, Collection $teachers): void
    {
        $duplicates = $records->groupBy(fn (array $record) => implode('|', [
            $record['teacher_detail_id'],
            $record['day_name'],
            $record['start_time'],
        ]))->first(fn (Collection $items) => $items->count() > 1);

        if (! ($duplicates instanceof Collection)) {
            return;
        }

        $record = $duplicates->first();
        $teacherName = $teachers->get($record['teacher_detail_id'])?->employee?->name ?? 'Guru';
        $classNames = $duplicates
            ->map(fn (array $item) => $classes->get($item['class_id'])?->name)
            ->filter()
            ->unique()
            ->implode(', ');

        $this->fail("Guru {$teacherName} tidak bisa dipasang di dua rombel sekaligus pada {$record['day_name']} jam {$record['start_time']} ({$classNames}).");
    }

    private function assertNoDuplicateClassSlotInPayload(Collection $records, Collection $classes, Collection $subjects): void
    {
        $duplicates = $records->groupBy(fn (array $record) => implode('|', [
            $record['class_id'],
            $record['day_name'],
            $record['start_time'],
        ]))->first(fn (Collection $items) => $items->count() > 1);

        if (! ($duplicates instanceof Collection)) {
            return;
        }

        $record = $duplicates->first();
        $className = $classes->get($record['class_id'])?->name ?? 'Rombel';
        if ($classes->get($record['class_id'])?->allow_team_teaching) {
            return;
        }
        $subjectNames = $duplicates
            ->map(fn (array $item) => $subjects->get($item['subject_id'])?->name)
            ->filter()
            ->unique()
            ->implode(', ');

        $this->fail("Rombel {$className} sudah memiliki slot {$record['day_name']} jam {$record['start_time']} untuk mapel {$subjectNames}.");
    }

    private function assertNoTeacherConflictInDatabase(
        Collection $records,
        array $ignoreIds,
        Collection $classes,
        Collection $teachers
    ): void {
        foreach ($records as $record) {
            $conflict = TeacherSubjectUnit::query()
                ->with(['class', 'teacherDetail.employee'])
                ->where('teacher_detail_id', $record['teacher_detail_id'])
                ->where('day_name', $record['day_name'])
                ->where('start_time', $record['start_time'])
                ->when($ignoreIds !== [], fn ($query) => $query->whereNotIn('id', $ignoreIds))
                ->first();

            if (! $conflict) {
                continue;
            }

            $teacherName = $teachers->get($record['teacher_detail_id'])?->employee?->name ?? 'Guru';
            $targetClass = $classes->get($record['class_id'])?->name ?? 'rombel lain';
            $conflictClass = $conflict->class?->name ?? 'rombel lain';

            $this->fail("Guru {$teacherName} bentrok pada {$record['day_name']} jam {$record['start_time']}. Slot {$targetClass} sudah terpakai di {$conflictClass}.");
        }
    }

    private function assertNoClassConflictInDatabase(
        Collection $records,
        array $ignoreIds,
        Collection $classes,
        Collection $subjects
    ): void {
        foreach ($records as $record) {
            $class = $classes->get($record['class_id']);
            if ($class?->allow_team_teaching) {
                continue;
            }

            $conflict = TeacherSubjectUnit::query()
                ->with(['class', 'subject'])
                ->where('class_id', $record['class_id'])
                ->where('day_name', $record['day_name'])
                ->where('start_time', $record['start_time'])
                ->when($ignoreIds !== [], fn ($query) => $query->whereNotIn('id', $ignoreIds))
                ->first();

            if (! $conflict) {
                continue;
            }

            $className = $classes->get($record['class_id'])?->name ?? 'Rombel';
            $subjectName = $subjects->get($record['subject_id'])?->name ?? 'mapel lain';
            $conflictSubject = $conflict->subject?->name ?? 'mapel lain';

            $this->fail("Rombel {$className} bentrok pada {$record['day_name']} jam {$record['start_time']}. Slot {$subjectName} bertabrakan dengan {$conflictSubject}.");
        }
    }

    private function assertSubjectQuota(Collection $records, array $ignoreIds, Collection $classes, Collection $subjects): void
    {
        $grouped = $records->groupBy(fn (array $record) => $record['class_id'].'|'.$record['subject_id']);

        foreach ($grouped as $group) {
            $record = $group->first();
            $subject = $subjects->get($record['subject_id']);
            $class = $classes->get($record['class_id']);

            if (! $subject || ! $class) {
                continue;
            }

            if ($subject->jp_per_week === null) {
                $this->fail("Beban JP mapel {$subject->name} belum diatur. Isi JP per minggu di master mata pelajaran sebelum plotting jadwal.");
            }

            $quota = DB::table('subject_unit')
                ->where('subject_id', $subject->id)
                ->where('unit_id', $class->unit_id)
                ->value('hours_per_week');

            $quota ??= $subject->jp_per_week;

            $existingHours = TeacherSubjectUnit::query()
                ->where('class_id', $record['class_id'])
                ->where('subject_id', $record['subject_id'])
                ->when($ignoreIds !== [], fn ($query) => $query->whereNotIn('id', $ignoreIds))
                ->sum('hours_per_week');

            $incomingHours = $group->sum('hours_per_week');
            $totalHours = (int) $existingHours + (int) $incomingHours;

            if ($totalHours > (int) $quota) {
                $this->fail("JP mapel {$subject->name} untuk rombel {$class->name} melebihi kuota. Maksimal {$quota} JP per minggu, sedang dicoba {$totalHours} JP.");
            }
        }
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages([
            'schedule' => $message,
        ]);
    }
}
