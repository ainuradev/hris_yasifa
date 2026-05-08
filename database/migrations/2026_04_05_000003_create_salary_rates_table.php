<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_rates', function (Blueprint $table) {
            $table->id();
            $table->string('jabatan');
            $table->enum('type', ['guru', 'non_guru']);
            $table->decimal('rate', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_rates');
    }
};
