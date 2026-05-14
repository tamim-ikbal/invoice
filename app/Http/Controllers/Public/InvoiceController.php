<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Jobs\LogInvoiceView;
use App\Services\PublicInvoiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Display the public invoice.
     */
    public function show(Request $request, string $uid, PublicInvoiceService $service): Response
    {
        $data = $service->show($uid);

        LogInvoiceView::dispatch(
            invoiceId: $data['invoiceId'],
            ip: $request->ip(),
            browser: $request->userAgent() ?? '',
            viewedAt: now()->toDateTimeString(),
        );

        return Inertia::render('public/Invoice/Show', [
            'invoice' => $data['invoice'],
        ]);
    }
}
