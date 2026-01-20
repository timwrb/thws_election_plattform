<?php

namespace App\Filament\Resources\Awpfs\Tables;

use App\Enums\ExamType;
use App\Enums\Language;
use App\Filament\Tables\Columns\ProfessorColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AwpfsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Course Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                ProfessorColumn::make('professor'),

                TextColumn::make('credits')
                    ->label('ECTS')
                    ->numeric()
                    ->sortable()
                    ->suffix(' cr.'),

                TextColumn::make('language')
                    ->label('Language')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('exam_type')
                    ->label('Exam Type')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('formatted_schedules')
                    ->label('Schedule')
                    ->placeholder('No schedule defined')
                    ->separator(', ')
                    ->listWithLineBreaks()
                    ->limitList()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('language')
                    ->options(Language::class)
                    ->label('Language'),

                SelectFilter::make('exam_type')
                    ->options(ExamType::class)
                    ->label('Exam Type'),

                SelectFilter::make('credits')
                    ->options([
                        '2.5' => '2.5 ECTS',
                        '5' => '5 ECTS',
                        '7.5' => '7.5 ECTS',
                        '10' => '10 ECTS',
                    ])
                    ->label('Credits'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
