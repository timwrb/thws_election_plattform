<?php

namespace App\Filament\Electives\Resources\Awpfs\Schemas;

use App\Models\Awpf;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use App\Services\SemesterService;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Facades\Auth;

class AwpfInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Course Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Course Name')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->columnSpanFull(),

                        Grid::make(4)
                            ->schema([
                                TextEntry::make('credits')
                                    ->label('Credits')
                                    ->suffix(' CP'),

                                TextEntry::make('language')
                                    ->label('Language')
                                    ->formatStateUsing(fn (Awpf $record): string => $record->language->getShortLabel()),

                                TextEntry::make('exam_type')
                                    ->label('Exam Type')
                                    ->icon(fn (Awpf $record) => $record->exam_type->getIcon()),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Description')
                    ->schema([
                        TextEntry::make('content')
                            ->hiddenLabel()
                            ->prose()
                            ->placeholder('No description provided'),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('Schedule')
                    ->schema([
                        RepeatableEntry::make('schedules')
                            ->hiddenLabel()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('day_of_week')
                                            ->label('Day')
                                            ->badge(),

                                        TextEntry::make('start_time')
                                            ->label('Start Time')
                                            ->time('H:i'),

                                        TextEntry::make('duration_minutes')
                                            ->label('Duration')
                                            ->suffix(' min'),
                                    ]),
                            ])
                            ->placeholder('No schedule defined'),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('Professor')
                    ->schema([
                        Flex::make([
                            ImageEntry::make('professor_avatar')
                                ->hiddenLabel()
                                ->circular()
                                ->size(64)
                                ->state(fn (Awpf $record): ?string => $record->professor?->getFilamentAvatarUrl())
                                ->grow(false)
                                ->visible(fn (Awpf $record): bool => $record->professor?->getFilamentAvatarUrl() !== null),

                            Grid::make(1)
                                ->schema([
                                    TextEntry::make('professor.full_name')
                                        ->label('Name')
                                        ->placeholder('No professor assigned'),

                                    TextEntry::make('professor.email')
                                        ->label('Email')
                                        ->url(fn (Awpf $record): ?string => $record->professor ? "mailto:{$record->professor->email}" : null)
                                        ->placeholder('-'),
                                ]),
                        ])->from('sm'),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('Enrollment Status')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('user_enrollment_status')
                                    ->label('Your Status')
                                    ->state(function (Awpf $record): string {
                                        /** @var User|null $user */
                                        $user = Auth::user();
                                        if (! $user instanceof User) {
                                            return 'Not logged in';
                                        }

                                        $semesterService = resolve(SemesterService::class);
                                        $currentSemester = $semesterService->getCurrentSemester();

                                        if (! $currentSemester instanceof Semester) {
                                            return 'No active semester';
                                        }

                                        $selection = UserSelection::query()
                                            ->forUser($user)
                                            ->forSemester($currentSemester)
                                            ->awpf()
                                            ->where('elective_choice_id', $record->id)
                                            ->first();

                                        if (! $selection instanceof UserSelection) {
                                            return 'Not enrolled';
                                        }

                                        return $selection->status->label();
                                    })
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Not enrolled', 'Not logged in', 'No active semester' => 'gray',
                                        'Pending' => 'warning',
                                        'Confirmed' => 'success',
                                        'Rejected' => 'danger',
                                        'Withdrawn' => 'gray',
                                        default => 'gray',
                                    }),

                                TextEntry::make('enrolled_count')
                                    ->label('Students Enrolled')
                                    ->state(function (Awpf $record): int {
                                        $semesterService = resolve(SemesterService::class);
                                        $currentSemester = $semesterService->getCurrentSemester();

                                        if (! $currentSemester instanceof Semester) {
                                            return 0;
                                        }

                                        return UserSelection::query()
                                            ->forSemester($currentSemester)
                                            ->awpf()
                                            ->where('elective_choice_id', $record->id)
                                            ->whereIn('status', ['pending', 'confirmed'])
                                            ->count();
                                    }),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
