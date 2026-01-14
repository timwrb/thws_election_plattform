<?php

namespace App\Filament\Electives\Resources\ResearchProjects\Pages;

use App\Filament\Electives\Resources\ResearchProjects\ResearchProjectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResearchProject extends EditRecord
{
    protected static string $resource = ResearchProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
