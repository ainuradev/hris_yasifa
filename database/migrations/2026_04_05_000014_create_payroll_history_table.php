<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->string('field_changed');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->unsignedBigInteger('changed_by');
            $table->timestamp('changed_at');

            $table->foreign('changed_by', 'fk_ph_changed_by')
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_history');
    }
};
