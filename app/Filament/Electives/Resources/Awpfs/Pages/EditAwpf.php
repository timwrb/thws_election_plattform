<?php

namespace App\Filament\Electives\Resources\Awpfs\Pages;

use App\Filament\Electives\Resources\Awpfs\AwpfResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAwpf extends EditRecord
{
    protected static string $resource = AwpfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
