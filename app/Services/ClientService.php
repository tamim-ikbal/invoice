<?php

namespace App\Services;

use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientService
{
    /**
     * @return array{clients: AnonymousResourceCollection}
     */
    public function index(): array
    {
        return [
            'clients' => ClientResource::collection(
                Client::withCount('invoices')->latest()->paginate()
            ),
        ];
    }
}
