<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();

        // Group subjects by unit
        if ($admin->isAdminPusat()) {
            $units = Unit::with(['subjects' => fn($q) => $q->orderBy('name')])->orderBy('name')->get();
            $globalSubjects = Subject::whereNull('unit_id')->orderBy('name')->get();
        } else {
            $units = Unit::with(['subjects' => fn($q) => $q->orderBy('name')])->whereKey($admin->unit_id)->get();
            $globalSubjects = collect();
        }

        return view('admin.subjects.index', compact('units', 'globalSubjects'));
    }

    public function store(Request $request)
    {
        $admin = $request->user();

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'unit_id' => 'nullable|exists:units,id',
            'jp_per_week' => 'required|integer|min:1|max:60',
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'jp_per_week.required' => 'JP per minggu wajib diisi.',
        ]);

        // Admin unit can only add to their own unit
        // Admin pusat with no unit_id = global (null)
        if (!$admin->isAdminPusat()) {
            $unitId = $admin->unit_id;
        } else {
            $unitId = $request->filled('unit_id') ? $request->unit_id : null;
        }

        // Check unique within the same unit scope
        $exists = Subject::where('name', $validated['name'])
            ->where('unit_id', $unitId)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Mata pelajaran ini sudah ada untuk unit tersebut.'])->withInput();
        }

        $subject = Subject::create([
            'name' => $validated['name'],
            'unit_id' => $unitId,
            'jp_per_week' => $validated['jp_per_week'],
        ]);

        if ($unitId) {
            DB::table('subject_unit')->updateOrInsert(
                ['subject_id' => $subject->id, 'unit_id' => $unitId],
                ['hours_per_week' => $validated['jp_per_week'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, Subject $subject)
    {
        $admin = $request->user();

        // Only admin pusat or same unit can edit
        if (!$admin->isAdminPusat() && (int)$subject->unit_id !== (int)$admin->unit_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'jp_per_week' => 'required|integer|min:1|max:60',
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'jp_per_week.required' => 'JP per minggu wajib diisi.',
        ]);

        $subject->update($validated);

        if ($subject->unit_id) {
            DB::table('subject_unit')->updateOrInsert(
                ['subject_id' => $subject->id, 'unit_id' => $subject->unit_id],
                ['hours_per_week' => $validated['jp_per_week'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject)
    {
        $admin = auth()->user();

        if (!$admin->isAdminPusat() && (int)$subject->unit_id !== (int)$admin->unit_id) {
            abort(403);
        }

        if ($subject->teacherSubjectUnits()->exists()) {
            return redirect()->route('admin.subjects.index')
                ->with('error', 'Mata pelajaran tidak bisa dihapus karena sedang digunakan pada jadwal guru.');
        }

        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
