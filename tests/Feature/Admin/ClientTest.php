<?php

use App\Models\Client;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guests cannot access client pages', function () {
    $this->get(route('admin.clients.index'))->assertRedirect(route('login'));
});

test('client index page can be rendered', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.clients.index'));

    $response->assertOk();
});

test('client index displays clients', function () {
    $client = Client::factory()->create(['name' => 'Acme Corp']);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.clients.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Client/Index')
        ->has('clients.data', 1)
        ->where('clients.data.0.name', 'Acme Corp')
    );
});

test('client create page can be rendered', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.clients.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Client/Create')
    );
});

test('client can be created', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.clients.store'), [
            'name' => 'New Client',
            'email' => 'client@example.com',
        ]);

    $response->assertRedirect(route('admin.clients.index'));

    $this->assertDatabaseHas('clients', [
        'name' => 'New Client',
        'email' => 'client@example.com',
    ]);
});

test('client creation requires name and email', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.clients.store'), [
            'name' => '',
            'email' => '',
        ]);

    $response->assertSessionHasErrors(['name', 'email']);
});

test('client creation requires unique email', function () {
    Client::factory()->create(['email' => 'taken@example.com']);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.clients.store'), [
            'name' => 'Another Client',
            'email' => 'taken@example.com',
        ]);

    $response->assertSessionHasErrors('email');
});

test('client edit page can be rendered', function () {
    $client = Client::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.clients.edit', $client));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Client/Edit')
        ->has('client')
    );
});

test('client can be updated', function () {
    $client = Client::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.clients.update', $client), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

    $response->assertRedirect(route('admin.clients.edit', $client));

    $client->refresh();

    expect($client->name)->toBe('Updated Name');
    expect($client->email)->toBe('updated@example.com');
});

test('client can keep same email on update', function () {
    $client = Client::factory()->create(['email' => 'same@example.com']);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.clients.update', $client), [
            'name' => 'Updated Name',
            'email' => 'same@example.com',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('admin.clients.edit', $client));
});

test('client can be deleted', function () {
    $client = Client::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.clients.destroy', $client));

    $response->assertRedirect(route('admin.clients.index'));

    $this->assertDatabaseMissing('clients', ['id' => $client->id]);
});
