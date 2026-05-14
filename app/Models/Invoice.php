<?php

namespace App\Models;

use App\Enums\InvoiceStatusEnum;
use App\Enums\PaymentStatusEnum;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['uid', 'client_id', 'title', 'status', 'date', 'settings', 'invoice_no'])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->uid)) {
                $invoice->uid = uniqid();
            }

            $invoice->settings = array_merge(
                config('settings.invoice'),
                $invoice->settings ?? [],
            );
        });

        static::created(function (Invoice $invoice) {
            $invoice->invoice_no = str($invoice->id)->padLeft(6, '0')->prepend('INV')->toString();
            $invoice->save();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => InvoiceStatusEnum::class,
            'date' => 'date',
            'settings' => 'json',
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return HasMany<InvoiceItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<InvoiceViewLog, $this>
     */
    public function viewLogs(): HasMany
    {
        return $this->hasMany(InvoiceViewLog::class);
    }

    /**
     * @return Attribute<float, never>
     */
    protected function totalAmount(): Attribute
    {
        return Attribute::get(fn () => $this->items->sum(fn ($item) => $item->quantity * $item->amount));
    }

    /**
     * @return Attribute<float, never>
     */
    protected function paidAmount(): Attribute
    {
        return Attribute::get(fn () => $this->payments->where('status', PaymentStatusEnum::PAID)->sum('amount'));
    }

    /**
     * @return Attribute<float, never>
     */
    protected function dueAmount(): Attribute
    {
        return Attribute::get(fn () => $this->total_amount - $this->paid_amount);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function publicUrl(): Attribute
    {
        return Attribute::get(fn () => url("/invoice/{$this->uid}"));
    }
}
