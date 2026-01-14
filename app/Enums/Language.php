<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Language: string implements HasLabel
{
    case English = 'English';
    case German = 'Deutsch';

    public function getLabel(): string
    {
        return match ($this) {
            self::English => __('English'),
            self::German => __('Deutsch'),
        };
    }
}
