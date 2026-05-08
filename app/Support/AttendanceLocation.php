<?php

namespace App\Support;

class AttendanceLocation
{
    public static function coordinates(): ?array
    {
        $latitude = config('hris.yayasan.latitude');
        $longitude = config('hris.yayasan.longitude');

        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return null;
        }

        return [
            'latitude' => (float) $latitude,
            'longitude' => (float) $longitude,
        ];
    }

    public static function radiusMeters(): int
    {
        return (int) config('hris.attendance.radius_meters', 100);
    }

    public static function distanceInMeters(float $latitude, float $longitude, float $targetLatitude, float $targetLongitude): float
    {
        $earthRadius = 6371000;

        $latitudeDelta = deg2rad($targetLatitude - $latitude);
        $longitudeDelta = deg2rad($targetLongitude - $longitude);

        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitude)) * cos(deg2rad($targetLatitude)) * sin($longitudeDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
