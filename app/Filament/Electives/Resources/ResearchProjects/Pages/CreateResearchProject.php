<?php

namespace App\Filament\Electives\Resources\ResearchProjects\Pages;

use App\Filament\Electives\Resources\ResearchProjects\ResearchProjectResource;
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Services\SemesterService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * @property \Filament\Schemas\Schema $form
 */
class CreateResearchProject extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ResearchProjectResource::class;

    protected static ?string $title = 'Create Research Project';

    protected static ?string $navigationLabel = 'Create Project';

    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedPlusCircle;

    protected static ?int $navigationSort = 31;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    protected ?Semester $currentSemester = null;

    public function mount(): void
    {
        $this->currentSemester = app(SemesterService::class)->getCurrentSemester();

        if (! $this->currentSemester instanceof Semester) {
            Notification::make()
                ->warning()
                ->title('No active semester')
                ->body('There is no active semester configured. Please contact administration.')
                ->persistent()
                ->send();

            $this->redirect(ResearchProjectResource::getUrl('index'));

            return;
        }

        // Pre-fill with defaults
        $this->form->fill([
            'max_students' => 1,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $semesterLabel = $this->currentSemester?->getLabel() ?? 'N/A';
        $credits = $this->getDefaultCreditsForSemester();

        return $schema
            ->components([
                Form::make([
                    Section::make('Semester Information')
                        ->description('Your project will be automatically assigned to your current semester.')
                        ->schema([
                            Placeholder::make('current_semester')
                                ->label('Semester')
                                ->content($semesterLabel),
                            Placeholder::make('credits')
                                ->label('Credits (ECTS)')
                                ->content((string) $credits),
                        ])
                        ->columns(2),
                    Section::make('Project Information')
                        ->description('Provide details about your research project.')
                        ->schema([
                            TextInput::make('title')
                                ->label('Project Title')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Textarea::make('description')
                                ->label('Project Description')
                                ->rows(5)
                                ->columnSpanFull(),
                            TextInput::make('supervisor')
                                ->label('Supervisor Name')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Professor or lecturer supervising this project'),
                            TextInput::make('max_students')
                                ->label('Maximum Students')
                                ->required()
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->maxValue(50)
                                ->helperText('Maximum number of students who can participate'),
                            DatePicker::make('start_date')
                                ->label('Start Date'),
                            DatePicker::make('end_date')
                                ->label('End Date')
                                ->after('start_date'),
                        ])
                        ->columns(2),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Create Project')
                                ->submit('save')
                                ->icon('heroicon-o-check')
                                ->keyBindings(['mod+s']),
                            Action::make('cancel')
                                ->label('Cancel')
                                ->color('gray')
                                ->url(ResearchProjectResource::getUrl('index')),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->data;

        // Validate required fields
        if (empty($data['title']) || empty($data['supervisor'])) {
            Notification::make()
                ->warning()
                ->title('Missing required fields')
                ->body('Please fill in all required fields.')
                ->send();

            return;
        }

        // Ensure user has active semester
        if (! $this->currentSemester instanceof Semester) {
            Notification::make()
                ->danger()
                ->title('No active semester')
                ->body('There is no active semester configured.')
                ->send();

            return;
        }

        try {
            // Create the research project with the authenticated user as creator
            $project = ResearchProject::query()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'supervisor' => $data['supervisor'],
                'creator_id' => auth()->id(),
                'semester_id' => $this->currentSemester->id,
                'credits' => $this->getDefaultCreditsForSemester(),
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'max_students' => $data['max_students'] ?? 1,
            ]);

            Notification::make()
                ->success()
                ->title('Project created')
                ->body('Your research project has been created successfully and is pending approval.')
                ->send();

            $this->redirect(ResearchProjectResource::getUrl('index'));
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Creation failed')
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function getDefaultCreditsForSemester(): int
    {
        // Default credits for research projects is typically 5 ECTS
        // This could be customized based on semester_number if needed
        return 5;
    }

    #[\Override]
    public function getView(): string
    {
        return 'filament.electives.resources.research-projects.pages.create-research-project';
    }
}
