<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ExamType: string implements HasLabel
{
    case Written = 'written';
    case Oral = 'oral';
    case Portfolio = 'portfolio';

    public function getLabel(): string
    {
        return match ($this) {
            self::Written => 'Written Exam',
            self::Oral => 'Oral Exam',
            self::Portfolio => 'Portfolio Assessment',
        };
    }
}
