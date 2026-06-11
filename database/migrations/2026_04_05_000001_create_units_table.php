<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('jenjang', ['MI', 'MTs', 'MA']);
            $table->string('kepala_unit', 150)->nullable();
            $table->timestamps();

            $table->unique('jenjang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
