<?php

namespace App\Filament\Resources\Fwpms\Schemas;

use App\Enums\DayOfWeek;
use App\Enums\ExamType;
use App\Enums\Language;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FwpmForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('General details about the FWPM course')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Course Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('content')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('credits')
                                    ->label('Credits (ECTS)')
                                    ->required()
                                    ->numeric()
                                    ->default(5)
                                    ->minValue(1)
                                    ->maxValue(30)
                                    ->step(1),

                                Select::make('language')
                                    ->label('Course Language')
                                    ->options(Language::class)
                                    ->required()
                                    ->native(false),

                                Select::make('exam_type')
                                    ->label('Examination Type')
                                    ->options(ExamType::class)
                                    ->required()
                                    ->native(false),
                            ]),
                    ]),

                Section::make('Course Schedule')
                    ->description('Define when this course takes place')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('schedules')
                            ->relationship()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('day_of_week')
                                            ->label('Day')
                                            ->options(DayOfWeek::class)
                                            ->required()
                                            ->native(false),

                                        TimePicker::make('start_time')
                                            ->label('Start Time')
                                            ->required()
                                            ->seconds(false),

                                        TextInput::make('duration_minutes')
                                            ->label('Duration (minutes)')
                                            ->required()
                                            ->numeric()
                                            ->default(90)
                                            ->minValue(1)
                                            ->step(15)
                                            ->suffix('min'),
                                    ]),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add Schedule')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => isset($state['day_of_week'], $state['start_time'])
                                ? DayOfWeek::from($state['day_of_week'])->getLabel().' at '.$state['start_time']
                                : null),
                    ]),
            ]);
    }
}
