<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubjectPermission;
use App\Models\AttendanceCorrection;
use App\Models\Attendance;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function index()
    {
        $permissions = SubjectPermission::with(['employee', 'teacherSubjectUnit.subject', 'teacherSubjectUnit.class'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $corrections = AttendanceCorrection::with('employee')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('admin.approvals.index', compact('permissions', 'corrections'));
    }

    public function approvePermission(SubjectPermission $permission)
    {
        $permission->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Izin jam pelajaran berhasil disetujui.');
    }

    public function rejectPermission(SubjectPermission $permission)
    {
        $permission->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Izin jam pelajaran berhasil ditolak.');
    }

    public function approveCorrection(AttendanceCorrection $correction)
    {
        \DB::transaction(function () use ($correction) {
            $correction->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Jika ada check_in/check_out, kita bisa update atau create attendance record
            // Tapi untuk sekarang kita tandai saja statusnya sudah beres.
        });

        return back()->with('success', 'Koreksi absensi berhasil disetujui.');
    }

    public function rejectCorrection(AttendanceCorrection $correction)
    {
        $correction->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Koreksi absensi berhasil ditolak.');
    }
}
