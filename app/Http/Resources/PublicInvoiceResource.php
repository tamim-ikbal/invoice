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
            'invoice_no' => $this->invoice_no,
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
            'adjustments_in' => $this->adjustmentsIn->map(fn ($adjustment) => [
                'invoice_no' => $adjustment->sourceInvoice?->invoice_no,
                'amount' => Helper::moneyFormat($adjustment->amount),
            ]),
            'adjustments_out' => $this->adjustments->map(fn ($adjustment) => [
                'invoice_no' => $adjustment->targetInvoice?->invoice_no,
                'amount' => Helper::moneyFormat($adjustment->amount),
            ]),
            'items_amount' => Helper::moneyFormat($this->items_amount),
            'total_amount' => Helper::moneyFormat($this->total_amount),
            'paid_amount' => Helper::moneyFormat($this->paid_amount),
            'due_amount' => Helper::moneyFormat($this->due_amount),
            'settings' => $this->settings,
        ];
    }
}
