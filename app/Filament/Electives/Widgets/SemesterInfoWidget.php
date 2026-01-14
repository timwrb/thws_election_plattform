<?php

namespace App\Filament\Electives\Widgets;

use App\Models\Semester;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class SemesterInfoWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getSemester(): ?Semester
    {
        $configuredSemesterId = config('electives.current_semester_id');

        if ($configuredSemesterId) {
            return Semester::query()->find($configuredSemesterId);
        }

        return Semester::query()
            ->orderBy('year', 'desc')
            ->orderBy('season', 'desc')
            ->first();
    }

    #[\Override]
    public function render(): View
    {
        return view('filament.electives.widgets.semester-info-widget');
    }
}
