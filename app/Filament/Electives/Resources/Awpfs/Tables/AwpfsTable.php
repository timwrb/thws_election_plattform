<?php

namespace App\Filament\Electives\Resources\Awpfs\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AwpfsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    TextColumn::make('name')
                        ->searchable(),
                    TextColumn::make('professor.name')
                        ->label('Professor')
                        ->formatStateUsing(fn ($record): string => $record->professor ? "{$record->professor->name} {$record->professor->surname}" : '-')
                        ->searchable(['professor.name', 'professor.surname']),
                    TextColumn::make('credits')
                        ->numeric()
                        ->sortable(),
                    TextColumn::make('language')
                        ->badge()
                        ->searchable(),
                    TextColumn::make('exam_type')
                        ->badge()
                        ->searchable(),
                    TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('updated_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
            ]);
    }
}
