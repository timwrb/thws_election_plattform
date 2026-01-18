<?php

namespace App\Filament\Electives\Resources\Fwpms\Pages;

use App\Filament\Electives\Resources\Fwpms\FwpmResource;
use App\Models\Fwpm;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ActiveCourses extends ListRecords
{
    protected static string $resource = FwpmResource::class;

    protected static ?string $title = 'My FWPM Courses';

    protected static ?string $navigationLabel = 'My Selections';

    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedCheckCircle;

    protected static ?int $navigationSort = 21;

    /**
     * @return Builder<Fwpm>
     */
    #[\Override]
    protected function getTableQuery(): Builder
    {
        return Fwpm::query()
            ->whereHas('orderedUserChoices', function (\Illuminate\Contracts\Database\Query\Builder $query): void {
                $query->where('user_id', auth()->id())
                    ->whereIn('status', ['pending', 'confirmed']);
            });
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
