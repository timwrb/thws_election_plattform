<?php

namespace App\Filament\Electives\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class EnrollmentGuideWidget extends Widget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    #[\Override]
    public function render(): View
    {
        return view('filament.electives.widgets.enrollment-guide-widget');
    }
}
