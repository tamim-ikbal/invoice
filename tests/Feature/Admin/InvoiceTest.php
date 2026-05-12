<?php

use App\Enums\InvoiceStatusEnum;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('guests cannot access invoice pages', function () {
    $this->get(route('admin.invoices.index'))->assertRedirect(route('login'));
});

test('invoice index page can be rendered', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.invoices.index'));

    $response->assertOk();
});

test('invoice index displays invoices', function () {
    $invoice = Invoice::factory()->create(['title' => 'Test Invoice']);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.invoices.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Invoice/Index')
        ->has('invoices.data', 1)
        ->where('invoices.data.0.title', 'Test Invoice')
    );
});

test('invoice can be created with just a title', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.store'), [
            'title' => 'New Invoice',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('invoices', [
        'title' => 'New Invoice',
        'status' => InvoiceStatusEnum::DRAFT->value,
    ]);
});

test('invoice creation requires a title', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.store'), [
            'title' => '',
        ]);

    $response->assertSessionHasErrors('title');
});

test('invoice edit page can be rendered', function () {
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.invoices.edit', $invoice));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Invoice/Edit')
        ->has('invoice')
        ->has('clients')
        ->has('statuses')
    );
});

test('invoice general info can be updated', function () {
    $client = Client::factory()->create();
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.invoices.update', $invoice), [
            'title' => 'Updated Invoice',
            'client_id' => $client->id,
            'status' => InvoiceStatusEnum::SENT->value,
            'date' => '2026-05-12',
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    $invoice->refresh();

    expect($invoice->title)->toBe('Updated Invoice');
    expect($invoice->client_id)->toBe($client->id);
    expect($invoice->status)->toBe(InvoiceStatusEnum::SENT);
});

test('item can be added to an invoice', function () {
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.invoice-items.store', $invoice), [
            'name' => 'New Item',
            'amount' => 150.50,
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    expect($invoice->items)->toHaveCount(1);
    expect($invoice->items->first()->name)->toBe('New Item');
    expect($invoice->items->first()->quantity)->toBe(1);
});

test('item can be added with quantity', function () {
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.invoice-items.store', $invoice), [
            'name' => 'Bulk Item',
            'quantity' => 5,
            'amount' => 100,
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    $item = $invoice->items->first();

    expect($item->name)->toBe('Bulk Item');
    expect($item->quantity)->toBe(5);
});

test('item can be updated', function () {
    $invoice = Invoice::factory()->create();
    $item = InvoiceItem::factory()->for($invoice)->create();

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.invoices.invoice-items.update', [$invoice, $item]), [
            'name' => 'Updated Item',
            'amount' => 200,
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    $item->refresh();

    expect($item->name)->toBe('Updated Item');
});

test('item can be deleted', function () {
    $invoice = Invoice::factory()->create();
    $item = InvoiceItem::factory()->for($invoice)->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.invoices.invoice-items.destroy', [$invoice, $item]));

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    $this->assertDatabaseMissing('invoice_items', ['id' => $item->id]);
});

test('item creation validates fields', function () {
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.invoice-items.store', $invoice), [
            'name' => '',
            'amount' => -1,
        ]);

    $response->assertSessionHasErrors(['name', 'amount']);
});

test('payment can be added to an invoice', function () {
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.payments.store', $invoice), [
            'amount' => 100,
            'date' => '2026-05-12',
            'status' => 'paid',
            'payment_method' => 'payoneer',
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    expect($invoice->payments)->toHaveCount(1);
});

test('payment can be updated', function () {
    $invoice = Invoice::factory()->create();
    $payment = Payment::factory()->for($invoice)->create(['amount' => 50]);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.invoices.payments.update', [$invoice, $payment]), [
            'amount' => 200,
            'date' => '2026-05-12',
            'status' => 'paid',
            'payment_method' => 'payoneer',
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    expect((float) $payment->refresh()->amount)->toBe(200.0);
});

test('payment can be deleted', function () {
    $invoice = Invoice::factory()->create();
    $payment = Payment::factory()->for($invoice)->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.invoices.payments.destroy', [$invoice, $payment]));

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
});

test('payment creation validates fields', function () {
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.payments.store', $invoice), [
            'amount' => '',
            'date' => '',
            'status' => 'invalid',
            'payment_method' => 'invalid',
        ]);

    $response->assertSessionHasErrors(['amount', 'date', 'status', 'payment_method']);
});

test('invoice can be soft deleted', function () {
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.invoices.destroy', $invoice));

    $response->assertRedirect(route('admin.invoices.index'));

    $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
});

test('invoice gets a uid on creation', function () {
    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.store'), [
            'title' => 'UID Test Invoice',
        ]);

    $invoice = Invoice::where('title', 'UID Test Invoice')->first();

    expect($invoice->uid)->not->toBeNull();
    expect($invoice->uid)->not->toBeEmpty();
});

test('invoice settings can be updated', function () {
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->patch(route('admin.invoices.settings.update', $invoice), [
            'show_quantity' => true,
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    $invoice->refresh();

    expect($invoice->settings['show_quantity'])->toBeTrue();
});

test('invoice settings validation works', function () {
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->patch(route('admin.invoices.settings.update', $invoice), [
            'show_quantity' => 'invalid',
        ]);

    $response->assertSessionHasErrors('show_quantity');
});

test('new invoice has default settings from config', function () {
    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.store'), [
            'title' => 'Settings Test Invoice',
        ]);

    $invoice = Invoice::where('title', 'Settings Test Invoice')->first();

    expect($invoice->settings)->toBeArray();
    expect($invoice->settings['show_quantity'])->toBeFalse();
});
