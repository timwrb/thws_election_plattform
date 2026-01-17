<?php

namespace App\Events\Enrollment;

use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PriorityChoicesRegistered
{
    use Dispatchable, SerializesModels;

    /**
     * @param  Collection<int, \App\Models\UserSelection>  $selections
     */
    public function __construct(
        public User $user,
        public Semester $semester,
        public string $electiveType,
        public Collection $selections
    ) {}
}
