<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Language: string implements HasLabel
{
    case English = 'English';
    case German = 'Deutsch';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::English => __('English'),
            self::German => __('Deutsch'),
        };
    }
}
