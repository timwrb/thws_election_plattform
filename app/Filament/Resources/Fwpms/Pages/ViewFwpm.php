<?php

namespace App\Filament\Resources\Fwpms\Pages;

use App\Filament\Resources\Fwpms\FwpmResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFwpm extends ViewRecord
{
    protected static string $resource = FwpmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
