<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('base_salary', 15, 2);
            $table->decimal('total_allowance', 15, 2)->default(0);
            $table->decimal('total_deduction', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2);
            $table->enum('status', ['draft', 'final', 'dibayar'])->default('draft');
            $table->date('paid_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);

            $table->foreign('employee_id', 'fk_pay_employee')
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete();

            $table->foreign('created_by', 'fk_pay_created_by')
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete();

            $table->foreign('updated_by', 'fk_pay_updated_by')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
