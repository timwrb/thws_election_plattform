<?php

namespace App\Filament\Resources\ResearchProjects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ResearchProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Project Title')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),

                TextColumn::make('supervisor')
                    ->label('Supervisor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('credits')
                    ->label('ECTS')
                    ->numeric()
                    ->sortable()
                    ->suffix(' cr.')
                    ->alignCenter(),

                TextColumn::make('max_students')
                    ->label('Max Students')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('d.m.Y')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('d.m.Y')
                    ->sortable()
                    ->placeholder('-'),

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
                SelectFilter::make('credits')
                    ->options([
                        '2.5' => '2.5 ECTS',
                        '5' => '5 ECTS',
                        '7.5' => '7.5 ECTS',
                        '10' => '10 ECTS',
                        '15' => '15 ECTS',
                    ])
                    ->label('Credits'),

                Filter::make('active_projects')
                    ->label('Active Projects')
                    ->query(fn (Builder $query): Builder => $query
                        ->where(function (Builder $query): void {
                            $query->whereNull('end_date')
                                ->orWhere('end_date', '>=', now());
                        })
                    )
                    ->toggle(),

                Filter::make('with_dates')
                    ->label('Has Timeline')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNotNull('start_date')
                        ->whereNotNull('end_date')
                    )
                    ->toggle(),
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
            ->defaultSort('title');
    }
}
