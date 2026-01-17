<?php

namespace App\Filament\Electives\Resources\Awpfs\Pages;

use App\Filament\Electives\Resources\Awpfs\AwpfResource;
use App\Models\Awpf;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ActiveCourses extends ListRecords
{
    protected static string $resource = AwpfResource::class;

    protected static ?string $title = 'My AWPF Courses';

    protected static ?string $navigationLabel = 'My Selections';

    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedCheckCircle;

    protected static ?int $navigationSort = 11;

    /**
     * @return Builder<Awpf>
     */
    #[\Override]
    protected function getTableQuery(): Builder
    {
        return Awpf::query()
            ->whereHas('orderedUserChoices', function ($query): void {
                $query->where('user_id', auth()->id())
                    ->whereIn('status', ['pending', 'confirmed']);
            });
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
