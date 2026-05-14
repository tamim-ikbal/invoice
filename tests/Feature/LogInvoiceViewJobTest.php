<?php

use App\Jobs\LogInvoiceView;
use App\Models\Invoice;
use App\Models\InvoiceViewLog;
use Illuminate\Support\Facades\Http;

test('job creates a view log record with correct data', function () {
    Http::fake([
        'api.ipinfo.io/*' => Http::response([
            'ip' => '103.145.50.100',
            'country_code' => 'BD',
            'country' => 'Bangladesh',
            'continent_code' => 'AS',
            'continent' => 'Asia',
        ]),
    ]);

    $invoice = Invoice::factory()->create();

    $job = new LogInvoiceView(
        invoiceId: $invoice->id,
        ip: '103.145.50.100',
        browser: 'Mozilla/5.0',
        viewedAt: '2026-05-14 12:00:00',
    );

    $job->handle();

    $log = InvoiceViewLog::first();

    expect($log)->not->toBeNull();
    expect($log->invoice_id)->toBe($invoice->id);
    expect($log->ip)->toBe('103.145.50.100');
    expect($log->browser)->toBe('Mozilla/5.0');
    expect($log->country)->toBe('Bangladesh');
});

test('job handles failed geolocation gracefully', function () {
    Http::fake([
        'api.ipinfo.io/*' => Http::response([], 403),
    ]);

    $invoice = Invoice::factory()->create();

    $job = new LogInvoiceView(
        invoiceId: $invoice->id,
        ip: '8.8.8.8',
        browser: 'Mozilla/5.0',
        viewedAt: '2026-05-14 12:00:00',
    );

    $job->handle();

    $log = InvoiceViewLog::first();

    expect($log)->not->toBeNull();
    expect($log->country)->toBeNull();
});

test('job skips geolocation for localhost IPs', function () {
    Http::fake();

    $invoice = Invoice::factory()->create();

    $job = new LogInvoiceView(
        invoiceId: $invoice->id,
        ip: '127.0.0.1',
        browser: 'Mozilla/5.0',
        viewedAt: '2026-05-14 12:00:00',
    );

    $job->handle();

    Http::assertNothingSent();

    $log = InvoiceViewLog::first();

    expect($log)->not->toBeNull();
    expect($log->country)->toBeNull();
});

test('job skips geolocation for private network IPs', function () {
    Http::fake();

    $invoice = Invoice::factory()->create();

    $job = new LogInvoiceView(
        invoiceId: $invoice->id,
        ip: '192.168.1.1',
        browser: 'Mozilla/5.0',
        viewedAt: '2026-05-14 12:00:00',
    );

    $job->handle();

    Http::assertNothingSent();

    $log = InvoiceViewLog::first();

    expect($log)->not->toBeNull();
    expect($log->country)->toBeNull();
});
