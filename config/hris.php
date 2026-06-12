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
        'admin_emails' => [
            'pusat' => env('HRIS_ADMIN_PUSAT_EMAIL', 'admin@sirojulfalah.test'),
            'mi' => env('HRIS_ADMIN_MI_EMAIL', 'admin-mi@sirojulfalah.test'),
            'mts' => env('HRIS_ADMIN_MTS_EMAIL', 'admin-mts@sirojulfalah.test'),
            'ma' => env('HRIS_ADMIN_MA_EMAIL', 'admin-ma@sirojulfalah.test'),
        ],
    ],
];
