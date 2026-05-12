<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\ClientData;
use App\Models\Client;

class StoreClientAction
{
    public function handle(ClientData $data): Client
    {
        return Client::create($data->toArray());
    }
}
