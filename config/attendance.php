<?php

return [
    'timezone' => env('ATTENDANCE_TIMEZONE', 'America/New_York'),
    'timezone_label' => env('ATTENDANCE_TIMEZONE_LABEL', 'EST'),
    'office_start' => env('ATTENDANCE_OFFICE_START', '09:00'),
    'grace_minutes' => (int) env('ATTENDANCE_GRACE_MINUTES', 15),
    'standard_hours' => (float) env('ATTENDANCE_STANDARD_HOURS', 8),
];
