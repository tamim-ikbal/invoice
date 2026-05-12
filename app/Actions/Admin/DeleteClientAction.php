<?php

namespace App\Actions\Admin;

use App\Models\Client;

class DeleteClientAction
{
    public function handle(Client $client): void
    {
        $client->delete();
    }
}
