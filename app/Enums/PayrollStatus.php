<?php

namespace App\Enums;

enum PayrollStatus: string
{
    case Draft = 'draft';
    case Final = 'final';
    case Dibayar = 'dibayar';
}
