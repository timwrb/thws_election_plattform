<?php

namespace App\Filament\Electives\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class QuickActionsWidget extends Widget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function render(): View
    {
        return view('filament.electives.widgets.quick-actions-widget');
    }
}
