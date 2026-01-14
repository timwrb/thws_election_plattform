<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maximum Course Selections
    |--------------------------------------------------------------------------
    |
    | Configure the maximum number of courses a student can select for each
    | elective type. These limits apply to priority-based enrollments.
    |
    */

    'max_selections' => [
        'awpf' => env('ELECTIVES_MAX_AWPF', 2),
        'fwpm' => env('ELECTIVES_MAX_FWPM', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Current Semester
    |--------------------------------------------------------------------------
    |
    | The ID of the currently active semester for enrollments.
    | Set this to null to require semester selection during enrollment.
    |
    */

    'current_semester_id' => env('ELECTIVES_CURRENT_SEMESTER_ID'),

];
