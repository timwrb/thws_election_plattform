<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Season: string implements HasLabel
{
    case Winter = 'WS';
    case Summer = 'SS';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Winter => __('Winter'),
            self::Summer => __('Summer'),
        };
    }
}
