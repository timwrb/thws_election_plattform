<?php

namespace App\Filament\Electives\Resources\Awpfs\Pages;

use App\Filament\Electives\Resources\Awpfs\AwpfResource;
use Filament\Resources\Pages\ListRecords;

class ListAwpfs extends ListRecords
{
    protected static string $resource = AwpfResource::class;

    protected static ?string $title = 'All AWPF Courses';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
