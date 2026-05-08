<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('salary_rate_id')->constrained()->cascadeOnDelete();
            $table->string('jabatan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_details');
    }
};
