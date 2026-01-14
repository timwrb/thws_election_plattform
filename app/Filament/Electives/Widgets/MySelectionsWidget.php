<?php

namespace App\Filament\Electives\Widgets;

use App\Enums\EnrollmentStatus;
use App\Models\Awpf;
use App\Models\Fwpm;
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\UserSelection;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class MySelectionsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('elective_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Awpf::class => 'AWPF',
                        Fwpm::class => 'FWPM',
                        ResearchProject::class => 'Research Project',
                        default => 'Unknown',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Awpf::class => 'info',
                        Fwpm::class => 'success',
                        ResearchProject::class => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('elective.name')
                    ->label('Course / Project')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('priority')
                    ->label('Priority')
                    ->getStateUsing(fn (UserSelection $record): ?int => $record->getPriorityOrder())
                    ->formatStateUsing(fn (?int $state): string => $state ? "{$state}. Choice" : 'Direct')
                    ->badge()
                    ->color(fn (?int $state): string => match ($state) {
                        1 => 'success',
                        2 => 'info',
                        3 => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (EnrollmentStatus $state): string => match ($state) {
                        EnrollmentStatus::Pending => 'warning',
                        EnrollmentStatus::Confirmed => 'success',
                        EnrollmentStatus::Rejected => 'danger',
                        EnrollmentStatus::Withdrawn => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Enrolled At')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->heading('My Course Selections')
            ->description('View all your current course and project selections');
    }

    /**
     * @return Builder<UserSelection>|null
     */
    protected function getTableQuery(): Builder|null
    {
        $semester = $this->getCurrentSemester();

        if (! $semester instanceof Semester) {
            return UserSelection::query()->whereRaw('1 = 0'); // Empty query
        }

        return UserSelection::query()
            ->forUser(auth()->user())
            ->forSemester($semester)
            ->with('elective')
            ->whereIn('status', [
                EnrollmentStatus::Pending,
                EnrollmentStatus::Confirmed,
            ]);
    }

    protected function getCurrentSemester(): ?Semester
    {
        $configuredSemesterId = config('electives.current_semester_id');

        if ($configuredSemesterId) {
            return Semester::query()->find($configuredSemesterId);
        }

        return Semester::query()
            ->orderBy('year', 'desc')
            ->orderBy('season', 'desc')
            ->first();
    }
}
