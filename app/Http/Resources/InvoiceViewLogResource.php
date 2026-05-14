<?php

namespace App\Http\Resources;

use App\Models\InvoiceViewLog;
use App\Services\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin InvoiceViewLog */
class InvoiceViewLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ip' => $this->ip,
            'browser' => $this->browser,
            'country' => $this->country,
            'viewed_at' => Helper::dateFormat($this->viewed_at, withTime: true),
        ];
    }
}
