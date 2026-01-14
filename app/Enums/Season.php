<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Season: string implements HasLabel
{
    case Winter = 'WS';
    case Summer = 'SS';

    public function getLabel(): string
    {
        return match ($this) {
            self::Winter => __('Winter'),
            self::Summer => __('Summer'),
        };
    }
}
