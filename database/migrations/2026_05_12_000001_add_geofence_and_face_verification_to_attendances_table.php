<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('check_out_latitude', 10, 7)->nullable()->after('longitude');
            $table->decimal('check_out_longitude', 10, 7)->nullable()->after('check_out_latitude');
            $table->unsignedInteger('check_in_distance_meters')->nullable()->after('check_out_longitude');
            $table->unsignedInteger('check_out_distance_meters')->nullable()->after('check_in_distance_meters');
            $table->string('face_check_in_path')->nullable()->after('check_out_distance_meters');
            $table->string('face_check_out_path')->nullable()->after('face_check_in_path');
            $table->boolean('face_verified')->default(false)->after('face_check_out_path');
            $table->string('attendance_challenge_hash')->nullable()->after('face_verified');
            $table->string('attendance_ip', 45)->nullable()->after('attendance_challenge_hash');
            $table->text('attendance_user_agent')->nullable()->after('attendance_ip');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_out_latitude',
                'check_out_longitude',
                'check_in_distance_meters',
                'check_out_distance_meters',
                'face_check_in_path',
                'face_check_out_path',
                'face_verified',
                'attendance_challenge_hash',
                'attendance_ip',
                'attendance_user_agent',
            ]);
        });
    }
};
