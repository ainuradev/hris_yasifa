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
            $table->string('name');
            $table->string('nik')->unique();
            $table->string('nuptk')->nullable()->unique();
            $table->string('npk')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['laki_laki', 'perempuan'])->nullable();
            $table->string('agama')->default('Islam');
            $table->string('nama_ibu_kandung')->nullable();
            $table->enum('status_perkawinan', ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'])->default('Belum Kawin');
            $table->enum('pendidikan_terakhir', ['SD', 'SMP', 'SMA/Sederajat', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'])->nullable();
            $table->year('tahun_lulus')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('password');
            $table->boolean('must_change_password')->default(false);
            $table->enum('type', ['guru', 'non_guru']);
            $table->enum('status_kepegawaian', ['PNS', 'PPPK', 'GTY', 'PTY', 'Honorer', 'Lainnya'])->default('GTY');
            $table->date('tmt_pegawai')->nullable();
            $table->string('no_sk_pengangkatan')->nullable();
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
