<?php

use App\Models\Invoice;
use App\Models\InvoiceViewLog;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guests cannot access view logs', function () {
    $invoice = Invoice::factory()->create();

    $this->getJson(route('admin.invoices.view-logs', $invoice))
        ->assertUnauthorized();
});

test('authenticated users can fetch view logs', function () {
    $invoice = Invoice::factory()->create();
    InvoiceViewLog::factory()->for($invoice)->count(3)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('admin.invoices.view-logs', $invoice));

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

test('view logs are ordered newest first', function () {
    $invoice = Invoice::factory()->create();

    $older = InvoiceViewLog::factory()->for($invoice)->create([
        'viewed_at' => now()->subDays(2),
    ]);

    $newer = InvoiceViewLog::factory()->for($invoice)->create([
        'viewed_at' => now(),
    ]);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('admin.invoices.view-logs', $invoice));

    $response->assertOk();

    $data = $response->json('data');

    expect($data[0]['id'])->toBe($newer->id);
    expect($data[1]['id'])->toBe($older->id);
});

test('view logs are paginated', function () {
    $invoice = Invoice::factory()->create();
    InvoiceViewLog::factory()->for($invoice)->count(20)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('admin.invoices.view-logs', $invoice));

    $response->assertOk();
    $response->assertJsonStructure([
        'data',
        'meta' => ['current_page', 'last_page'],
    ]);
});

test('only returns logs for the specified invoice', function () {
    $invoice = Invoice::factory()->create();
    $otherInvoice = Invoice::factory()->create();

    InvoiceViewLog::factory()->for($invoice)->count(2)->create();
    InvoiceViewLog::factory()->for($otherInvoice)->count(3)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('admin.invoices.view-logs', $invoice));

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

test('response contains expected fields', function () {
    $invoice = Invoice::factory()->create();
    InvoiceViewLog::factory()->for($invoice)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('admin.invoices.view-logs', $invoice));

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [['id', 'ip', 'browser', 'country', 'viewed_at']],
    ]);
});
