<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('subjects', 'jp_per_week')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->unsignedTinyInteger('jp_per_week')->nullable()->after('name');
            });
        }

        DB::table('subjects')
            ->whereNull('jp_per_week')
            ->update(['jp_per_week' => 4]);

        if (! Schema::hasColumn('teacher_subject_unit', 'jadwal_id')) {
            Schema::table('teacher_subject_unit', function (Blueprint $table) {
                $table->uuid('jadwal_id')->nullable()->after('id');
            });
        }

        DB::table('teacher_subject_unit')
            ->whereNull('jadwal_id')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($row): void {
                DB::table('teacher_subject_unit')
                    ->where('id', $row->id)
                    ->update(['jadwal_id' => (string) Str::uuid()]);
            });

        Schema::table('teacher_subject_unit', function (Blueprint $table) {
            $table->unique('jadwal_id', 'teacher_subject_unit_jadwal_id_unique');
            $table->unique(['teacher_detail_id', 'day_name', 'start_time'], 'teacher_subject_unit_teacher_slot_unique');
            $table->unique(['class_id', 'day_name', 'start_time'], 'teacher_subject_unit_class_slot_unique');
        });

        if (! Schema::hasColumn('attendances', 'jadwal_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->uuid('jadwal_id')->nullable()->after('teacher_subject_unit_id');
            });
        }

        DB::table('attendances')
            ->whereNotNull('teacher_subject_unit_id')
            ->orderBy('id')
            ->get(['id', 'teacher_subject_unit_id'])
            ->each(function ($attendance): void {
                $jadwalId = DB::table('teacher_subject_unit')
                    ->where('id', $attendance->teacher_subject_unit_id)
                    ->value('jadwal_id');

                DB::table('attendances')
                    ->where('id', $attendance->id)
                    ->update(['jadwal_id' => $jadwalId]);
            });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('jadwal_id', 'attendances_jadwal_id_foreign')
                ->references('jadwal_id')
                ->on('teacher_subject_unit')
                ->cascadeOnDelete();

            $table->unique(['employee_id', 'schedule_id', 'jadwal_id'], 'attendances_employee_schedule_jadwal_unique');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_employee_schedule_jadwal_unique');
            $table->dropForeign('attendances_jadwal_id_foreign');
            $table->dropColumn('jadwal_id');
        });

        Schema::table('teacher_subject_unit', function (Blueprint $table) {
            $table->dropUnique('teacher_subject_unit_jadwal_id_unique');
            $table->dropUnique('teacher_subject_unit_teacher_slot_unique');
            $table->dropUnique('teacher_subject_unit_class_slot_unique');
            $table->dropColumn('jadwal_id');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('jp_per_week');
        });
    }
};
