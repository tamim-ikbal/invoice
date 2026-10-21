<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->travelTo('2026-10-21 12:00:00');
});

test('guests cannot access the admin dashboard', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('dashboard renders zeroed stats when there is no data', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Dashboard')
        ->where('period', 'all')
        ->has('periods', 4)
        ->where('periods.0', ['value' => '3m', 'label' => '3 Months'])
        ->where('periods.3', ['value' => 'all', 'label' => 'All Time'])
        ->where('stats', [
            'total_usd' => '$0.00',
            'total_bdt' => '৳0.00',
            'total_invoices' => 0,
            'total_clients' => 0,
        ])
    );
});

test('dashboard all time stats only count paid payments', function () {
    $clients = Client::factory()->count(2)->create();
    $invoice = Invoice::factory()->for($clients[0])->create();
    Invoice::factory()->for($clients[1])->create();

    Payment::factory()->for($invoice)->paid()->create(['amount' => 100, 'bdt_rate' => 120]);
    Payment::factory()->for($invoice)->paid()->create(['amount' => 50.50, 'bdt_rate' => 110]);
    // Paid payment without a BDT rate counts towards USD but adds nothing to BDT.
    Payment::factory()->for($invoice)->paid()->create(['amount' => 25, 'bdt_rate' => null]);
    // Unpaid payments must be ignored.
    Payment::factory()->for($invoice)->create(['amount' => 1000, 'bdt_rate' => 120]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_usd', '$175.50')
        ->where('stats.total_bdt', '৳17,555.00')
        ->where('stats.total_invoices', 2)
        ->where('stats.total_clients', 2)
    );
});

test('dashboard excludes soft deleted invoices and their payments', function () {
    $invoice = Invoice::factory()->create();
    Payment::factory()->for($invoice)->paid()->create(['amount' => 100, 'bdt_rate' => 120]);

    $invoice->delete();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_usd', '$0.00')
        ->where('stats.total_bdt', '৳0.00')
        ->where('stats.total_invoices', 0)
    );
});

test('dashboard filters stats by period', function (string $period, array $expected) {
    $dates = [
        'one month ago' => '2026-09-21',
        'four months ago' => '2026-06-21',
        'nine months ago' => '2026-01-21',
        'two years ago' => '2024-10-21',
    ];

    foreach ($dates as $date) {
        $client = Client::factory()->create(['created_at' => $date]);
        $invoice = Invoice::factory()->for($client)->create(['date' => $date]);
        Payment::factory()->for($invoice)->paid()->create([
            'amount' => 100,
            'bdt_rate' => 100,
            'date' => $date,
        ]);
    }

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.dashboard', ['period' => $period]));

    $response->assertInertia(fn ($page) => $page
        ->where('period', $period)
        ->where('stats', $expected)
    );
})->with([
    '3 months' => ['3m', ['total_usd' => '$100.00', 'total_bdt' => '৳10,000.00', 'total_invoices' => 1, 'total_clients' => 1]],
    '6 months' => ['6m', ['total_usd' => '$200.00', 'total_bdt' => '৳20,000.00', 'total_invoices' => 2, 'total_clients' => 2]],
    '1 year' => ['1y', ['total_usd' => '$300.00', 'total_bdt' => '৳30,000.00', 'total_invoices' => 3, 'total_clients' => 3]],
    'all time' => ['all', ['total_usd' => '$400.00', 'total_bdt' => '৳40,000.00', 'total_invoices' => 4, 'total_clients' => 4]],
]);

test('dashboard period includes records on the boundary date', function () {
    $invoice = Invoice::factory()->create(['date' => '2026-07-21']);
    Payment::factory()->for($invoice)->paid()->create(['amount' => 80, 'bdt_rate' => 120, 'date' => '2026-07-21']);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.dashboard', ['period' => '3m']));

    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_usd', '$80.00')
        ->where('stats.total_invoices', 1)
    );
});

test('dashboard filters payments by payment date rather than invoice date', function () {
    $invoice = Invoice::factory()->create(['date' => '2025-01-01']);
    Payment::factory()->for($invoice)->paid()->create(['amount' => 60, 'bdt_rate' => 100, 'date' => '2026-10-01']);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.dashboard', ['period' => '3m']));

    $response->assertInertia(fn ($page) => $page
        ->where('stats.total_usd', '$60.00')
        ->where('stats.total_bdt', '৳6,000.00')
        ->where('stats.total_invoices', 0)
    );
});

test('dashboard falls back to all time for an invalid period', function () {
    Invoice::factory()->create(['date' => '2020-01-01']);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.dashboard', ['period' => 'invalid']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('period', 'all')
        ->where('stats.total_invoices', 1)
    );
});
