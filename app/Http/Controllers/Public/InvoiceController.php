<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PublicInvoiceService;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Display the public invoice.
     */
    public function show(string $uid, PublicInvoiceService $service): Response
    {
        return Inertia::render('public/Invoice/Show', $service->show($uid));
    }
}
