<?php

namespace App\Filament\Resources\Fwpms\Pages;

use App\Filament\Resources\Fwpms\FwpmResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFwpm extends EditRecord
{
    protected static string $resource = FwpmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
