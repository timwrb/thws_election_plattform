<?php

namespace App\Filament\Electives\Resources\Fwpms\Pages;

use App\Filament\Electives\Resources\Fwpms\FwpmResource;
use Filament\Resources\Pages\ListRecords;

class ListFwpms extends ListRecords
{
    protected static string $resource = FwpmResource::class;

    protected static ?string $title = 'All FWPM Courses';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
