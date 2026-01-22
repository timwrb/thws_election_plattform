<?php

namespace App\Filament\Electives\Resources\ResearchProjects\Pages;

use App\Filament\Electives\Resources\ResearchProjects\ResearchProjectResource;
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\User;
use App\Services\SemesterService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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
 * @property Schema $form
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
        $this->currentSemester = resolve(SemesterService::class)->getCurrentSemester();

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
                            Select::make('professor_id')
                                ->label('Professor')
                                ->options(User::query()->pluck('name', 'id'))
                                ->getOptionLabelUsing(fn ($value): string => User::query()->find($value)->name ?? '')
                                ->searchable()
                                ->preload()
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
                    Section::make('Attachments')
                        ->description('Upload relevant files for this research project (max 5 files)')
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('attachments')
                                ->collection('attachments')
                                ->multiple()
                                ->reorderable()
                                ->maxFiles(5)
                                ->helperText('You can upload up to 5 files (PDF, DOC, DOCX, XLS, XLSX, images)')
                                ->acceptedFileTypes([
                                    'application/pdf',
                                    'application/msword',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    'application/vnd.ms-excel',
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'image/jpeg',
                                    'image/png',
                                    'image/gif',
                                ])
                                ->maxSize(10240)
                                ->columnSpanFull(),
                        ]),
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

        if (data_get($data, 'title', '') === '') {
            Notification::make()
                ->warning()
                ->title('Missing required fields')
                ->body('Please fill in all required fields.')
                ->send();

            return;
        }

        if (! $this->currentSemester instanceof Semester) {
            Notification::make()
                ->danger()
                ->title('No active semester')
                ->body('There is no active semester configured.')
                ->send();

            return;
        }

        try {
            $project = ResearchProject::query()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'professor_id' => $data['professor_id'] ?? null,
                'creator_id' => auth()->id(),
                'semester_id' => $this->currentSemester->id,
                'credits' => $this->getDefaultCreditsForSemester(),
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'max_students' => $data['max_students'] ?? 1,
            ]);

            // Handle file attachments if present
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    if (is_string($attachment)) {
                        $project->addMediaFromDisk($attachment, config('filament.default_filesystem_disk', 'public'))
                            ->toMediaCollection('attachments');
                    }
                }
            }

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
        return 5;
    }

    #[\Override]
    public function getView(): string
    {
        return 'filament.electives.resources.research-projects.pages.create-research-project';
    }
}
