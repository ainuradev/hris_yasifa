<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalaryComponent;
use App\Models\Unit;

class SalaryComponentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $admin = $request->user();
        
        $components = SalaryComponent::with('unit')
            ->when(!$admin->isAdminPusat(), function ($query) use ($admin) {
                // Admin Unit can see global components (unit_id null) and their own unit's components
                $query->whereNull('unit_id')->orWhere('unit_id', $admin->unit_id);
            })
            ->orderBy('type')
            ->orderBy('name')
            ->get();
            
        return view('admin.salary-components.index', compact('components'));
    }

    public function create(Request $request)
    {
        $admin = $request->user();
        $units = $admin->isAdminPusat() ? Unit::orderBy('name')->get() : collect();
        
        return view('admin.salary-components.create', compact('units'));
    }

    public function store(Request $request)
    {
        $admin = $request->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:tunjangan,potongan',
            'default_amount' => 'required|numeric|min:0',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        if (!$admin->isAdminPusat()) {
            $validated['unit_id'] = $admin->unit_id;
        }

        SalaryComponent::create($validated);
        return redirect()->route('admin.salary-components.index')->with('success', 'Komponen gaji berhasil ditambahkan.');
    }

    public function edit(Request $request, SalaryComponent $salaryComponent)
    {
        $admin = $request->user();
        
        // Admin Unit cannot edit global components or other unit's components
        if (!$admin->isAdminPusat() && $salaryComponent->unit_id !== $admin->unit_id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit komponen ini.');
        }

        $units = $admin->isAdminPusat() ? Unit::orderBy('name')->get() : collect();
        
        return view('admin.salary-components.edit', compact('salaryComponent', 'units'));
    }

    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        $admin = $request->user();
        
        if (!$admin->isAdminPusat() && $salaryComponent->unit_id !== $admin->unit_id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit komponen ini.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:tunjangan,potongan',
            'default_amount' => 'required|numeric|min:0',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        if (!$admin->isAdminPusat()) {
            $validated['unit_id'] = $admin->unit_id;
        }

        $salaryComponent->update($validated);
        return redirect()->route('admin.salary-components.index')->with('success', 'Komponen gaji berhasil diperbarui.');
    }

    public function destroy(Request $request, SalaryComponent $salaryComponent)
    {
        $admin = $request->user();
        
        if (!$admin->isAdminPusat() && $salaryComponent->unit_id !== $admin->unit_id) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus komponen ini.');
        }

        $salaryComponent->delete();
        return redirect()->route('admin.salary-components.index')->with('success', 'Komponen gaji berhasil dihapus.');
    }
}
