<?php

namespace App\Filament\Resources\ResearchProjects\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ResearchProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Project Details')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Project Title')
                            ->size('lg')
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('No description provided')
                            ->columnSpanFull()
                            ->prose(),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('supervisor')
                                    ->label('Supervisor')
                                    ->icon('heroicon-o-user'),

                                TextEntry::make('credits')
                                    ->label('Credits (ECTS)')
                                    ->numeric()
                                    ->suffix(' cr.')
                                    ->icon('heroicon-o-academic-cap'),

                                TextEntry::make('max_students')
                                    ->label('Maximum Students')
                                    ->numeric()
                                    ->icon('heroicon-o-user-group'),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Project Timeline')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('start_date')
                                    ->label('Start Date')
                                    ->date('d.m.Y')
                                    ->placeholder('Not set')
                                    ->icon('heroicon-o-calendar'),

                                TextEntry::make('end_date')
                                    ->label('End Date')
                                    ->date('d.m.Y')
                                    ->placeholder('Not set')
                                    ->icon('heroicon-o-calendar'),
                            ]),
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
