<?php

namespace App\Filament\Resources\Awpfs\Pages;

use App\Filament\Resources\Awpfs\AwpfResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAwpf extends ViewRecord
{
    protected static string $resource = AwpfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
