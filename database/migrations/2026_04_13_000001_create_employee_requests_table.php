<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('name', 150);
            $table->string('nik', 32)->unique();
            $table->string('email', 150)->unique();
            $table->date('date_of_birth');
            $table->enum('type', ['guru', 'non_guru']);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('employment_status', ['aktif', 'nonaktif'])->default('aktif');
            $table->date('contract_end_date')->nullable();
            $table->string('jabatan', 100);
            $table->foreignId('salary_rate_id')->constrained()->cascadeOnDelete();
            $table->string('approval_document_path', 255)->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_requests');
    }
};
