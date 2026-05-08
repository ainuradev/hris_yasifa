<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\SalaryRate;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherDetail;
use App\Models\TeacherSubjectUnit;
use App\Models\Unit;
use App\Services\SchedulePlottingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SchedulePlottingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_teacher_conflict_across_classes(): void
    {
        $service = app(SchedulePlottingService::class);
        $unit = $this->createUnit();
        $teacher = $this->createTeacher($unit, 'teacher-a');
        $subject = $this->createSubject($unit, 'Matematika', 4);
        $classA = $this->createClass($unit, '7A');
        $classB = $this->createClass($unit, '7B');

        TeacherSubjectUnit::create($this->makeRecord($teacher, $classA, $subject, 'Senin', '07:00:00', '07:45:00'));

        try {
            $service->replaceClassSlot(
                $classB,
                'Senin',
                '07:00:00',
                $this->makeRecord($teacher, $classB, $subject, 'Senin', '07:00:00', '07:45:00')
            );

            $this->fail('Teacher conflict should raise a validation exception.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('bentrok', $exception->errors()['schedule'][0]);
        }
    }

    public function test_it_rejects_class_conflict_for_the_same_slot(): void
    {
        $service = app(SchedulePlottingService::class);
        $unit = $this->createUnit();
        $teacherA = $this->createTeacher($unit, 'teacher-a');
        $teacherB = $this->createTeacher($unit, 'teacher-b');
        $subjectA = $this->createSubject($unit, 'Matematika', 4);
        $subjectB = $this->createSubject($unit, 'IPA', 4);
        $class = $this->createClass($unit, '8A');

        TeacherSubjectUnit::create($this->makeRecord($teacherA, $class, $subjectA, 'Selasa', '07:45:00', '08:30:00'));

        try {
            $service->syncTeacherSchedule($teacherB, [
                $this->makeRecord($teacherB, $class, $subjectB, 'Selasa', '07:45:00', '08:30:00'),
            ]);

            $this->fail('Class conflict should raise a validation exception.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('Rombel', $exception->errors()['schedule'][0]);
        }
    }

    public function test_it_rejects_subject_quota_overflow_per_class(): void
    {
        $service = app(SchedulePlottingService::class);
        $unit = $this->createUnit();
        $teacherA = $this->createTeacher($unit, 'teacher-a');
        $teacherB = $this->createTeacher($unit, 'teacher-b');
        $subject = $this->createSubject($unit, 'Bahasa Indonesia', 2);
        $class = $this->createClass($unit, '9A');

        TeacherSubjectUnit::create($this->makeRecord($teacherA, $class, $subject, 'Senin', '07:00:00', '07:45:00'));
        TeacherSubjectUnit::create($this->makeRecord($teacherA, $class, $subject, 'Selasa', '07:45:00', '08:30:00'));

        try {
            $service->syncTeacherSchedule($teacherB, [
                $this->makeRecord($teacherB, $class, $subject, 'Rabu', '08:30:00', '09:15:00'),
            ]);

            $this->fail('JP quota overflow should raise a validation exception.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('kuota', $exception->errors()['schedule'][0]);
        }
    }

    public function test_it_bridges_attendance_with_schedule_uuid(): void
    {
        $unit = $this->createUnit();
        $teacher = $this->createTeacher($unit, 'teacher-a');
        $subject = $this->createSubject($unit, 'SKI', 2);
        $class = $this->createClass($unit, '10A');
        $scheduleA = $this->createSchedule($unit, '2026-05-04');
        $scheduleB = $this->createSchedule($unit, '2026-05-05');

        $session = TeacherSubjectUnit::create(
            $this->makeRecord($teacher, $class, $subject, 'Senin', '09:15:00', '10:00:00')
        );

        $attendanceByNumericId = Attendance::create([
            'employee_id' => $teacher->employee_id,
            'schedule_id' => $scheduleA->id,
            'teacher_subject_unit_id' => $session->id,
            'status' => 'hadir',
        ]);

        $attendanceByUuid = Attendance::create([
            'employee_id' => $teacher->employee_id,
            'schedule_id' => $scheduleB->id,
            'jadwal_id' => $session->jadwal_id,
            'status' => 'izin',
        ]);

        $this->assertSame($session->jadwal_id, $attendanceByNumericId->fresh()->jadwal_id);
        $this->assertSame($session->id, $attendanceByUuid->fresh()->teacher_subject_unit_id);
    }

    private function createUnit(): Unit
    {
        return Unit::create([
            'name' => 'Unit Test',
            'jenjang' => 'MTs',
        ]);
    }

    private function createSubject(Unit $unit, string $name, int $jpPerWeek): Subject
    {
        return Subject::create([
            'unit_id' => $unit->id,
            'name' => $name,
            'jp_per_week' => $jpPerWeek,
        ]);
    }

    private function createTeacher(Unit $unit, string $suffix): TeacherDetail
    {
        $salaryRate = SalaryRate::create([
            'jabatan' => 'Guru ' . $suffix,
            'type' => 'guru',
            'rate' => 45000,
        ]);

        $employee = Employee::create([
            'unit_id' => $unit->id,
            'name' => 'Guru ' . $suffix,
            'nik' => 'NIK-' . $suffix,
            'email' => $suffix . '@example.test',
            'password' => 'password',
            'type' => 'guru',
            'role' => 'karyawan',
            'status' => 'aktif',
        ]);

        return TeacherDetail::create([
            'employee_id' => $employee->id,
            'salary_rate_id' => $salaryRate->id,
            'jabatan' => 'Guru',
        ]);
    }

    private function createClass(Unit $unit, string $name): SchoolClass
    {
        return SchoolClass::create([
            'unit_id' => $unit->id,
            'name' => $name,
            'level' => 7,
            'academic_year' => '2025/2026',
        ]);
    }

    private function createSchedule(Unit $unit, string $date): Schedule
    {
        return Schedule::create([
            'unit_id' => $unit->id,
            'work_date' => $date,
            'check_in_start' => '07:00:00',
            'check_in_end' => '15:00:00',
            'day_type' => 'normal',
        ]);
    }

    private function makeRecord(
        TeacherDetail $teacher,
        SchoolClass $class,
        Subject $subject,
        string $dayName,
        string $startTime,
        string $endTime
    ): array {
        return [
            'teacher_detail_id' => $teacher->id,
            'unit_id' => $class->unit_id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'day_name' => $dayName,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'hours_per_week' => 1,
        ];
    }
}
