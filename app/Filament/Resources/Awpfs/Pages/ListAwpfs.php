<?php

namespace App\Filament\Resources\Awpfs\Pages;

use App\Filament\Resources\Awpfs\AwpfResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAwpfs extends ListRecords
{
    protected static string $resource = AwpfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
