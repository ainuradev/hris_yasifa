<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->nullable()
                ->constrained('units')
                ->nullOnDelete();
            $table->string('name', 100);
            $table->unsignedTinyInteger('jp_per_week')->default(4);

            $table->unique(['unit_id', 'name'], 'subjects_unit_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
