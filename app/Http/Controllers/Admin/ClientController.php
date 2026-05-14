<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\DeleteClientAction;
use App\Actions\Admin\StoreClientAction;
use App\Actions\Admin\UpdateClientAction;
use App\DTOs\Admin\ClientData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Client;
use App\Models\Invoice;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(ClientService $service): Response
    {
        return Inertia::render('admin/Client/Index', $service->index());
    }

    /**
     * Store a newly created client.
     */
    public function store(StoreClientRequest $request, StoreClientAction $action): RedirectResponse
    {
        $action->handle(ClientData::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Client created.')]);

        return to_route('admin.clients.index');
    }

    /**
     * Update the specified client.
     */
    public function update(UpdateClientRequest $request, Client $client, UpdateClientAction $action): RedirectResponse
    {
        $action->handle($client, ClientData::fromRequest($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Client updated.')]);

        return to_route('admin.clients.index');
    }

    /**
     * Remove the specified client.
     */
    public function destroy(Client $client, DeleteClientAction $action): RedirectResponse
    {
        $action->handle($client);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Client deleted.')]);

        return to_route('admin.clients.index');
    }

    /**
     * Return paginated invoices for the specified client.
     */
    public function invoices(Client $client): JsonResponse
    {
        $invoices = Invoice::where('client_id', $client->id)
            ->with('client')
            ->latest()
            ->paginate();

        return InvoiceResource::collection($invoices)->response();
    }
}
