<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // Drop the old global unique constraint on name
            $table->dropUnique('subjects_name_unique');

            // Add composite unique: same name can exist per unit (or globally if unit_id is null)
            // We allow duplicate names only across different units
            $table->unique(['unit_id', 'name'], 'subjects_unit_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique('subjects_unit_name_unique');
            $table->unique('name', 'subjects_name_unique');
        });
    }
};
