<?php

use App\Models\Awpf;
use App\Models\Fwpm;
use App\Models\Semester;
use App\Models\User;
use App\Services\EnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = resolve(EnrollmentService::class);
    $this->student = User::factory()->create(['name' => 'Max', 'surname' => 'Mustermann']);
    $this->professor = User::factory()->create(['name' => 'Prof', 'surname' => 'Schmidt']);
    $this->semester = Semester::factory()->year(2024)->winter()->create();
});

it('notifies professor when student enrolls in their AWPF course', function (): void {
    $awpf = Awpf::factory()->create([
        'professor_id' => $this->professor->id,
        'name' => 'Advanced Web Development',
    ]);

    $this->service->registerPriorityChoices(
        $this->student,
        $this->semester,
        Awpf::class,
        [$awpf->id]
    );

    expect($this->professor->notifications)->toHaveCount(1);

    $notification = $this->professor->notifications->first();
    expect($notification->data)
        ->toHaveKey('title', 'New Student Enrollment')
        ->toHaveKey('body')
        ->and($notification->data['body'])->toContain($this->student->full_name)
        ->and($notification->data['body'])->toContain($awpf->name)
        ->and($notification->data['body'])->toContain('choice #1');
});

it('notifies professor when student enrolls in their FWPM course', function (): void {
    $fwpm = Fwpm::factory()->create([
        'professor_id' => $this->professor->id,
        'name' => 'Mobile Application Development',
    ]);

    $this->service->registerPriorityChoices(
        $this->student,
        $this->semester,
        Fwpm::class,
        [$fwpm->id]
    );

    expect($this->professor->notifications)->toHaveCount(1);

    $notification = $this->professor->notifications->first();
    expect($notification->data['body'])
        ->toContain($fwpm->name);
});

it('notifies professors with correct priority positions for multiple choices', function (): void {
    $professor1 = User::factory()->create();
    $professor2 = User::factory()->create();
    $professor3 = User::factory()->create();

    $awpf1 = Awpf::factory()->create(['professor_id' => $professor1->id, 'name' => 'First Choice']);
    $awpf2 = Awpf::factory()->create(['professor_id' => $professor2->id, 'name' => 'Second Choice']);
    $awpf3 = Awpf::factory()->create(['professor_id' => $professor3->id, 'name' => 'Third Choice']);

    $this->service->registerPriorityChoices(
        $this->student,
        $this->semester,
        Awpf::class,
        [$awpf1->id, $awpf2->id, $awpf3->id]
    );

    expect($professor1->notifications->first()->data['body'])->toContain('choice #1');
    expect($professor2->notifications->first()->data['body'])->toContain('choice #2');
    expect($professor3->notifications->first()->data['body'])->toContain('choice #3');
});

it('does not send notification when course has no professor', function (): void {
    $awpf = Awpf::factory()->create(['professor_id' => null]);

    $this->service->registerPriorityChoices(
        $this->student,
        $this->semester,
        Awpf::class,
        [$awpf->id]
    );

    expect(DatabaseNotification::query()->count())->toBe(0);
});

it('notifies multiple professors when student selects multiple courses', function (): void {
    $professor1 = User::factory()->create();
    $professor2 = User::factory()->create();

    $awpf1 = Awpf::factory()->create(['professor_id' => $professor1->id]);
    $awpf2 = Awpf::factory()->create(['professor_id' => $professor2->id]);

    $this->service->registerPriorityChoices(
        $this->student,
        $this->semester,
        Awpf::class,
        [$awpf1->id, $awpf2->id]
    );

    expect($professor1->notifications)->toHaveCount(1);
    expect($professor2->notifications)->toHaveCount(1);
});

it('notification contains complete information', function (): void {
    $awpf = Awpf::factory()->create([
        'professor_id' => $this->professor->id,
        'name' => 'Data Science Fundamentals',
    ]);

    $this->service->registerPriorityChoices(
        $this->student,
        $this->semester,
        Awpf::class,
        [$awpf->id]
    );

    $notification = $this->professor->notifications->first();

    expect($notification->data)
        ->toHaveKey('title')
        ->toHaveKey('body')
        ->and($notification->data['title'])->toBe('New Student Enrollment')
        ->and($notification->data['body'])->toContain($this->student->full_name)
        ->and($notification->data['body'])->toContain($awpf->name)
        ->and($notification->data['body'])->toContain('choice #1');
});

it('notification is marked as unread by default', function (): void {
    $awpf = Awpf::factory()->create(['professor_id' => $this->professor->id]);

    $this->service->registerPriorityChoices(
        $this->student,
        $this->semester,
        Awpf::class,
        [$awpf->id]
    );

    $notification = $this->professor->notifications->first();

    expect($notification->read_at)->toBeNull();
});

it('handles same professor for multiple courses correctly', function (): void {
    $awpf1 = Awpf::factory()->create(['professor_id' => $this->professor->id, 'name' => 'Course A']);
    $awpf2 = Awpf::factory()->create(['professor_id' => $this->professor->id, 'name' => 'Course B']);

    $this->service->registerPriorityChoices(
        $this->student,
        $this->semester,
        Awpf::class,
        [$awpf1->id, $awpf2->id]
    );

    expect($this->professor->notifications)->toHaveCount(2);

    $notifications = $this->professor->notifications;

    expect($notifications->first()->data['body'])->toContain('Course A')
        ->and($notifications->first()->data['body'])->toContain('choice #1')
        ->and($notifications->last()->data['body'])->toContain('Course B')
        ->and($notifications->last()->data['body'])->toContain('choice #2');
});

it('notifications are stored in database', function (): void {
    $awpf = Awpf::factory()->create(['professor_id' => $this->professor->id]);

    $this->service->registerPriorityChoices(
        $this->student,
        $this->semester,
        Awpf::class,
        [$awpf->id]
    );

    $this->assertDatabaseHas('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $this->professor->id,
    ]);
});
