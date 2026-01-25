<?php

namespace App\Filament\Electives\Resources\Fwpms\Tables;

use App\Filament\Electives\Resources\Fwpms\FwpmResource;
use App\Filament\Tables\Columns\ProfessorColumn;
use App\Models\Fwpm;
use App\Models\Semester;
use App\Services\SemesterService;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class FwpmsTable
{
    public static function configure(Table $table): Table
    {
        $semesterService = resolve(SemesterService::class);
        $currentSemester = $semesterService->getCurrentSemester();

        return $table
            ->query(
                Fwpm::query()
                    ->with(['professor', 'schedules', 'semesters'])
            )
            ->columns([
                Stack::make([
                    TextColumn::make('name')
                        ->weight(FontWeight::SemiBold)
                        ->size(TextSize::Large)
                        ->searchable(['name_english', 'name_german']),

                    ProfessorColumn::make('professor'),

                    Split::make([
                        TextColumn::make('language')
                            ->formatStateUsing(fn (Fwpm $record): string => $record->language->getLabel())
                            ->grow(false),

                        TextColumn::make('exam_type')
                            ->icon(fn (Fwpm $record) => $record->exam_type->getIcon())
                            ->formatStateUsing(fn (Fwpm $record): string => $record->exam_type->getShortLabel())
                            ->grow(false),

                        TextColumn::make('credits')
                            ->formatStateUsing(fn (Fwpm $record): string => $record->credits.' CP')
                            ->grow(false),
                    ])->from('sm'),

                    TextColumn::make('formatted_schedules')
                        ->color('gray')
                        ->size(TextSize::Small)
                        ->placeholder('No schedule'),
                ])->space(2),
            ])
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->groups([
                Group::make('semesters.id')
                    ->label('Semester')
                    ->getTitleFromRecordUsing(function (Fwpm $record) use ($currentSemester): string {
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
            ->groupingSettingsHidden()
            ->modifyQueryUsing(fn ($query) => $query->orderBy('name_english'))
            ->recordUrl(fn (Fwpm $record): string => FwpmResource::getUrl('view', ['record' => $record]))
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
