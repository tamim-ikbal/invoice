<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\InvoiceSentNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guests cannot send invoice', function () {
    $invoice = Invoice::factory()->create();

    $this->post(route('admin.invoices.send', $invoice))->assertRedirect(route('login'));
});

test('invoice can be sent to client', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $invoice = Invoice::factory()->for($client)->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.send', $invoice));

    $response->assertRedirect();

    Notification::assertSentTo($client, InvoiceSentNotification::class);
});

test('sending invoice without client returns 422', function () {
    $invoice = Invoice::factory()->create(['client_id' => null]);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.send', $invoice));

    $response->assertStatus(422);
});

test('invoice sent notification is not sent when no client assigned', function () {
    Notification::fake();

    $invoice = Invoice::factory()->create(['client_id' => null]);

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.send', $invoice));

    Notification::assertNothingSent();
});
