<?php

namespace App\Filament\Electives\Resources\ResearchProjects\Pages;

use App\Filament\Electives\Resources\ResearchProjects\ResearchProjectResource;
use Filament\Resources\Pages\ListRecords;

class ListResearchProjects extends ListRecords
{
    protected static string $resource = ResearchProjectResource::class;

    protected static ?string $title = 'Research Projects Overview';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
