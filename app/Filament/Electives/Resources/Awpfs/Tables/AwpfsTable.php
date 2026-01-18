<?php

namespace App\Filament\Electives\Resources\Awpfs\Tables;

use App\Filament\Electives\Resources\Awpfs\AwpfResource;
use App\Filament\Tables\Columns\ProfessorColumn;
use App\Models\Awpf;
use App\Models\Semester;
use App\Services\SemesterService;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class AwpfsTable
{
    public static function configure(Table $table): Table
    {
        $semesterService = resolve(SemesterService::class);
        $currentSemester = $semesterService->getCurrentSemester();

        return $table
            ->query(
                Awpf::query()
                    ->with(['professor', 'schedules', 'semesters'])
            )
            ->columns([
                Stack::make([
                    TextColumn::make('name')
                        ->weight(FontWeight::SemiBold)
                        ->size(TextColumn\TextColumnSize::Large)
                        ->searchable(),

                    ProfessorColumn::make('professor'),

                    Split::make([
                        TextColumn::make('language')
                            ->formatStateUsing(fn (Awpf $record): string => $record->language->getShortLabel())
                            ->grow(false),

                        TextColumn::make('exam_type')
                            ->icon(fn (Awpf $record) => $record->exam_type->getIcon())
                            ->formatStateUsing(fn (Awpf $record): string => $record->exam_type->getShortLabel())
                            ->grow(false),

                        TextColumn::make('credits')
                            ->formatStateUsing(fn (Awpf $record): string => $record->credits.' CP')
                            ->grow(false),
                    ])->from('sm'),

                    TextColumn::make('formatted_schedules')
                        ->color('gray')
                        ->size(TextColumn\TextColumnSize::Small)
                        ->placeholder('No schedule'),
                ])->space(2),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->groups([
                Group::make('semesters.id')
                    ->label('Semester')
                    ->getTitleFromRecordUsing(function (Awpf $record) use ($currentSemester): string {
                        $semester = $record->semesters->first();
                        if (! $semester instanceof Semester) {
                            return 'No Semester';
                        }

                        $label = $semester->getLabel();

                        if ($currentSemester instanceof Semester && $semester->id === $currentSemester->id) {
                            return $label.' (Current)';
                        }

                        return $label;
                    })
                    ->collapsible(),
            ])
            ->defaultGroup('semesters.id')
            ->groupingSettingsHidden()
            ->modifyQueryUsing(fn ($query) => $query->orderBy('name'))
            ->recordUrl(fn (Awpf $record): string => AwpfResource::getUrl('view', ['record' => $record]))
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
