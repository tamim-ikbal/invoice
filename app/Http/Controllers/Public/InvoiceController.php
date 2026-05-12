<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PublicInvoiceShowService;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Display the public invoice.
     */
    public function show(string $uid, PublicInvoiceShowService $service): Response
    {
        return Inertia::render('public/Invoice/Show', $service->handle($uid));
    }
}
