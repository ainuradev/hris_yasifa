<?php

namespace App\Enums;

enum AnnouncementCategory: string
{
    case Umum = 'umum';
    case Penggajian = 'penggajian';
    case Absensi = 'absensi';
    case Kegiatan = 'kegiatan';
}
