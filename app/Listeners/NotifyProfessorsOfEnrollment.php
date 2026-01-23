<?php

namespace App\Listeners;

use App\Events\Enrollment\PriorityChoicesRegistered;
use App\Models\Awpf;
use App\Models\Fwpm;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(PriorityChoicesRegistered::class)]
class NotifyProfessorsOfEnrollment implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PriorityChoicesRegistered $event): void
    {
        $event->selections->each(function ($selection, $index) use ($event): void {
            if (! $selection->relationLoaded('elective')) {
                $selection->load('elective.professor');
            }

            $course = $selection->elective;

            if (! $course instanceof Awpf && ! $course instanceof Fwpm) {
                return;
            }

            if (! $course->professor instanceof User) {
                return;
            }

            $priorityPosition = $index + 1;

            Notification::make()
                ->title('New Student Enrollment')
                ->body("{$event->user->full_name} selected {$course->name} as choice #{$priorityPosition}")
                ->success()
                ->sendToDatabase($course->professor);
        });
    }
}
