<?php

use App\Models\Invoice;
use App\Models\InvoiceItem;

test('public invoice page can be viewed without authentication', function () {
    $invoice = Invoice::factory()->create();
    InvoiceItem::factory()->for($invoice)->count(2)->create();

    $response = $this->get(route('public.invoice.show', $invoice->uid));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('public/Invoice/Show')
        ->has('invoice')
    );
});

test('public invoice page returns 404 for invalid uid', function () {
    $response = $this->get(route('public.invoice.show', 'nonexistent-uid'));

    $response->assertNotFound();
});

test('public invoice page shows correct amounts', function () {
    $invoice = Invoice::factory()->create();
    InvoiceItem::factory()->for($invoice)->create(['amount' => 100]);
    InvoiceItem::factory()->for($invoice)->create(['amount' => 200]);

    $response = $this->get(route('public.invoice.show', $invoice->uid));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('public/Invoice/Show')
        ->has('invoice')
    );
});

test('soft deleted invoices are not accessible publicly', function () {
    $invoice = Invoice::factory()->create();
    $invoice->delete();

    $response = $this->get(route('public.invoice.show', $invoice->uid));

    $response->assertNotFound();
});
