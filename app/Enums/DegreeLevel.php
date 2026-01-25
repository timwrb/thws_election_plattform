<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DegreeLevel: string implements HasLabel
{
    case Bachelor = 'bachelor';
    case Master = 'master';

    public function getLabel(): string
    {
        return match ($this) {
            self::Bachelor => __('Bachelor'),
            self::Master => __('Master'),
        };
    }

    public function getAbbreviation(): string
    {
        return match ($this) {
            self::Bachelor => 'B.',
            self::Master => 'M.',
        };
    }
}
