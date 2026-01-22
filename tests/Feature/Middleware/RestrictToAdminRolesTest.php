<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'professor', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
});

it('redirects unauthenticated users to login', function () {
    $response = $this->get('/admin');

    $response->assertRedirect();
});

it('denies access to users without required roles', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    $response = $this->actingAs($user)->get('/admin');

    $response->assertNotFound();
});

it('denies access to users with no roles', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertNotFound();
});

it('allows access to users with professor role', function () {
    $user = User::factory()->professor()->create();
    $user->assignRole('professor');

    $response = $this->actingAs($user)->get('/admin');

    $response->assertSuccessful();
});

it('allows access to users with super_admin role', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $response = $this->actingAs($user)->get('/admin');

    $response->assertSuccessful();
});

it('allows access to users with both professor and super_admin roles', function () {
    $user = User::factory()->professor()->create();
    $user->assignRole(['professor', 'super_admin']);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertSuccessful();
});
