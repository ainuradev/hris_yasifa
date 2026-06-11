<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->string('title', 150);
            $table->text('content');
            $table->enum('category', ['umum', 'penggajian', 'absensi', 'kegiatan']);
            $table->boolean('is_global')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('unit_id', 'fk_ann_unit')
                ->references('id')
                ->on('units')
                ->nullOnDelete();

            $table->foreign('created_by', 'fk_ann_created_by')
                ->references('id')
                ->on('employees')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
