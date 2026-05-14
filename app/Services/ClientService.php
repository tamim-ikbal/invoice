<?php

namespace App\Services;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use Inertia\DeferProp;
use Inertia\Inertia;

class ClientService
{
    /**
     * @return array{clients: DeferProp}
     */
    public function index(): array
    {
        return [
            'clients' => Inertia::defer(fn () => ClientResource::collection(
                Client::withCount('invoices')->latest()->paginate()
            )),
        ];
    }
}
