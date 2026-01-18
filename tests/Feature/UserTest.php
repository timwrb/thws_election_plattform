<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('getInitials method', function (): void {
    it('generates initials from name and surname', function (): void {
        $user = User::factory()->create([
            'name' => 'John',
            'surname' => 'Doe',
        ]);

        expect($user->getInitials())->toBe('JD');
    });

    it('generates initials from single name', function (): void {
        $user = User::factory()->create([
            'name' => 'John',
            'surname' => '',
        ]);

        expect($user->getInitials())->toBe('J');
    });

    it('handles unicode characters correctly', function (): void {
        $user = User::factory()->create([
            'name' => 'Ümit',
            'surname' => 'Özdemir',
        ]);

        expect($user->getInitials())->toBe('ÜÖ');
    });
});

describe('getAvatarColor method', function (): void {
    it('generates consistent avatar color from name', function (): void {
        $user = User::factory()->create([
            'name' => 'John',
            'surname' => 'Doe',
        ]);

        $color1 = $user->getAvatarColor();
        $color2 = $user->getAvatarColor();

        expect($color1)->toBe($color2);
        expect($color1)->toMatch('/^#[0-9A-Fa-f]{6}$/');
    });

    it('generates different colors for different names', function (): void {
        $user1 = User::factory()->create(['name' => 'Alice', 'surname' => 'Brown']);
        $user2 = User::factory()->create(['name' => 'Bob', 'surname' => 'Wilson']);

        expect($user1->getAvatarColor())->not->toBe($user2->getAvatarColor());
    });

    it('returns a valid hex color', function (): void {
        $user = User::factory()->create([
            'name' => 'Test',
            'surname' => 'User',
        ]);

        $color = $user->getAvatarColor();

        expect($color)->toMatch('/^#[0-9A-Fa-f]{6}$/');
    });
});
