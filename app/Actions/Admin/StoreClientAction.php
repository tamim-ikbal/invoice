<?php

namespace App\Actions\Admin;

use App\Models\Client;

class StoreClientAction
{
    public function handle(array $data): Client
    {
        return Client::create($data);
    }
}
