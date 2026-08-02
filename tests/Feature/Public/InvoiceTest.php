<?php

use App\Models\Adjustment;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;

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

test('public invoice shows the amount carried to the next invoice', function () {
    $client = Client::factory()->create();
    $source = Invoice::factory()->for($client)->create();
    InvoiceItem::factory()->for($source)->create(['quantity' => 1, 'amount' => 595]);
    Payment::factory()->for($source)->paid()->create(['amount' => 500]);
    $target = Invoice::factory()->for($client)->create();
    Adjustment::factory()->for($source, 'sourceInvoice')->for($target, 'targetInvoice')->create(['amount' => 95]);

    $this->get(route('public.invoice.show', $source->uid))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/Invoice/Show')
            ->where('invoice.adjustments_out.0.amount', '$95.00')
            ->where('invoice.adjustments_out.0.invoice_no', $target->invoice_no)
            ->where('invoice.paid_amount', '$500.00')
            ->where('invoice.due_amount', '$0.00')
        );
});

test('public invoice shows a balance carried forward from another invoice', function () {
    $client = Client::factory()->create();
    $source = Invoice::factory()->for($client)->create();
    $target = Invoice::factory()->for($client)->create();
    InvoiceItem::factory()->for($target)->create(['quantity' => 1, 'amount' => 300]);
    Adjustment::factory()->for($source, 'sourceInvoice')->for($target, 'targetInvoice')->create(['amount' => 95]);

    $this->get(route('public.invoice.show', $target->uid))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/Invoice/Show')
            ->where('invoice.adjustments_in.0.amount', '$95.00')
            ->where('invoice.adjustments_in.0.invoice_no', $source->invoice_no)
            ->where('invoice.items_amount', '$300.00')
            ->where('invoice.total_amount', '$395.00')
            ->where('invoice.due_amount', '$395.00')
        );
});

test('soft deleted invoices are not accessible publicly', function () {
    $invoice = Invoice::factory()->create();
    $invoice->delete();

    $response = $this->get(route('public.invoice.show', $invoice->uid));

    $response->assertNotFound();
});
