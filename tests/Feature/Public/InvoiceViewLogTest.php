<?php

use App\Jobs\LogInvoiceView;
use App\Models\Invoice;
use Illuminate\Support\Facades\Queue;

test('viewing public invoice dispatches LogInvoiceView job', function () {
    Queue::fake();

    $invoice = Invoice::factory()->create();

    $this->get(route('public.invoice.show', $invoice->uid));

    Queue::assertPushed(LogInvoiceView::class, function (LogInvoiceView $job) use ($invoice) {
        return $job->invoiceId === $invoice->id;
    });
});

test('job receives correct IP and User-Agent', function () {
    Queue::fake();

    $invoice = Invoice::factory()->create();

    $this
        ->withHeaders([
            'User-Agent' => 'TestBrowser/1.0',
        ])
        ->get(route('public.invoice.show', $invoice->uid));

    Queue::assertPushed(LogInvoiceView::class, function (LogInvoiceView $job) {
        return $job->browser === 'TestBrowser/1.0'
            && $job->ip === '127.0.0.1';
    });
});
