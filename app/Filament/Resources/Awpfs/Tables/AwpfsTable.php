<?php

namespace App\Filament\Resources\Awpfs\Tables;

use App\Enums\ExamType;
use App\Enums\Language;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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

                TextColumn::make('credits')
                    ->label('ECTS')
                    ->numeric()
                    ->sortable()
                    ->suffix(' cr.')
                    ->alignCenter(),

                TextColumn::make('language')
                    ->label('Language')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('exam_type')
                    ->label('Exam Type')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('formatted_schedules')
                    ->label('Schedule')
                    ->placeholder('No schedule defined')
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                ViewAction::make(),
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
