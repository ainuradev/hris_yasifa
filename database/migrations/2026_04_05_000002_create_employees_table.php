<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('nik', 32)->unique();
            $table->string('nuptk', 32)->nullable()->unique();
            $table->string('npk', 32)->nullable();
            $table->string('email', 150)->unique();
            $table->string('phone', 25)->nullable();
            $table->text('address')->nullable();
            $table->string('place_of_birth', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['laki_laki', 'perempuan'])->nullable();
            $table->string('agama', 50)->default('Islam');
            $table->string('nama_ibu_kandung', 150)->nullable();
            $table->enum('status_perkawinan', ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'])->default('Belum Kawin');
            $table->enum('pendidikan_terakhir', ['SD', 'SMP', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'])->nullable();
            $table->year('tahun_lulus')->nullable();
            $table->string('emergency_contact_name', 150)->nullable();
            $table->string('emergency_contact_phone', 25)->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->string('password', 255);
            $table->boolean('must_change_password')->default(false);
            $table->enum('type', ['guru', 'non_guru']);
            $table->enum('status_kepegawaian', ['PNS', 'PPPK', 'GTY', 'PTY', 'Honorer', 'Lainnya'])->default('GTY');
            $table->date('tmt_pegawai')->nullable();
            $table->string('no_sk_pengangkatan', 100)->nullable();
            $table->enum('role', ['admin_pusat', 'admin_unit', 'karyawan']);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->date('contract_end_date')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
