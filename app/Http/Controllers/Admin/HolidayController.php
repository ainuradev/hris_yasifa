<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Unit;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $holidaysQuery = Holiday::with('unit')->orderBy('date', 'desc');
        
        if ($user->isAdminUnit()) {
            // Admin Unit can see Yayasan holidays (unit_id = null) and their own unit holidays
            $holidaysQuery->where(function($q) use ($user) {
                $q->whereNull('unit_id')->orWhere('unit_id', $user->unit_id);
            });
            $units = Unit::where('id', $user->unit_id)->get();
        } else {
            $units = Unit::orderBy('name')->get();
        }
        
        $holidays = $holidaysQuery->get();
        
        return view('admin.holidays.index', compact('holidays', 'units'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $rules = [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:date',
        ];

        if ($user->isAdminPusat()) {
            $rules['unit_id'] = 'nullable|exists:units,id';
        }

        $validated = $request->validate($rules);

        // Jika end_date tidak diisi, berarti libur 1 hari saja
        if (empty($validated['end_date'])) {
            $validated['end_date'] = null;
        }

        // Admin Unit force to their own unit, Admin Pusat force to null if creating yayasan holiday
        if ($user->isAdminUnit()) {
            $validated['unit_id'] = $user->unit_id;
        } elseif ($user->isAdminPusat()) {
            // Admin Pusat shouldn't create unit-specific holiday as per user's instruction?
            // "Admin pusat tidak bisa menghapus... dibuat oleh admin unit." 
            // It's safer to let Admin Pusat only create global holidays.
            $validated['unit_id'] = null;
        }

        Holiday::create($validated);

        return back()->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function edit(Holiday $holiday)
    {
        $this->authorizeAccess($holiday);
        
        $user = auth()->user();
        $units = $user->isAdminUnit() ? Unit::where('id', $user->unit_id)->get() : Unit::orderBy('name')->get();
        
        return view('admin.holidays.edit', compact('holiday', 'units'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $this->authorizeAccess($holiday);
        
        $user = auth()->user();

        $rules = [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:date',
        ];

        $validated = $request->validate($rules);

        if (empty($validated['end_date'])) {
            $validated['end_date'] = null;
        }

        if ($user->isAdminUnit()) {
            $validated['unit_id'] = $user->unit_id;
        } else {
            $validated['unit_id'] = null;
        }

        $holiday->update($validated);

        return redirect()->route('admin.holidays.index')->with('success', 'Hari libur berhasil diperbarui.');
    }

    public function destroy(Holiday $holiday)
    {
        $this->authorizeAccess($holiday);

        $holiday->delete();

        return back()->with('success', 'Hari libur berhasil dihapus.');
    }

    /**
     * Cek apakah user berhak mengakses/mengubah holiday ini.
     * Admin Unit hanya bisa ubah unit-nya sendiri.
     * Admin Pusat hanya bisa ubah libur Yayasan (unit_id = null).
     */
    private function authorizeAccess(Holiday $holiday)
    {
        $user = auth()->user();
        
        if ($user->isAdminUnit()) {
            if ($holiday->unit_id !== $user->unit_id) {
                abort(403, 'Anda tidak memiliki akses untuk mengubah kalender libur pusat atau unit lain.');
            }
        } elseif ($user->isAdminPusat()) {
            if ($holiday->unit_id !== null) {
                abort(403, 'Admin Pusat tidak memiliki akses untuk mengubah kalender libur milik unit.');
            }
        }
    }
}
