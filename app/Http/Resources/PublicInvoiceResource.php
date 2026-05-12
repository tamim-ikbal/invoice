<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Invoice */
class PublicInvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uid' => $this->uid,
            'title' => $this->title,
            'date' => $this->date->format('Y-m-d'),
            'status' => $this->status,
            'client' => $this->whenLoaded('client', fn () => [
                'name' => $this->client->name,
            ]),
            'tasks' => $this->tasks->map(fn ($task) => [
                'id' => $task->id,
                'name' => $task->name,
                'amount' => $task->amount,
            ]),
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'due_amount' => $this->due_amount,
        ];
    }
}
