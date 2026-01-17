<?php

namespace App\Filament\Electives\Resources\ResearchProjects\Schemas;

use App\Models\User;
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
                Select::make('professor_id')
                    ->label('Professor')
                    ->relationship('professor')
                    ->getOptionLabelFromRecordUsing(fn (User $user): string => "{$user->name} {$user->surname}")
                    ->searchable(['name', 'surname', 'email'])
                    ->preload(),
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
