<?php

namespace App\Filament\Electives\Resources\ResearchProjects\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ResearchProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('supervisor')
                    ->required(),
                Select::make('creator_id')
                    ->relationship('creator', 'name'),
                TextInput::make('credits')
                    ->required()
                    ->numeric()
                    ->default(5),
                DatePicker::make('start_date'),
                DatePicker::make('end_date'),
                Select::make('semester_id')
                    ->relationship('semester', 'id'),
                TextInput::make('max_students')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }
}
