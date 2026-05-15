<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Jobs\LogInvoiceView;
use App\Models\Invoice;
use App\Services\Helper;
use App\Services\PublicInvoiceService;
use Illuminate\Http\JsonResponse;
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

    /**
     * Return payments for the public invoice.
     */
    public function payments(string $uid): JsonResponse
    {
        $invoice = Invoice::where('uid', $uid)
            ->with('payments')
            ->firstOrFail();

        return response()->json([
            'payments' => $invoice->payments->map(fn ($payment) => [
                'title' => $payment->title,
                'amount' => Helper::moneyFormat($payment->amount),
                'date' => Helper::dateFormat($payment->date),
                'status' => $payment->status->label(),
                'payment_method' => $payment->payment_method->label(),
            ]),
        ]);
    }
}
