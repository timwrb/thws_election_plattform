<?php

namespace App\Filament\Electives\Resources\Awpfs\Pages;

use App\Enums\EnrollmentStatus;
use App\Filament\Electives\Resources\Awpfs\AwpfResource;
use App\Models\Awpf;
use App\Models\Semester;
use App\Models\UserSelection;
use App\Services\EnrollmentService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class EnrollInCourses extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = AwpfResource::class;

    protected static ?string $title = 'Enroll in AWPF Courses';

    protected static ?string $navigationLabel = 'Enroll Now';

    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedPlusCircle;

    protected static ?int $navigationSort = 12;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getExistingSelections());
    }

    #[\Override]
    public function getView(): string
    {
        return 'filament.electives.resources.awpfs.pages.enroll-in-courses';
    }

    public function form(Schema $schema): Schema
    {
        $maxSelections = config('electives.max_selections.awpf', 2);
        $availableCourses = $this->getAvailableCourses();

        $fields = [];

        for ($i = 1; $i <= $maxSelections; $i++) {
            $fields[] = Select::make("choice_{$i}")
                ->label($this->getOrdinalLabel($i).' Choice')
                ->options($availableCourses)
                ->searchable()
                ->placeholder('Select a course')
                ->distinct()
                ->helperText($i === 1 ? 'Select your courses in order of preference' : null);
        }

        return $schema
            ->components([
                Form::make([
                    Section::make('Course Selection')
                        ->description('Select up to '.$maxSelections.' AWPF courses in order of your preference. Your 1st choice has the highest priority.')
                        ->schema($fields)
                        ->columns(1),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Submit Enrollment')
                                ->submit('save')
                                ->icon('heroicon-o-check')
                                ->keyBindings(['mod+s']),
                            Action::make('cancel')
                                ->label('Cancel')
                                ->color('gray')
                                ->url(AwpfResource::getUrl('index')),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Extract only filled choices and preserve their order
        $orderedElectiveIds = collect($data)
            ->filter(fn ($value): bool => ! empty($value))
            ->values()
            ->toArray();

        if (empty($orderedElectiveIds)) {
            Notification::make()
                ->warning()
                ->title('No courses selected')
                ->body('Please select at least one course to enroll.')
                ->send();

            return;
        }

        // Get current semester
        $semester = $this->getCurrentSemester();

        if (! $semester instanceof \App\Models\Semester) {
            Notification::make()
                ->danger()
                ->title('No active semester')
                ->body('There is no active semester for enrollment.')
                ->send();

            return;
        }

        try {
            DB::transaction(function () use ($orderedElectiveIds, $semester): void {
                // Use EnrollmentService to register priority choices
                $service = app(EnrollmentService::class);
                $service->registerPriorityChoices(
                    auth()->user(),
                    $semester,
                    Awpf::class,
                    $orderedElectiveIds
                );
            });

            Notification::make()
                ->success()
                ->title('Enrollment submitted')
                ->body('Your course selections have been submitted successfully. They are pending approval.')
                ->send();

            $this->redirect(AwpfResource::getUrl('my-courses'));
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Enrollment failed')
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function getAvailableCourses(): array
    {
        return Awpf::query()
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function getExistingSelections(): array
    {
        $semester = $this->getCurrentSemester();

        if (! $semester instanceof \App\Models\Semester) {
            return [];
        }

        // Get existing selections for this user and semester
        $selections = UserSelection::query()
            ->forUser(auth()->user())
            ->forSemester($semester)
            ->where('elective_type', Awpf::class)
            ->whereIn('status', [EnrollmentStatus::Pending, EnrollmentStatus::Confirmed])
            ->orderByRaw('CASE WHEN parent_elective_choice_id IS NULL THEN 0 ELSE parent_elective_choice_id END')
            ->get();

        $data = [];
        $index = 1;

        foreach ($selections as $selection) {
            $data["choice_{$index}"] = $selection->elective_choice_id;
            $index++;
        }

        return $data;
    }

    protected function getCurrentSemester(): ?Semester
    {
        $configuredSemesterId = config('electives.current_semester_id');

        if ($configuredSemesterId) {
            return Semester::query()->find($configuredSemesterId);
        }

        // Fallback: get the most recent semester
        return Semester::query()
            ->orderBy('year', 'desc')
            ->orderBy('season', 'desc')
            ->first();
    }

    protected function getOrdinalLabel(int $number): string
    {
        return match ($number) {
            1 => '1st',
            2 => '2nd',
            3 => '3rd',
            default => $number.'th',
        };
    }

    #[\Override]
    public function getTitle(): string|Htmlable
    {
        return static::$title;
    }
}
