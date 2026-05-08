<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalaryRate;
use App\Models\Unit;
use Illuminate\Http\Request;

class SalaryRateController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();
        $user = auth()->user();
        
        $salaryRates = SalaryRate::with('unit')
            ->when(!$user->isAdminPusat(), fn($q) => $q->where('unit_id', $user->unit_id))
            ->orderBy('type')
            ->orderBy('jabatan')
            ->get();
            
        $units = $user->isAdminPusat() ? Unit::orderBy('name')->get() : [];
        
        return view('admin.salary-rates.index', compact('salaryRates', 'units'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $user = auth()->user();
        
        $validated = $request->validate([
            'unit_id' => $user->isAdminPusat() ? 'required|exists:units,id' : 'nullable',
            'jabatan' => 'required|string|max:255',
            'type' => 'required|in:guru,non_guru',
            'rate' => 'required|numeric|min:0',
        ]);

        if (!$user->isAdminPusat()) {
            $validated['unit_id'] = $user->unit_id;
        }

        // Check uniqueness per unit
        $exists = SalaryRate::where('unit_id', $validated['unit_id'])
            ->where('jabatan', $validated['jabatan'])
            ->exists();
            
        if ($exists) {
            return back()->withErrors(['jabatan' => 'Jabatan ini sudah ada di unit terpilih.'])->withInput();
        }

        SalaryRate::create($validated);

        return redirect()->route('admin.salary-rates.index')->with('success', 'Rate gaji berhasil ditambahkan.');
    }

    public function update(Request $request, SalaryRate $salaryRate)
    {
        $this->authorizeAccess($salaryRate);

        $user = auth()->user();
        $validated = $request->validate([
            'unit_id' => $user->isAdminPusat() ? 'required|exists:units,id' : 'nullable',
            'jabatan' => 'required|string|max:255',
            'type' => 'required|in:guru,non_guru',
            'rate' => 'required|numeric|min:0',
        ]);

        if (!$user->isAdminPusat()) {
            $validated['unit_id'] = $user->unit_id;
        }

        // Check uniqueness per unit excluding current
        $exists = SalaryRate::where('unit_id', $validated['unit_id'])
            ->where('jabatan', $validated['jabatan'])
            ->where('id', '!=', $salaryRate->id)
            ->exists();
            
        if ($exists) {
            return back()->withErrors(['jabatan' => 'Jabatan ini sudah ada di unit terpilih.'])->withInput();
        }

        $salaryRate->update($validated);

        return redirect()->route('admin.salary-rates.index')->with('success', 'Rate gaji berhasil diperbarui.');
    }

    public function destroy(SalaryRate $salaryRate)
    {
        $this->authorizeAccess($salaryRate);

        if ($salaryRate->teacherDetails()->exists() || $salaryRate->nonTeacherDetails()->exists()) {
            return back()->with('error', 'Rate tidak bisa dihapus karena masih digunakan oleh karyawan.');
        }

        $salaryRate->delete();

        return redirect()->route('admin.salary-rates.index')->with('success', 'Rate gaji berhasil dihapus.');
    }

    private function authorizeAdmin()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin yang bisa mengelola master rate gaji.');
        }
    }

    private function authorizeAccess(SalaryRate $salaryRate)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) abort(403);
        
        if (!$user->isAdminPusat() && $salaryRate->unit_id !== $user->unit_id) {
            abort(403, 'Anda tidak memiliki akses ke rate gaji unit lain.');
        }
    }
}
