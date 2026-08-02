<?php

use App\Models\Adjustment;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->client = Client::factory()->create();
});

/**
 * Build an invoice with a single item and an optional paid payment.
 */
function invoiceWorth(Client $client, float $items, float $paid = 0): Invoice
{
    $invoice = Invoice::factory()->for($client)->create();
    InvoiceItem::factory()->for($invoice)->create(['quantity' => 1, 'amount' => $items]);

    if ($paid > 0) {
        Payment::factory()->for($invoice)->paid()->create(['amount' => $paid]);
    }

    return $invoice->fresh();
}

// --- Creating ---

test('adjustment can be carried to another invoice', function () {
    $source = invoiceWorth($this->client, 595, 500);
    $target = invoiceWorth($this->client, 300);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.store', $source), [
            'amount' => 95,
            'target_invoice_id' => $target->id,
            'note' => 'Balance carried forward',
        ]);

    $response->assertRedirect(route('admin.invoices.edit', $source));

    $this->assertDatabaseHas('adjustments', [
        'source_invoice_id' => $source->id,
        'target_invoice_id' => $target->id,
        'amount' => 95,
        'note' => 'Balance carried forward',
    ]);

    expect((float) $source->fresh()->due_amount)->toBe(0.0);
    expect((float) $target->fresh()->total_amount)->toBe(395.0);
});

test('adjustment can be created without a target', function () {
    $source = invoiceWorth($this->client, 595, 500);

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.store', $source), ['amount' => 95])
        ->assertRedirect(route('admin.invoices.edit', $source));

    $this->assertDatabaseHas('adjustments', [
        'source_invoice_id' => $source->id,
        'target_invoice_id' => null,
    ]);

    expect((float) $source->fresh()->due_amount)->toBe(0.0);
});

// --- Amount validation ---

test('adjustment amount can equal the remaining balance exactly', function () {
    $source = invoiceWorth($this->client, 595, 500);

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.store', $source), ['amount' => 95])
        ->assertSessionHasNoErrors();
});

test('adjustment amount cannot exceed the remaining balance', function () {
    $source = invoiceWorth($this->client, 595, 500);

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.store', $source), ['amount' => 95.01])
        ->assertSessionHasErrors('amount');

    $this->assertDatabaseCount('adjustments', 0);
});

test('adjustment amount must be greater than zero', function () {
    $source = invoiceWorth($this->client, 595);

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.store', $source), ['amount' => 0])
        ->assertSessionHasErrors('amount');
});

test('remaining balance excludes balances already carried out', function () {
    $source = invoiceWorth($this->client, 595, 500);
    Adjustment::factory()->for($source, 'sourceInvoice')->unapplied()->create(['amount' => 60]);

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.store', $source), ['amount' => 36])
        ->assertSessionHasErrors('amount');

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.store', $source), ['amount' => 35])
        ->assertSessionHasNoErrors();
});

test('a balance carried in can be carried forward again', function () {
    $first = invoiceWorth($this->client, 100);
    $second = invoiceWorth($this->client, 0);
    Adjustment::factory()->for($first, 'sourceInvoice')->for($second, 'targetInvoice')->create(['amount' => 100]);

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.store', $second), ['amount' => 100])
        ->assertSessionHasNoErrors();
});

// --- Target validation ---

test('adjustment cannot target its own invoice', function () {
    $source = invoiceWorth($this->client, 595);

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.store', $source), [
            'amount' => 95,
            'target_invoice_id' => $source->id,
        ])
        ->assertSessionHasErrors('target_invoice_id');
});

test('adjustment cannot target a soft deleted invoice', function () {
    $source = invoiceWorth($this->client, 595);
    $target = invoiceWorth($this->client, 100);
    $target->delete();

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.store', $source), [
            'amount' => 95,
            'target_invoice_id' => $target->id,
        ])
        ->assertSessionHasErrors('target_invoice_id');
});

test('adjustment cannot target an invoice belonging to another client', function () {
    $source = invoiceWorth($this->client, 595);
    $target = invoiceWorth(Client::factory()->create(), 100);

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.store', $source), [
            'amount' => 95,
            'target_invoice_id' => $target->id,
        ])
        ->assertSessionHasErrors('target_invoice_id');
});

test('adjustment cannot create a direct cycle', function () {
    $first = invoiceWorth($this->client, 595);
    $second = invoiceWorth($this->client, 300);
    Adjustment::factory()->for($second, 'sourceInvoice')->for($first, 'targetInvoice')->create(['amount' => 50]);

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.store', $first), [
            'amount' => 95,
            'target_invoice_id' => $second->id,
        ])
        ->assertSessionHasErrors('target_invoice_id');
});

// --- Updating ---

test('updating an adjustment excludes its own amount from the balance check', function () {
    $source = invoiceWorth($this->client, 595, 500);
    $adjustment = Adjustment::factory()->for($source, 'sourceInvoice')->unapplied()->create(['amount' => 95]);

    $this
        ->actingAs($this->user)
        ->put(route('admin.invoices.adjustments.update', [$source, $adjustment]), ['amount' => 95])
        ->assertSessionHasNoErrors();

    $this
        ->actingAs($this->user)
        ->put(route('admin.invoices.adjustments.update', [$source, $adjustment]), ['amount' => 96])
        ->assertSessionHasErrors('amount');
});

