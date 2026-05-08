<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->time('check_in_start');
            $table->time('check_in_end');
            $table->enum('day_type', ['normal', 'libur', 'setengah_hari']);
            $table->timestamps();

            $table->unique(['unit_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
