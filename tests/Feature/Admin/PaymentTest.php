<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentRecordedNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->user = User::factory()->create();
});

// --- Payment Title ---

test('payment can be created with title', function () {
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.payments.store', $invoice), [
            'title' => 'Installment 1',
            'amount' => 100,
            'date' => '2026-05-15',
            'status' => 'unpaid',
            'payment_method' => 'payoneer',
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    $this->assertDatabaseHas('payments', [
        'invoice_id' => $invoice->id,
        'title' => 'Installment 1',
    ]);
});

test('payment can be created without title', function () {
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.payments.store', $invoice), [
            'amount' => 100,
            'date' => '2026-05-15',
            'status' => 'unpaid',
            'payment_method' => 'payoneer',
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    $this->assertDatabaseHas('payments', [
        'invoice_id' => $invoice->id,
        'title' => null,
    ]);
});

test('payment title is returned on edit page', function () {
    $invoice = Invoice::factory()->create();
    Payment::factory()->for($invoice)->create(['title' => 'Final Payment']);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.invoices.edit', $invoice));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Invoice/Edit')
        ->loadDeferredProps(fn ($reload) => $reload
            ->where('payments.0.title', 'Final Payment')
        )
    );
});

test('payment title can be updated', function () {
    $invoice = Invoice::factory()->create();
    $payment = Payment::factory()->for($invoice)->create(['title' => 'Old Title']);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.invoices.payments.update', [$invoice, $payment]), [
            'title' => 'New Title',
            'amount' => $payment->amount,
            'date' => $payment->date->format('Y-m-d'),
            'status' => $payment->status->value,
            'payment_method' => $payment->payment_method->value,
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    expect($payment->refresh()->title)->toBe('New Title');
});

// --- Payment with pending status ---

test('payment can be created with pending status', function () {
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.payments.store', $invoice), [
            'amount' => 100,
            'date' => '2026-05-15',
            'status' => 'pending',
            'payment_method' => 'payoneer',
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    $this->assertDatabaseHas('payments', [
        'invoice_id' => $invoice->id,
        'status' => 'pending',
    ]);
});

// --- Payment Notifications ---

test('creating payment with paid status sends notification to client', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $invoice = Invoice::factory()->for($client)->create();

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.payments.store', $invoice), [
            'amount' => 100,
            'date' => '2026-05-15',
            'status' => 'paid',
            'payment_method' => 'payoneer',
        ]);

    Notification::assertSentTo($client, PaymentRecordedNotification::class);
});

test('creating payment with pending status sends notification to client', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $invoice = Invoice::factory()->for($client)->create();

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.payments.store', $invoice), [
            'amount' => 100,
            'date' => '2026-05-15',
            'status' => 'pending',
            'payment_method' => 'payoneer',
        ]);

    Notification::assertSentTo($client, PaymentRecordedNotification::class);
});

test('creating payment with unpaid status does not send notification', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $invoice = Invoice::factory()->for($client)->create();

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.payments.store', $invoice), [
            'amount' => 100,
            'date' => '2026-05-15',
            'status' => 'unpaid',
            'payment_method' => 'payoneer',
        ]);

    Notification::assertNotSentTo($client, PaymentRecordedNotification::class);
});

test('updating payment with paid status sends notification to client', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $invoice = Invoice::factory()->for($client)->create();
    $payment = Payment::factory()->for($invoice)->create(['status' => 'unpaid']);

    $this
        ->actingAs($this->user)
        ->put(route('admin.invoices.payments.update', [$invoice, $payment]), [
            'amount' => 200,
            'date' => '2026-05-15',
            'status' => 'paid',
            'payment_method' => 'payoneer',
        ]);

    Notification::assertSentTo($client, PaymentRecordedNotification::class);
});

test('updating payment without changing status does not send notification', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $invoice = Invoice::factory()->for($client)->create();
    $payment = Payment::factory()->for($invoice)->create(['status' => 'paid']);

    $this
        ->actingAs($this->user)
        ->put(route('admin.invoices.payments.update', [$invoice, $payment]), [
            'amount' => 999,
            'date' => '2026-05-15',
            'status' => 'paid',
            'payment_method' => 'payoneer',
        ]);

    Notification::assertNotSentTo($client, PaymentRecordedNotification::class);
});

test('updating payment from unpaid to pending sends notification', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $invoice = Invoice::factory()->for($client)->create();
    $payment = Payment::factory()->for($invoice)->create(['status' => 'unpaid']);

    $this
        ->actingAs($this->user)
        ->put(route('admin.invoices.payments.update', [$invoice, $payment]), [
            'amount' => 100,
            'date' => '2026-05-15',
            'status' => 'pending',
            'payment_method' => 'payoneer',
        ]);

    Notification::assertSentTo($client, PaymentRecordedNotification::class);
});

test('updating payment from paid to unpaid does not send notification', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $invoice = Invoice::factory()->for($client)->create();
    $payment = Payment::factory()->for($invoice)->create(['status' => 'paid']);

    $this
        ->actingAs($this->user)
        ->put(route('admin.invoices.payments.update', [$invoice, $payment]), [
            'amount' => 100,
            'date' => '2026-05-15',
            'status' => 'unpaid',
            'payment_method' => 'payoneer',
        ]);

    Notification::assertNotSentTo($client, PaymentRecordedNotification::class);
});

// --- BDT Rate ---

test('payment can be created with bdt_rate', function () {
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.payments.store', $invoice), [
            'amount' => 100,
            'date' => '2026-05-15',
            'status' => 'unpaid',
            'payment_method' => 'payoneer',
            'bdt_rate' => 121.50,
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    $this->assertDatabaseHas('payments', [
        'invoice_id' => $invoice->id,
        'bdt_rate' => 121.50,
    ]);
});

test('payment can be created without bdt_rate', function () {
    $invoice = Invoice::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.payments.store', $invoice), [
            'amount' => 100,
            'date' => '2026-05-15',
            'status' => 'unpaid',
            'payment_method' => 'payoneer',
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    $this->assertDatabaseHas('payments', [
        'invoice_id' => $invoice->id,
        'bdt_rate' => null,
    ]);
});

test('payment bdt_rate is returned on edit page', function () {
    $invoice = Invoice::factory()->create();
    Payment::factory()->for($invoice)->create(['bdt_rate' => 121.50]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.invoices.edit', $invoice));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Invoice/Edit')
        ->loadDeferredProps(fn ($reload) => $reload
            ->where('payments.0.bdt_rate', '121.50')
        )
    );
});

test('payment bdt_rate can be updated', function () {
    $invoice = Invoice::factory()->create();
    $payment = Payment::factory()->for($invoice)->create(['bdt_rate' => 100]);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.invoices.payments.update', [$invoice, $payment]), [
            'amount' => $payment->amount,
            'date' => $payment->date->format('Y-m-d'),
            'status' => $payment->status->value,
            'payment_method' => $payment->payment_method->value,
            'bdt_rate' => 125.75,
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $invoice));

    expect((float) $payment->refresh()->bdt_rate)->toBe(125.75);
});

test('no notification sent if invoice has no client', function () {
    Notification::fake();

    $invoice = Invoice::factory()->create(['client_id' => null]);

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.payments.store', $invoice), [
            'amount' => 100,
            'date' => '2026-05-15',
            'status' => 'paid',
            'payment_method' => 'payoneer',
        ]);

    Notification::assertNothingSent();
});
