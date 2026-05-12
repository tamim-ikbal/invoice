<?php

use App\Models\User;

test('super admin is redirected to admin dashboard on login', function () {
    $user = User::factory()->create([
        'role' => 'super_admin',
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/admin');
});

test('admin is redirected to admin dashboard on login', function () {
    $user = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/admin');
});

test('regular user is redirected to dashboard on login', function () {
    $user = User::factory()->create([
        'role' => 'user',
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
});

test('admin dashboard page can be rendered', function () {
    $user = User::factory()->create(['role' => 'super_admin']);

    $response = $this
        ->actingAs($user)
        ->get('/admin');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Dashboard')
    );
});
