<?php

return [
    'yayasan' => [
        'latitude' => env('HRIS_YAYASAN_LATITUDE'),
        'longitude' => env('HRIS_YAYASAN_LONGITUDE'),
    ],

    'attendance' => [
        'radius_meters' => env('HRIS_ATTENDANCE_RADIUS_METERS', 100),
    ],

    'seeders' => [
        'admin_password' => env('HRIS_DEFAULT_ADMIN_PASSWORD', 'admin123'),
    ],
];
