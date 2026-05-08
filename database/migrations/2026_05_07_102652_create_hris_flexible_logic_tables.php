<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing tables if they exist to avoid collision
        Schema::dropIfExists('payroll_snapshots');
        Schema::dropIfExists('attendance_corrections');
        Schema::dropIfExists('subject_permissions');
        Schema::dropIfExists('subject_unit');
        Schema::dropIfExists('holidays');

        // 1. Tabel Master Hari Libur
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->timestamps();

            $table->unique(['date', 'unit_id'], 'holidays_date_unit_unique');
        });

        // 2. Tabel Pivot Subject Unit (Untuk beda JP per unit)
        Schema::create('subject_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->unsignedTinyInteger('hours_per_week')->default(0);
            $table->timestamps();

            $table->unique(['subject_id', 'unit_id'], 'subject_unit_subject_unit_unique');
        });

        // 3. Tabel Izin Per Jam Pelajaran
        Schema::create('subject_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->onDelete('cascade');
            $table->foreignId('teacher_subject_unit_id')->nullable()->constrained('teacher_subject_unit')->onDelete('cascade');
            $table->uuid('jadwal_id')->nullable();
            $table->date('date');
            $table->text('reason');
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'date'], 'subject_permissions_employee_date_index');
        });

        // 4. Tabel Koreksi Absen Harian
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->onDelete('cascade');
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->onDelete('cascade');
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->text('reason');
            $table->string('proof_path')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'date'], 'attendance_corrections_employee_date_index');
        });

        // 5. Tabel Payroll Snapshots
        Schema::create('payroll_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->unique()->constrained('payrolls')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->decimal('rate_gaji', 15, 2)->default(0);
            $table->decimal('verified_jp_total', 8, 2)->default(0);
            $table->decimal('daily_allowance_rate', 15, 2)->default(0);
            $table->decimal('daily_allowance_total', 15, 2)->default(0);
            $table->json('payload')->nullable(); // Detail tanggal & jam yang dihitung
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_snapshots');
        Schema::dropIfExists('attendance_corrections');
        Schema::dropIfExists('subject_permissions');
        Schema::dropIfExists('subject_unit');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('hris_flexible_logic_tables');
    }
};
