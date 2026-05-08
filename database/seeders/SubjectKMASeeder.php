<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectKMASeeder extends Seeder
{
    /**
     * Reseed mata pelajaran berdasarkan KMA 1503 Tahun 2025
     * per jenjang (MI, MTs, MA) dan bersihkan yang lama.
     */
    public function run(): void
    {
        // Ambil unit IDs
        $units = DB::table('units')->pluck('id', 'jenjang');

        if ($units->isEmpty()) {
            $this->command->error('Units belum ada di database! Jalankan DatabaseSeeder dulu.');
            return;
        }

        // Bersihkan relasi teacher_subject_unit yang pakai subjects (opsional - skip jika mau jaga data)
        // DB::table('teacher_subject_unit')->delete();

        // Hapus semua subjects lama
        DB::table('subjects')->delete();
        DB::statement('ALTER TABLE subjects AUTO_INCREMENT = 1');

        $this->command->info('Subjects lama dihapus. Menyemai ulang dengan KMA 1503 2025...');

        // =====================
        // MI - Madrasah Ibtidaiyah (Kelas 1-6)
        // =====================
        $miSubjects = [
            // PAI & Bahasa Arab
            'Al-Qur\'an Hadis',
            'Akidah Akhlak',
            'Fikih',
            'Sejarah Kebudayaan Islam (SKI)',
            'Bahasa Arab',
            // Umum
            'Pendidikan Pancasila',
            'Bahasa Indonesia',
            'Matematika',
            'Ilmu Pengetahuan Alam dan Sosial (IPAS)',
            'Bahasa Inggris',
            'Pendidikan Jasmani, Olahraga, dan Kesehatan (PJOK)',
            'Seni Budaya dan Prakarya (SBdP)',
            // Muatan Lokal
            'Muatan Lokal',
        ];

        foreach ($miSubjects as $name) {
            DB::table('subjects')->insert([
                'unit_id' => $units['MI'] ?? null,
                'name' => $name,
            ]);
        }
        $this->command->info('✅ ' . count($miSubjects) . ' mapel MI berhasil ditambahkan.');

        // =====================
        // MTs - Madrasah Tsanawiyah (Kelas 7-9)
        // =====================
        $mtsSubjects = [
            // PAI & Bahasa Arab
            'Al-Qur\'an Hadis',
            'Akidah Akhlak',
            'Fikih',
            'Sejarah Kebudayaan Islam (SKI)',
            'Bahasa Arab',
            // Umum
            'Pendidikan Pancasila',
            'Bahasa Indonesia',
            'Matematika',
            'Ilmu Pengetahuan Alam (IPA)',
            'Ilmu Pengetahuan Sosial (IPS)',
            'Bahasa Inggris',
            'Pendidikan Jasmani, Olahraga, dan Kesehatan (PJOK)',
            'Informatika',
            'Seni Budaya',
            'Prakarya',
            // Muatan Lokal
            'Muatan Lokal',
        ];

        foreach ($mtsSubjects as $name) {
            DB::table('subjects')->insert([
                'unit_id' => $units['MTs'] ?? null,
                'name' => $name,
            ]);
        }
        $this->command->info('✅ ' . count($mtsSubjects) . ' mapel MTs berhasil ditambahkan.');

        // =====================
        // MA - Madrasah Aliyah (Kelas 10-12)
        // =====================
        $maSubjects = [
            // PAI & Bahasa Arab (Kelompok A)
            'Al-Qur\'an Hadis',
            'Akidah Akhlak',
            'Fikih',
            'Sejarah Kebudayaan Islam (SKI)',
            'Bahasa Arab',
            // Umum (Kelompok B)
            'Pendidikan Pancasila',
            'Bahasa Indonesia',
            'Matematika',
            'Bahasa Inggris',
            'Sejarah Indonesia',
            'Pendidikan Jasmani, Olahraga, dan Kesehatan (PJOK)',
            'Seni Budaya',
            'Informatika',
            // Pilihan IPA (Kelompok C)
            'Fisika',
            'Kimia',
            'Biologi',
            // Pilihan IPS (Kelompok C)
            'Ekonomi',
            'Sosiologi',
            'Geografi',
            // Pilihan Keagamaan (Kelompok C)
            'Ilmu Tafsir',
            'Ushul Fikih',
            'Ilmu Hadis',
            // Muatan Lokal
            'Muatan Lokal',
        ];

        foreach ($maSubjects as $name) {
            DB::table('subjects')->insert([
                'unit_id' => $units['MA'] ?? null,
                'name' => $name,
            ]);
        }
        $this->command->info('✅ ' . count($maSubjects) . ' mapel MA berhasil ditambahkan.');

        $total = count($miSubjects) + count($mtsSubjects) + count($maSubjects);
        $this->command->info("🎉 Total {$total} mata pelajaran (KMA 1503 2025) berhasil di-seed!");
    }
}
