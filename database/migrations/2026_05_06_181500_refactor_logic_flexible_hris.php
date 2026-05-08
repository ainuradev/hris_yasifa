<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('holidays')) {
            Schema::create('holidays', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->date('date');
                $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
                $table->timestamps();

                $table->unique(['date', 'unit_id'], 'holidays_date_unit_unique');
            });
        }

        foreach ([
            ['name' => 'Tahun Baru', 'date' => '2026-01-01'],
            ['name' => 'Idul Fitri Hari 1', 'date' => '2026-03-20'],
            ['name' => 'Idul Fitri Hari 2', 'date' => '2026-03-21'],
            ['name' => 'Hari Buruh', 'date' => '2026-05-01'],
            ['name' => 'Idul Adha', 'date' => '2026-05-21'],
            ['name' => 'Hari Kemerdekaan', 'date' => '2026-08-17'],
            ['name' => 'Hari Natal', 'date' => '2026-12-25'],
        ] as $holiday) {
            DB::table('holidays')->updateOrInsert(
                ['date' => $holiday['date'], 'unit_id' => null],
                ['name' => $holiday['name'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        if (! Schema::hasTable('subject_unit')) {
            Schema::create('subject_unit', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
                $table->unsignedTinyInteger('hours_per_week');
                $table->timestamps();

                $table->unique(['subject_id', 'unit_id'], 'subject_unit_subject_unit_unique');
            });
        }

        DB::table('subjects')
            ->whereNotNull('unit_id')
            ->orderBy('id')
            ->get(['id', 'unit_id', 'jp_per_week'])
            ->each(function ($subject): void {
                DB::table('subject_unit')->updateOrInsert(
                    [
                        'subject_id' => $subject->id,
                        'unit_id' => $subject->unit_id,
                    ],
                    [
                        'hours_per_week' => $subject->jp_per_week ?? 4,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            });

        if (! Schema::hasTable('subject_permissions')) {
            Schema::create('subject_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
                $table->foreignId('teacher_subject_unit_id')->nullable()->constrained('teacher_subject_unit')->cascadeOnDelete();
                $table->uuid('jadwal_id')->nullable();
                $table->date('date');
                $table->text('reason');
                $table->string('status')->default('pending');
                $table->foreignId('reviewed_by')->nullable()->constrained('employees')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('review_notes')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'date'], 'subject_permissions_employee_date_index');
                $table->foreign('jadwal_id', 'subject_permissions_jadwal_id_foreign')
                    ->references('jadwal_id')
                    ->on('teacher_subject_unit')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('attendance_corrections')) {
            Schema::create('attendance_corrections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
                $table->date('correction_date');
                $table->text('reason');
                $table->string('proof_path');
                $table->string('status')->default('pending');
                $table->foreignId('reviewed_by')->nullable()->constrained('employees')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('review_notes')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'correction_date'], 'attendance_corrections_employee_date_index');
            });
        }

        if (! Schema::hasTable('payroll_snapshots')) {
            Schema::create('payroll_snapshots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payroll_id')->unique()->constrained('payrolls')->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
                $table->decimal('rate_gaji', 15, 2)->default(0);
                $table->decimal('verified_jp_total', 10, 2)->default(0);
                $table->decimal('daily_allowance_rate', 15, 2)->default(0);
                $table->decimal('daily_allowance_total', 15, 2)->default(0);
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('audit_trails')) {
            Schema::create('audit_trails', function (Blueprint $table) {
                $table->id();
                $table->foreignId('actor_employee_id')->nullable()->constrained('employees')->nullOnDelete();
                $table->string('action');
                $table->string('auditable_type');
                $table->unsignedBigInteger('auditable_id')->nullable();
                $table->string('description')->nullable();
                $table->json('before_data')->nullable();
                $table->json('after_data')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['auditable_type', 'auditable_id'], 'audit_trails_auditable_index');
            });
        }

        if (! Schema::hasColumn('classes', 'allow_team_teaching')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->boolean('allow_team_teaching')->default(false)->after('academic_year');
            });
        }

        try {
            Schema::table('teacher_subject_unit', function (Blueprint $table) {
                $table->dropUnique('teacher_subject_unit_class_slot_unique');
            });
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('classes', 'allow_team_teaching')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropColumn('allow_team_teaching');
            });
        }

        if (Schema::hasTable('audit_trails')) {
            Schema::dropIfExists('audit_trails');
        }

        if (Schema::hasTable('payroll_snapshots')) {
            Schema::dropIfExists('payroll_snapshots');
        }

        if (Schema::hasTable('attendance_corrections')) {
            Schema::dropIfExists('attendance_corrections');
        }

        if (Schema::hasTable('subject_permissions')) {
            Schema::dropIfExists('subject_permissions');
        }

        if (Schema::hasTable('subject_unit')) {
            Schema::dropIfExists('subject_unit');
        }

        if (Schema::hasTable('holidays')) {
            Schema::dropIfExists('holidays');
        }
    }
};
