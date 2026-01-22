<?php

namespace App\Filament\Electives\Resources\Fwpms\Pages;

use App\Filament\Electives\Resources\Fwpms\FwpmResource;
use App\Filament\Electives\Resources\Fwpms\Schemas\FwpmInfolist;
use App\Models\Fwpm;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;
use App\Services\EnrollmentService;
use App\Services\SemesterService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

/**
 * @property Fwpm $record
 */
class ViewFwpm extends ViewRecord
{
    protected static string $resource = FwpmResource::class;

    protected static ?string $title = 'Course Details';

    #[\Override]
    public function infolist(Schema $schema): Schema
    {
        return FwpmInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('enroll')
                ->label($this->getEnrollButtonLabel())
                ->icon(Heroicon::OutlinedAcademicCap)
                ->color($this->isEnrolled() ? 'gray' : 'primary')
                ->disabled($this->isEnrolled())
                ->requiresConfirmation()
                ->modalHeading('Enroll in Course')
                ->modalDescription(fn (): string => "Are you sure you want to enroll in \"{$this->record->name}\"?")
                ->action(function (): void {
                    $this->enrollInCourse();
                }),
        ];
    }

    private function getEnrollButtonLabel(): string
    {
        if ($this->isEnrolled()) {
            return 'Already Enrolled';
        }

        return 'Enroll in Course';
    }

    private function isEnrolled(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user instanceof User) {
            return false;
        }

        $semesterService = resolve(SemesterService::class);
        $currentSemester = $semesterService->getCurrentSemester();

        if (! $currentSemester instanceof Semester) {
            return false;
        }

        return UserSelection::query()
            ->forUser($user)
            ->forSemester($currentSemester)
            ->fwpm()
            ->where('elective_choice_id', $this->record->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
    }

    private function enrollInCourse(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user instanceof User) {
            Notification::make()
                ->title('Error')
                ->body('You must be logged in to enroll.')
                ->danger()
                ->send();

            return;
        }

        $semesterService = resolve(SemesterService::class);
        $currentSemester = $semesterService->getCurrentSemester();

        if (! $currentSemester instanceof Semester) {
            Notification::make()
                ->title('Error')
                ->body('No active semester found.')
                ->danger()
                ->send();

            return;
        }

        /** @var EnrollmentService $enrollmentService */
        $enrollmentService = resolve(EnrollmentService::class);

        try {
            $enrollmentService->registerPriorityChoices(
                $user,
                $currentSemester,
                Fwpm::class,
                [$this->record->id]
            );

            Notification::make()
                ->title('Enrolled Successfully')
                ->body("You have been enrolled in \"{$this->record->name}\".")
                ->success()
                ->send();

            $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Enrollment Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
