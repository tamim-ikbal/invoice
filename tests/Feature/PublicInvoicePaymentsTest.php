<?php

use App\Models\Invoice;
use App\Models\Payment;

test('public payments endpoint returns payments for invoice', function () {
    $invoice = Invoice::factory()->create();
    Payment::factory()->for($invoice)->create([
        'title' => 'Installment 1',
        'status' => 'paid',
    ]);
    Payment::factory()->for($invoice)->create([
        'title' => null,
        'status' => 'pending',
    ]);

    $response = $this->getJson(route('public.invoice.payments', $invoice->uid));

    $response->assertOk();
    $response->assertJsonCount(2, 'payments');
    $response->assertJsonPath('payments.0.title', 'Installment 1');
    $response->assertJsonPath('payments.1.title', null);
});

test('public payments endpoint returns 404 for invalid uid', function () {
    $response = $this->getJson(route('public.invoice.payments', 'invalid-uid'));

    $response->assertNotFound();
});

test('public payments endpoint returns empty array when no payments', function () {
    $invoice = Invoice::factory()->create();

    $response = $this->getJson(route('public.invoice.payments', $invoice->uid));

    $response->assertOk();
    $response->assertJsonCount(0, 'payments');
});
