<?php

namespace App\Filament\Electives\Resources\ResearchProjects\Pages;

use App\Filament\Electives\Resources\ResearchProjects\ResearchProjectResource;
use App\Models\ResearchProject;
use App\Models\Semester;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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

    public function mount(): void
    {
        $this->form->fill([
            'credits' => 5,
            'max_students' => 1,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
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
                            Select::make('semester_id')
                                ->label('Semester')
                                ->options($this->getSemesterOptions())
                                ->required()
                                ->helperText('The semester when this project will be conducted'),
                            TextInput::make('credits')
                                ->label('Credits (ECTS)')
                                ->required()
                                ->numeric()
                                ->default(5)
                                ->minValue(1)
                                ->maxValue(30),
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
        if (empty($data['title']) || empty($data['supervisor']) || empty($data['semester_id'])) {
            Notification::make()
                ->warning()
                ->title('Missing required fields')
                ->body('Please fill in all required fields.')
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
                'semester_id' => $data['semester_id'],
                'credits' => $data['credits'] ?? 5,
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

    /**
     * @return array<int, string>
     */
    protected function getSemesterOptions(): array
    {
        return Semester::query()
            ->orderBy('year', 'desc')
            ->orderBy('season', 'desc')
            ->get()
            ->mapWithKeys(fn (Semester $semester): array => [
                $semester->id => $semester->getLabel(),
            ])
            ->toArray();
    }

    #[\Override]
    public function getView(): string
    {
        return 'filament.electives.resources.research-projects.pages.create-research-project';
    }
}
