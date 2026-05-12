<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateTaskAction;
use App\Actions\Admin\DeleteTaskAction;
use App\Actions\Admin\UpdateTaskAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTaskRequest;
use App\Http\Requests\Admin\UpdateTaskRequest;
use App\Models\Invoice;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Store a newly created task for the given invoice.
     */
    public function store(StoreTaskRequest $request, Invoice $invoice, CreateTaskAction $action): RedirectResponse
    {
        $action->handle($invoice, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Task added.')]);

        return to_route('admin.invoices.edit', $invoice);
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Invoice $invoice, Task $task, UpdateTaskAction $action): RedirectResponse
    {
        $action->handle($task, $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Task updated.')]);

        return to_route('admin.invoices.edit', $invoice);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Invoice $invoice, Task $task, DeleteTaskAction $action): RedirectResponse
    {
        $action->handle($task);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Task deleted.')]);

        return to_route('admin.invoices.edit', $invoice);
    }
}
