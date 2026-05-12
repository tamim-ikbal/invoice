<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'super_admin']);
});

test('admin profile page can be rendered', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.settings.profile.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Settings/Profile')
    );
});

test('admin profile can be updated', function () {
    $response = $this
        ->actingAs($this->user)
        ->patch(route('admin.settings.profile.update'), [
            'name' => 'Updated Admin',
            'email' => 'updated@example.com',
        ]);

    $response->assertRedirect(route('admin.settings.profile.edit'));

    $this->user->refresh();

    expect($this->user->name)->toBe('Updated Admin');
    expect($this->user->email)->toBe('updated@example.com');
});

test('admin security page can be rendered', function () {
    $response = $this
        ->actingAs($this->user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('admin.settings.security.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Settings/Security')
    );
});

test('admin password can be updated', function () {
    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.settings.security.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertSessionHasNoErrors();
});

test('admin appearance page can be rendered', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.settings.appearance.edit'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Settings/Appearance')
    );
});
