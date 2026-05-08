<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            // KELOMPOK MATA PELAJARAN AGAMA ISLAM DAN BAHASA ARAB
            'Al-Qur\'an Hadis',
            'Akidah Akhlak',
            'Fikih',
            'Sejarah Kebudayaan Islam (SKI)',
            'Bahasa Arab',

            // KELOMPOK MATA PELAJARAN UMUM (MI, MTs, MA)
            'Pendidikan Pancasila',
            'Bahasa Indonesia',
            'Matematika',
            
            // SAINS DAN SOSIAL
            'Ilmu Pengetahuan Alam dan Sosial (IPAS)', // MI
            'Ilmu Pengetahuan Alam (IPA)', // MTs/MA
            'Ilmu Pengetahuan Sosial (IPS)', // MTs/MA
            
            // BAHASA INGGRIS DAN KETERAMPILAN
            'Bahasa Inggris',
            'Bahasa Inggris Tingkat Lanjut',
            'Pendidikan Jasmani, Olahraga, dan Kesehatan (PJOK)',
            'Informatika',
            
            // SENI DAN PRAKARYA
            'Seni Budaya',
            'Seni Rupa',
            'Seni Musik',
            'Seni Tari',
            'Seni Teater',
            'Prakarya dan Kewirausahaan',

            // MATA PELAJARAN PILIHAN / PEMINATAN MA (IPA/IPS/BAHASA/AGAMA)
            'Biologi',
            'Fisika',
            'Kimia',
            'Sosiologi',
            'Ekonomi',
            'Geografi',
            'Antropologi',
            'Ilmu Tafsir',
            'Ilmu Hadis',
            'Ushul Fikih',

            // MUATAN LOKAL
            'Muatan Lokal',
            'Tahfiz Al-Qur\'an',
            'Bahasa Daerah',
        ];

        foreach ($subjects as $subject) {
            DB::table('subjects')->updateOrInsert(
                ['name' => $subject]
            );
        }
    }
}
