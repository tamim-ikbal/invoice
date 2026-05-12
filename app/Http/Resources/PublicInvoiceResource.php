<?php

namespace App\Http\Resources;

use App\Models\Invoice;
use App\Services\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Invoice */
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
            'date' => Helper::dateFormat($this->date),
            'status' => $this->status,
            'client' => $this->whenLoaded('client', fn () => [
                'name' => $this->client->name,
            ]),
            'items' => $this->items->map(fn ($item) => [
                'name' => $item->name,
                'quantity' => $item->quantity,
                'amount' => Helper::moneyFormat($item->amount),
            ]),
            'total_amount' => Helper::moneyFormat($this->total_amount),
            'paid_amount' => Helper::moneyFormat($this->paid_amount),
            'due_amount' => Helper::moneyFormat($this->due_amount),
            'settings' => $this->settings,
        ];
    }
}
