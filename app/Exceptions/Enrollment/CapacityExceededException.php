<?php

namespace App\Exceptions\Enrollment;

class CapacityExceededException extends EnrollmentException
{
    public static function forResearchProject(): self
    {
        return new self(
            'This research project has reached maximum capacity.',
            ['project' => ['This research project has reached maximum capacity.']]
        );
    }

    public static function forElective(string $type): self
    {
        return new self(
            "This {$type} has reached maximum capacity.",
            ['elective' => ["This {$type} has reached maximum capacity."]]
        );
    }
}
