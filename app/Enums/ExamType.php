<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ExamType: string implements HasLabel
{
    case Written = 'written';
    case Oral = 'oral';
    case Portfolio = 'portfolio';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Written => 'Written Exam',
            self::Oral => 'Oral Exam',
            self::Portfolio => 'Portfolio Assessment',
        };
    }
}
