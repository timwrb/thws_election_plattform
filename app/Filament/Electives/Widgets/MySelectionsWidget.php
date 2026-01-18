<?php

namespace App\Filament\Electives\Widgets;

use App\Enums\EnrollmentStatus;
use App\Models\Awpf;
use App\Models\Fwpm;
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\UserSelection;
use App\Services\SemesterService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MySelectionsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    #[\Override]
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
                    ->tooltip(fn (TextColumn $column): ?string => strlen((string) $column->getState()) > 50 ? $column->getState() : null),

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
     * @return Builder<UserSelection>
     */
    protected function getTableQuery(): Builder
    {
        $semester = resolve(SemesterService::class)->getCurrentSemester();

        if (! $semester instanceof Semester || ! Auth::check()) {
            return UserSelection::query()->whereRaw('1 = 0');
        }

        return UserSelection::query()
            ->forUser(Auth::user())
            ->forSemester($semester)
            ->with('elective')
            ->whereIn('status', [
                EnrollmentStatus::Pending,
                EnrollmentStatus::Confirmed,
            ]);
    }
}
