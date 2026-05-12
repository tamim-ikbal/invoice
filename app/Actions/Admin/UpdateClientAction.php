<?php

namespace App\Actions\Admin;

use App\Models\Client;

class UpdateClientAction
{
    public function handle(Client $client, array $data): Client
    {
        $client->update($data);

        return $client;
    }
}
