<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('name', 50);          // e.g. "Kelas 10 MA"
            $table->unsignedTinyInteger('level')->nullable(); // 7,8,9,10,11,12
            $table->string('major', 50)->nullable(); // e.g. "MIPA", "IPS", "Bahasa"
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('teacher_details')->nullOnDelete();
            $table->string('academic_year', 9)->default('2025/2026'); // e.g. "2025/2026"
            $table->boolean('allow_team_teaching')->default(false);
            $table->timestamps();

            $table->unique(['unit_id', 'name', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