test('adjustment cannot be updated through a different invoice', function () {
    $source = invoiceWorth($this->client, 595, 500);
    $other = invoiceWorth($this->client, 300);
    $adjustment = Adjustment::factory()->for($source, 'sourceInvoice')->unapplied()->create(['amount' => 95]);

    $this
        ->actingAs($this->user)
        ->put(route('admin.invoices.adjustments.update', [$other, $adjustment]), ['amount' => 50])
        ->assertNotFound();

    expect((float) $adjustment->fresh()->amount)->toBe(95.0);
});

// --- Deleting ---

test('adjustment can be deleted', function () {
    $source = invoiceWorth($this->client, 595, 500);
    $adjustment = Adjustment::factory()->for($source, 'sourceInvoice')->unapplied()->create(['amount' => 95]);

    $this
        ->actingAs($this->user)
        ->delete(route('admin.invoices.adjustments.destroy', [$source, $adjustment]))
        ->assertRedirect(route('admin.invoices.edit', $source));

    $this->assertDatabaseCount('adjustments', 0);
    expect((float) $source->fresh()->due_amount)->toBe(95.0);
});

test('deleting an invoice removes its adjustments and restores the counterpart due', function () {
    $source = invoiceWorth($this->client, 595, 500);
    $target = invoiceWorth($this->client, 300);
    Adjustment::factory()->for($source, 'sourceInvoice')->for($target, 'targetInvoice')->create(['amount' => 95]);

    $this
        ->actingAs($this->user)
        ->delete(route('admin.invoices.destroy', $target))
        ->assertRedirect();

    $this->assertDatabaseCount('adjustments', 0);
    expect((float) $source->fresh()->due_amount)->toBe(95.0);
});

// --- Linking ---

test('pending adjustment can be linked from the target invoice', function () {
    $source = invoiceWorth($this->client, 595, 500);
    $target = invoiceWorth($this->client, 300);
    $adjustment = Adjustment::factory()->for($source, 'sourceInvoice')->unapplied()->create(['amount' => 95]);

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.link', [$target, $adjustment]))
        ->assertRedirect(route('admin.invoices.edit', $target));

    expect($adjustment->fresh()->target_invoice_id)->toBe($target->id);
    expect((float) $target->fresh()->total_amount)->toBe(395.0);
});

test('linking rejects an adjustment that is already carried forward', function () {
    $source = invoiceWorth($this->client, 595, 500);
    $target = invoiceWorth($this->client, 300);
    $other = invoiceWorth($this->client, 200);
    $adjustment = Adjustment::factory()->for($source, 'sourceInvoice')->for($other, 'targetInvoice')->create(['amount' => 95]);

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.link', [$target, $adjustment]))
        ->assertSessionHasErrors('target_invoice_id');

    expect($adjustment->fresh()->target_invoice_id)->toBe($other->id);
});

test('linking rejects the source invoice itself', function () {
    $source = invoiceWorth($this->client, 595, 500);
    $adjustment = Adjustment::factory()->for($source, 'sourceInvoice')->unapplied()->create(['amount' => 95]);

    $this
        ->actingAs($this->user)
        ->post(route('admin.invoices.adjustments.link', [$source, $adjustment]))
        ->assertSessionHasErrors('target_invoice_id');
});

test('a carried balance can be unlinked', function () {
    $source = invoiceWorth($this->client, 595, 500);
    $target = invoiceWorth($this->client, 300);
    $adjustment = Adjustment::factory()->for($source, 'sourceInvoice')->for($target, 'targetInvoice')->create(['amount' => 95]);

    $this
        ->actingAs($this->user)
        ->delete(route('admin.invoices.adjustments.unlink', [$target, $adjustment]))
        ->assertRedirect(route('admin.invoices.edit', $target));

    expect($adjustment->fresh()->target_invoice_id)->toBeNull();
    expect((float) $target->fresh()->total_amount)->toBe(300.0);
});

// --- Edit page ---

test('adjustments are returned on the edit page', function () {
    $source = invoiceWorth($this->client, 595, 500);
    $target = invoiceWorth($this->client, 300);
    Adjustment::factory()->for($source, 'sourceInvoice')->for($target, 'targetInvoice')->create(['amount' => 95]);

    $this
        ->actingAs($this->user)
        ->get(route('admin.invoices.edit', $source))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Invoice/Edit')
            ->where('invoice.due_amount', '$0.00')
            ->where('invoice.adjustment_out_amount', '$95.00')
            ->loadDeferredProps(fn ($reload) => $reload
                ->where('adjustments.0.direction', 'out')
                ->where('adjustments.0.amount', '95.00')
                ->where('adjustments.0.invoice.invoice_no', $target->invoice_no)
            )
        );
});

test('the receiving invoice sees the carried balance and pending list', function () {
    $source = invoiceWorth($this->client, 595, 500);
    $target = invoiceWorth($this->client, 300);
    Adjustment::factory()->for($source, 'sourceInvoice')->unapplied()->create(['amount' => 95]);

    $this
        ->actingAs($this->user)
        ->get(route('admin.invoices.edit', $target))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Invoice/Edit')
            ->loadDeferredProps(fn ($reload) => $reload
                ->where('pendingAdjustments.0.amount', '95.00')
                ->where('pendingAdjustments.0.invoice.invoice_no', $source->invoice_no)
            )
        );
});
