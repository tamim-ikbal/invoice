<?php

namespace App\Services;

use App\Http\Resources\ClientResource;
use App\Models\Client;

class ClientEditService
{
    /**
     * @return array{client: ClientResource}
     */
    public function handle(Client $client): array
    {
        return [
            'client' => ClientResource::make($client)->resolve(),
        ];
    }
}
