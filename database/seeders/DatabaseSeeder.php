<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $clients = Client::factory(3)->create();

        $clients->each(function (Client $client) {
            Invoice::factory(2)
                ->for($client)
                ->create()
                ->each(function (Invoice $invoice) {
                    InvoiceItem::factory(3)->for($invoice)->create();
                    Payment::factory(1)->paid()->for($invoice)->create();
                    Payment::factory(1)->for($invoice)->create();
                });
        });

        // An invoice without a client
        Invoice::factory()
            ->create()
            ->each(function (Invoice $invoice) {
                InvoiceItem::factory(2)->for($invoice)->create();
            });
    }
}
