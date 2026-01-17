<?php

namespace App\Exceptions\Enrollment;

use App\Enums\EnrollmentStatus;

class InvalidEnrollmentStatusException extends EnrollmentException
{
    public static function cannotWithdrawConfirmed(): self
    {
        return new self(
            'Cannot withdraw from confirmed enrollment. Please contact administrator.',
            ['selection' => ['Cannot withdraw from confirmed enrollment. Please contact administrator.']]
        );
    }

    public static function canOnlyConfirmPending(): self
    {
        return new self(
            'Can only confirm pending enrollments.',
            ['selection' => ['Can only confirm pending enrollments.']]
        );
    }

    public static function canOnlyRejectPending(): self
    {
        return new self(
            'Can only reject pending enrollments.',
            ['selection' => ['Can only reject pending enrollments.']]
        );
    }

    public static function invalidTransition(EnrollmentStatus $from, EnrollmentStatus $to): self
    {
        return new self(
            "Cannot transition enrollment from {$from->label()} to {$to->label()}.",
            ['selection' => ["Cannot transition enrollment from {$from->label()} to {$to->label()}."]]
        );
    }
}
