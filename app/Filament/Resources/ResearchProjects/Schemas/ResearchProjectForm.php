<?php

namespace App\Filament\Resources\ResearchProjects\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ResearchProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Project Information')
                    ->description('Basic details about the research project')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label('Project Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Project Description')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('Provide a detailed description of the research project, its goals, and expected outcomes.'),

                        Grid::make(3)
                            ->schema([
                                Select::make('professor_id')
                                    ->label('Professor')
                                    ->relationship('professor')
                                    ->getOptionLabelFromRecordUsing(fn (User $user): string => "{$user->name} {$user->surname}")
                                    ->searchable(['name', 'surname', 'email'])
                                    ->preload(),

                                TextInput::make('credits')
                                    ->label('Credits (ECTS)')
                                    ->required()
                                    ->numeric()
                                    ->default(5)
                                    ->minValue(1)
                                    ->maxValue(30)
                                    ->step(1),

                                TextInput::make('max_students')
                                    ->label('Max. Students')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(10)
                                    ->helperText('Maximum number of students who can work on this project'),
                            ]),
                    ]),

                Section::make('Project Timeline')
                    ->description('Define the project duration')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Start Date')
                                    ->native(false)
                                    ->displayFormat('d.m.Y')
                                    ->maxDate(fn (Get $get): mixed => $get('end_date')),

                                DatePicker::make('end_date')
                                    ->label('End Date')
                                    ->native(false)
                                    ->displayFormat('d.m.Y')
                                    ->minDate(fn (Get $get): mixed => $get('start_date'))
                                    ->after('start_date'),
                            ]),
                    ]),
            ]);
    }
}
