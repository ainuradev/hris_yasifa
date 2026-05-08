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
        $holidays = Holiday::with('unit')->orderBy('date', 'desc')->get();
        $units = Unit::orderBy('name')->get();
        
        return view('admin.holidays.index', compact('holidays', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        Holiday::create($validated);

        return back()->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return back()->with('success', 'Hari libur berhasil dihapus.');
    }
}
