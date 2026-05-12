<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Invoice */
class InvoiceEditResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'title' => $this->title,
            'status' => $this->status,
            'date' => $this->date->format('Y-m-d'),
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ]),
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'due_amount' => $this->due_amount,
            'public_url' => $this->public_url,
            'created_at' => $this->created_at,
            'tasks' => $this->tasks->map(fn ($task) => [
                'id' => $task->id,
                'name' => $task->name,
                'amount' => $task->amount,
            ]),
            'payments' => $this->payments->map(fn ($payment) => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'date' => $payment->date->format('Y-m-d'),
                'status' => $payment->status,
                'payment_method' => $payment->payment_method,
            ]),
        ];
    }
}
