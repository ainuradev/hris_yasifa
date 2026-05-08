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
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpa']);
            $table->string('notes')->nullable();
            
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
            
            $table->index('employee_id', 'attendances_employee_id_index');
            $table->unique(
                ['employee_id', 'schedule_id', 'teacher_subject_unit_id'],
                'attendances_employee_schedule_session_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
