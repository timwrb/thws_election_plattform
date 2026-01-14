<?php

namespace App\Filament\Resources\ResearchProjects\Pages;

use App\Filament\Resources\ResearchProjects\ResearchProjectResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewResearchProject extends ViewRecord
{
    protected static string $resource = ResearchProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
