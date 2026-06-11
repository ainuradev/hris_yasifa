<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_subject_unit_id')->nullable()->constrained('teacher_subject_unit')->cascadeOnDelete();
            $table->uuid('jadwal_id')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();
            $table->unsignedInteger('check_in_distance_meters')->nullable();
            $table->unsignedInteger('check_out_distance_meters')->nullable();
            $table->string('face_check_in_path', 255)->nullable();
            $table->string('face_check_out_path', 255)->nullable();
            $table->boolean('face_verified')->default(false);
            $table->string('attendance_challenge_hash', 255)->nullable();
            $table->string('attendance_ip', 45)->nullable();
            $table->text('attendance_user_agent')->nullable();
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpa']);
            $table->string('notes', 255)->nullable();
            
            // Approval fields
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            $table->timestamps();

            $table->foreign('employee_id', 'fk_att_employee')
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete();

            $table->foreign('jadwal_id', 'attendances_jadwal_id_foreign')
                ->references('jadwal_id')
                ->on('teacher_subject_unit')
                ->cascadeOnDelete();
            
            $table->index('employee_id', 'attendances_employee_id_index');
            $table->unique(
                ['employee_id', 'schedule_id', 'teacher_subject_unit_id'],
                'attendances_employee_schedule_session_unique'
            );
            $table->unique(['employee_id', 'schedule_id', 'jadwal_id'], 'attendances_employee_schedule_jadwal_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
