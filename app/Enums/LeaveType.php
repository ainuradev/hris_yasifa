<?php

namespace App\Enums;

enum LeaveType: string
{
    case Tahunan = 'tahunan';
    case Sakit = 'sakit';
    case Penting = 'penting';
    case Melahirkan = 'melahirkan';
}
