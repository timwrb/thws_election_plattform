<?php

namespace App\Filament\Electives\Resources\Awpfs\Tables;

use App\Models\Awpf;
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
                        ->formatStateUsing(fn (Awpf $record): string => $record->professor->full_name),
                    TextColumn::make('language')
                        ->badge()
                        ->searchable(),
                    TextColumn::make('exam_type')
                        ->badge()
                        ->searchable(),
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
            ])
            ->toolbarActions([
            ]);
    }
}
