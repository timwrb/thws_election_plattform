<?php

namespace App\Filament\Resources\Fwpms\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FwpmInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Course Details')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Course Name')
                            ->size('lg')
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('content')
                            ->label('Description')
                            ->placeholder('No description provided')
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('credits')
                                    ->label('Credits (ECTS)')
                                    ->numeric()
                                    ->suffix(' cr.'),

                                TextEntry::make('language')
                                    ->label('Language')
                                    ->badge(),

                                TextEntry::make('exam_type')
                                    ->label('Exam Type')
                                    ->badge(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Schedule')
                    ->schema([
                        RepeatableEntry::make('schedules')
                            ->hiddenLabel()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('day_of_week')
                                            ->label('Day')
                                            ->badge(),

                                        TextEntry::make('start_time')
                                            ->label('Start Time')
                                            ->time('H:i'),

                                        TextEntry::make('duration_minutes')
                                            ->label('Duration')
                                            ->suffix(' minutes'),
                                    ]),
                            ])
                            ->placeholder('No schedule defined'),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('Metadata')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime()
                                    ->placeholder('-'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime()
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->collapsed(),
            ]);
    }
}
