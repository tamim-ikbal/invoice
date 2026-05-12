<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\DeleteClientAction;
use App\Actions\Admin\StoreClientAction;
use App\Actions\Admin\UpdateClientAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientCreateService;
use App\Services\ClientEditService;
use App\Services\ClientIndexService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(ClientIndexService $service): Response
    {
        return Inertia::render('admin/Client/Index', $service->handle());
    }

    /**
     * Show the form for creating a new client.
     */
    public function create(ClientCreateService $service): Response
    {
        return Inertia::render('admin/Client/Create', $service->handle());
    }

    /**
     * Store a newly created client.
     */
    public function store(StoreClientRequest $request, StoreClientAction $action): RedirectResponse
    {
        $action->handle($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Client created.')]);

        return to_route('admin.clients.index');
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client, ClientEditService $service): Response
    {
        return Inertia::render('admin/Client/Edit', $service->handle($client));
    }

    /**
     * Update the specified client.
     */
    public function update(UpdateClientRequest $request, Client $client, UpdateClientAction $action): RedirectResponse
    {
        $action->handle($client, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Client updated.')]);

        return to_route('admin.clients.edit', $client);
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
}
