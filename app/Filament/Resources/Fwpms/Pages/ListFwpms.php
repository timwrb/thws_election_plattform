<?php

namespace App\Filament\Resources\Fwpms\Pages;

use App\Filament\Resources\Fwpms\FwpmResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFwpms extends ListRecords
{
    protected static string $resource = FwpmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
