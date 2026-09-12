<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Edit Allowed Days
    |--------------------------------------------------------------------------
    |
    | Number of days back a teacher can edit attendance. After this period,
    | only institute-admin or super-admin can make changes.
    |
    */
    'edit_allowed_days' => (int) env('ATTENDANCE_EDIT_ALLOWED_DAYS', 3),
];
