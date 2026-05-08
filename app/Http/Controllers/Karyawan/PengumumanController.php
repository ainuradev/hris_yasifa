<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Contracts\View\View;

class PengumumanController extends Controller
{
    public function index(): View
    {
        $employee = auth()->user();

        $announcements = Announcement::with(['createdBy', 'unit'])
            ->where(function ($query) use ($employee): void {
                $query->where('is_global', true)
                    ->orWhere('unit_id', $employee->unit_id);
            })
            ->latest()
            ->paginate(20);

        return view('karyawan.pengumuman.index', compact('announcements'));
    }
}
