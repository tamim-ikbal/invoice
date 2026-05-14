<?php

namespace App\Http\Resources;

use App\Models\Invoice;
use App\Services\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Invoice */
class InvoiceResource extends JsonResource
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
            'status' => $this->status->label(),
            'date' => Helper::dateFormat($this->date),
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ]),
            'total_amount' => Helper::moneyFormat($this->total_amount),
            'paid_amount' => Helper::moneyFormat($this->paid_amount),
            'due_amount' => Helper::moneyFormat($this->due_amount),
            'public_url' => $this->public_url,
            'created_at' => Helper::dateFormat($this->created_at, withTime: true),
        ];
    }
}
