<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_subject_unit', function (Blueprint $table) {
            $table->id();
            $table->uuid('jadwal_id')->nullable();
            $table->foreignId('teacher_detail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->unsignedTinyInteger('hours_per_week');
            $table->enum('day_name', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();

            $table->unique('jadwal_id', 'teacher_subject_unit_jadwal_id_unique');
            $table->unique(['teacher_detail_id', 'day_name', 'start_time'], 'teacher_subject_unit_teacher_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_subject_unit');
    }
};
