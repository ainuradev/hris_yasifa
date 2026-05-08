<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_rate_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_rate_id')->constrained()->cascadeOnDelete();
            $table->decimal('old_rate', 15, 2);
            $table->decimal('new_rate', 15, 2);
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('changed_by');
            $table->timestamp('changed_at');

            $table->foreign('changed_by', 'fk_srh_changed_by')
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_rate_history');
    }
};
