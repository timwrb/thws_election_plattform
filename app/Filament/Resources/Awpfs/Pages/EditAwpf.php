<?php

namespace App\Filament\Resources\Awpfs\Pages;

use App\Filament\Resources\Awpfs\AwpfResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAwpf extends EditRecord
{
    protected static string $resource = AwpfResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
