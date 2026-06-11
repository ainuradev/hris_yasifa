<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->string('description', 150);
            $table->decimal('amount', 15, 2);
            $table->enum('category', ['tunjangan', 'potongan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};
