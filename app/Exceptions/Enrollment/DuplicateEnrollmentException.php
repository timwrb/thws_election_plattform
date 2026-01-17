<?php

namespace App\Exceptions\Enrollment;

class DuplicateEnrollmentException extends EnrollmentException
{
    public static function forResearchProject(): self
    {
        return new self(
            'You are already enrolled in this project.',
            ['project' => ['You are already enrolled in this project.']]
        );
    }

    public static function forElective(string $type): self
    {
        return new self(
            "You are already enrolled in this {$type}.",
            ['elective' => ["You are already enrolled in this {$type}."]]
        );
    }
}
