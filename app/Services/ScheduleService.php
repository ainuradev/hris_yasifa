<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\Holiday;
use App\Enums\DayType;
use Carbon\Carbon;

class ScheduleService
{
    /**
     * Pastikan jadwal tersedia untuk unit dan tanggal tertentu.
     * Jika belum ada, buat otomatis berdasarkan aturan standar.
     */
    public function ensureScheduleExists(int $unitId, string|Carbon $date): Schedule
    {
        $date = Carbon::parse($date)->toDateString();

        $schedule = Schedule::where('unit_id', $unitId)
            ->whereDate('work_date', $date)
            ->first();

        if ($schedule) {
            return $schedule;
        }

        // Jika tidak ada, buat otomatis
        return $this->generateDefaultSchedule($unitId, $date);
    }

    /**
     * Pastikan jadwal tersedia untuk rentang waktu (misal seminggu)
     */
    public function ensureSchedulesForRange(int $unitId, Carbon $start, Carbon $end): void
    {
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $this->ensureScheduleExists($unitId, $date);
        }
    }

    /**
     * Membuat jadwal default (07:30 - 15:00)
     */
    protected function generateDefaultSchedule(int $unitId, string $date): Schedule
    {
        $carbonDate = Carbon::parse($date);
        $isHoliday = $this->isHoliday($unitId, $date);
        $isSunday = $carbonDate->dayOfWeekIso === 7;

        $dayType = ($isHoliday || $isSunday) ? DayType::Libur->value : ($carbonDate->dayOfWeekIso === 6 ? DayType::SetengahHari->value : DayType::Normal->value);

        return Schedule::create([
            'unit_id' => $unitId,
            'work_date' => $date,
            'check_in_start' => '07:00:00',
            'check_in_end' => '14:30:00',
            'day_type' => $dayType,
        ]);
    }

    public function isHoliday(int $unitId, string|Carbon $date): bool
    {
        $date = Carbon::parse($date)->toDateString();

        return Holiday::query()
            ->where(function ($query) use ($date): void {
                // Libur 1 hari (end_date null) → cocokkan exact date
                $query->where(function ($q) use ($date): void {
                    $q->whereNull('end_date')
                      ->whereDate('date', $date);
                })
                // Libur range (end_date terisi) → cek apakah tanggal ada di dalam range
                ->orWhere(function ($q) use ($date): void {
                    $q->whereNotNull('end_date')
                      ->whereDate('date', '<=', $date)
                      ->whereDate('end_date', '>=', $date);
                });
            })
            ->where(function ($query) use ($unitId): void {
                $query->whereNull('unit_id')
                    ->orWhere('unit_id', $unitId);
            })
            ->exists();
    }
}
