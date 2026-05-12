<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\ClientData;
use App\Models\Client;

class UpdateClientAction
{
    public function handle(Client $client, ClientData $data): Client
    {
        $client->update($data->toArray());

        return $client;
    }
}
