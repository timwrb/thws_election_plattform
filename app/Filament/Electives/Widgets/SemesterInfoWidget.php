<?php

namespace App\Filament\Electives\Widgets;

use App\Models\Semester;
use App\Services\SemesterService;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class SemesterInfoWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public ?Semester $semester = null;

    public function mount(): void
    {
        $this->semester = resolve(SemesterService::class)->getCurrentSemester();
    }

    #[\Override]
    public function render(): View
    {
        return view('filament.electives.widgets.semester-info-widget');
    }
}
