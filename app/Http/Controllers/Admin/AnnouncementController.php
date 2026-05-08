<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AnnouncementCategory;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Unit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $admin = auth()->user();

        $announcements = Announcement::with(['createdBy', 'unit'])
            ->when(! $admin->isAdminPusat(), function ($query) use ($admin): void {
                $query->where(function ($q) use ($admin) {
                    $q->where('unit_id', $admin->unit_id)
                      ->orWhere('is_global', true);
                });
            })
            ->latest()
            ->paginate(20);

        $units = $admin->isAdminPusat()
            ? Unit::orderBy('name')->get()
            : Unit::whereKey($admin->unit_id)->get();

        return view('admin.pengumuman.index', compact('announcements', 'units'));
    }

    public function store(Request $request, \App\Services\ScheduleService $scheduleService): RedirectResponse
    {
        $admin = $request->user();

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'category'     => ['required', \Illuminate\Validation\Rule::in([
                AnnouncementCategory::Umum->value,
                AnnouncementCategory::Penggajian->value,
                AnnouncementCategory::Absensi->value,
                AnnouncementCategory::Kegiatan->value,
            ])],
            'is_global'    => ['nullable', 'boolean'],
            'unit_id'      => [
                \Illuminate\Validation\Rule::requiredIf(! $request->boolean('is_global') && $admin->isAdminPusat()),
                'nullable',
                'exists:units,id',
            ],
            'expires_at'   => ['nullable', 'date', 'after:today'],
            'is_holiday'   => ['nullable', 'boolean'],
            'holiday_date' => ['nullable', 'date', 'required_if:is_holiday,1'],
        ]);

        $isGlobal = $admin->isAdminPusat() ? $request->boolean('is_global') : false;
        $unitId   = $admin->isAdminPusat()
            ? ($isGlobal ? null : $validated['unit_id'])
            : $admin->unit_id;

        Announcement::create([
            'unit_id'    => $unitId,
            'created_by' => $admin->id,
            'title'      => $validated['title'],
            'content'    => $validated['content'],
            'category'   => $validated['category'],
            'is_global'  => $isGlobal,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        if ($request->boolean('is_holiday')) {
            $holidayDate = \Carbon\Carbon::parse($validated['holiday_date']);
            if ($isGlobal) {
                foreach (Unit::all() as $unit) {
                    $schedule = $scheduleService->ensureScheduleExists($unit->id, $holidayDate);
                    $schedule->update(['day_type' => \App\Enums\DayType::Libur->value]);
                }
            } else {
                $schedule = $scheduleService->ensureScheduleExists($unitId, $holidayDate);
                $schedule->update(['day_type' => \App\Enums\DayType::Libur->value]);
            }
        }

        session()->flash('success', 'Pengumuman ' . ($request->boolean('is_holiday') ? 'dan Jadwal Libur ' : '') . 'berhasil ditambahkan.');

        return back();
    }

    public function edit(Announcement $pengumuman): View
    {
        $admin = auth()->user();
        $this->authorizeAccess($pengumuman, $admin);

        $units = $admin->isAdminPusat()
            ? Unit::orderBy('name')->get()
            : Unit::whereKey($admin->unit_id)->get();

        return view('admin.pengumuman.edit', compact('pengumuman', 'units'));
    }

    public function update(Request $request, Announcement $pengumuman): RedirectResponse
    {
        $admin = $request->user();
        $this->authorizeAccess($pengumuman, $admin);

        $validated = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'content'    => ['required', 'string'],
            'category'   => ['required', \Illuminate\Validation\Rule::in([
                AnnouncementCategory::Umum->value,
                AnnouncementCategory::Penggajian->value,
                AnnouncementCategory::Absensi->value,
                AnnouncementCategory::Kegiatan->value,
            ])],
            'is_global'  => ['nullable', 'boolean'],
            'unit_id'    => ['nullable', 'exists:units,id'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $isGlobal = $admin->isAdminPusat() ? $request->boolean('is_global') : $pengumuman->is_global;
        $unitId   = $admin->isAdminPusat()
            ? ($isGlobal ? null : ($validated['unit_id'] ?? $pengumuman->unit_id))
            : $pengumuman->unit_id;

        $pengumuman->update([
            'unit_id'    => $unitId,
            'title'      => $validated['title'],
            'content'    => $validated['content'],
            'category'   => $validated['category'],
            'is_global'  => $isGlobal,
            'expires_at' => $validated['expires_at'] ? \Carbon\Carbon::parse($validated['expires_at'])->endOfDay() : null,
        ]);

        session()->flash('success', 'Pengumuman berhasil diperbarui.');

        return redirect()->route('admin.pengumuman.index');
    }

    public function destroy(Announcement $pengumuman): RedirectResponse
    {
        $admin = auth()->user();
        $this->authorizeAccess($pengumuman, $admin);

        $pengumuman->delete();

        session()->flash('success', 'Pengumuman berhasil dihapus.');

        return back();
    }

    private function authorizeAccess(Announcement $announcement, $admin): void
    {
        if ($admin->isAdminPusat()) return;
        abort_if((int) $announcement->unit_id !== (int) $admin->unit_id, 403);
    }
}
