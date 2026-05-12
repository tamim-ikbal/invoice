<?php

namespace App\Services;

use App\Enums\InvoiceStatusEnum;
use App\Http\Resources\ClientResource;
use App\Http\Resources\InvoiceEditResource;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceEditService
{
    /**
     * @return array{invoice: InvoiceEditResource, clients: AnonymousResourceCollection, statuses: array<int, array{value: string, label: string}>}
     */
    public function handle(Invoice $invoice): array
    {
        $invoice->load(['items', 'payments', 'client']);

        return [
            'invoice' => InvoiceEditResource::make($invoice)->resolve(),
            'clients' => ClientResource::collection(Client::all()),
            'statuses' => array_map(
                fn ($case) => ['value' => $case->value, 'label' => ucfirst($case->value)],
                InvoiceStatusEnum::cases()
            ),
        ];
    }
}
