<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->enum('leave_type', ['tahunan', 'sakit', 'penting', 'melahirkan']);
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedTinyInteger('total_days');
            $table->text('reason');
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('employee_id', 'fk_lr_employee')
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete();

            $table->foreign('reviewed_by', 'fk_lr_reviewed_by')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
