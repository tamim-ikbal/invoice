<?php

use App\Enums\InvoiceStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('invoice auto-generates uid on creation', function () {
    $invoice = Invoice::factory()->create(['uid' => null]);

    expect($invoice->uid)->not->toBeNull();
    expect($invoice->uid)->toBeString();
});

test('invoice belongs to a client', function () {
    $client = Client::factory()->create();
    $invoice = Invoice::factory()->for($client)->create();

    expect($invoice->client)->toBeInstanceOf(Client::class);
    expect($invoice->client->id)->toBe($client->id);
});

test('invoice has many items', function () {
    $invoice = Invoice::factory()->create();
    InvoiceItem::factory()->for($invoice)->count(3)->create();

    expect($invoice->items)->toHaveCount(3);
});

test('invoice has many payments', function () {
    $invoice = Invoice::factory()->create();
    Payment::factory()->for($invoice)->count(2)->create();

    expect($invoice->payments)->toHaveCount(2);
});

test('total amount is sum of quantity times amount', function () {
    $invoice = Invoice::factory()->create();
    InvoiceItem::factory()->for($invoice)->create(['quantity' => 2, 'amount' => 150.50]);
    InvoiceItem::factory()->for($invoice)->create(['quantity' => 1, 'amount' => 249.50]);

    expect($invoice->total_amount)->toBe(550.5);
});

test('paid amount only counts paid payments', function () {
    $invoice = Invoice::factory()->create();
    Payment::factory()->for($invoice)->paid()->create(['amount' => 100]);
    Payment::factory()->for($invoice)->create(['amount' => 200, 'status' => PaymentStatusEnum::UNPAID]);

    expect((float) $invoice->paid_amount)->toBe(100.0);
});

test('due amount is total minus paid', function () {
    $invoice = Invoice::factory()->create();
    InvoiceItem::factory()->for($invoice)->create(['amount' => 500]);
    Payment::factory()->for($invoice)->paid()->create(['amount' => 200]);

    expect((float) $invoice->due_amount)->toBe(300.0);
});

test('invoice casts status to enum', function () {
    $invoice = Invoice::factory()->create(['status' => 'draft']);

    expect($invoice->status)->toBeInstanceOf(InvoiceStatusEnum::class);
    expect($invoice->status)->toBe(InvoiceStatusEnum::DRAFT);
});

test('invoice casts date to carbon', function () {
    $invoice = Invoice::factory()->create(['date' => '2026-05-12']);

    expect($invoice->date)->toBeInstanceOf(DateTimeInterface::class);
    expect($invoice->date->toDateString())->toBe('2026-05-12');
});

test('invoice uses soft deletes', function () {
    $invoice = Invoice::factory()->create();
    $invoice->delete();

    expect(Invoice::find($invoice->id))->toBeNull();
    expect(Invoice::withTrashed()->find($invoice->id))->not->toBeNull();
});

test('public url contains the uid', function () {
    $invoice = Invoice::factory()->create();

    expect($invoice->public_url)->toContain("/invoice/{$invoice->uid}");
});

test('new invoice gets default settings from config', function () {
    $invoice = Invoice::factory()->create();

    expect($invoice->settings)->toBeArray();
    expect($invoice->settings['show_quantity'])->toBeFalse();
});
