<?php

namespace App\Filament\Electives\Resources\Fwpms\Pages;

use App\Filament\Electives\Resources\Fwpms\FwpmResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFwpm extends EditRecord
{
    protected static string $resource = FwpmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
