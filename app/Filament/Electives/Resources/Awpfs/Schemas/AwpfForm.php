<?php

namespace App\Filament\Electives\Resources\Awpfs\Schemas;

use App\Enums\ExamType;
use App\Enums\Language;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AwpfForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('content')
                    ->columnSpanFull(),
                TextInput::make('credits')
                    ->required()
                    ->numeric()
                    ->default(5),
                Select::make('language')
                    ->options(Language::class)
                    ->required(),
                Select::make('exam_type')
                    ->options(ExamType::class)
                    ->required(),
            ]);
    }
}
