<?php

namespace App\Enums;

enum EmployeeRole: string
{
    case AdminPusat = 'admin_pusat';
    case AdminUnit = 'admin_unit';
    case Karyawan = 'karyawan';
}
