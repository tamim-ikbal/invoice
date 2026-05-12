<?php

namespace App\Services;

use App\Http\Resources\ClientResource;
use App\Models\Client;

class ClientIndexService
{
    /**
     * @return array{clients: \Illuminate\Http\Resources\Json\AnonymousResourceCollection}
     */
    public function handle(): array
    {
        $clients = Client::withCount('invoices')->latest()->paginate();

        return [
            'clients' => ClientResource::collection($clients),
        ];
    }
}
