<?php

namespace App\Enums;

enum LeaveStatus: string
{
    case Pending = 'pending';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';
}
