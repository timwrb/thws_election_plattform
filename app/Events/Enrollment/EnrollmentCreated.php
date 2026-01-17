<?php

namespace App\Events\Enrollment;

use App\Models\UserSelection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnrollmentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public UserSelection $enrollment) {}
}
