<?php

namespace App\Models;

use App\Models\Scopes\OldestScope;
use Database\Factories\AdjustmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;

#[Fillable(['source_invoice_id', 'target_invoice_id', 'amount', 'note'])]
#[ScopedBy(OldestScope::class)]
class Adjustment extends Model
{
    /** @use HasFactory<AdjustmentFactory> */
    use HasFactory;

    /**
     * The invoice the balance was carried out of.
     *
     * Trashed invoices are still resolved so the audit trail keeps its invoice number.
     *
     * @return BelongsTo<Invoice, $this>
     */
    public function sourceInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'source_invoice_id')
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    /**
     * The invoice the balance was carried into, if it has been linked yet.
     *
     * @return BelongsTo<Invoice, $this>
     */
    public function targetInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'target_invoice_id')
            ->withoutGlobalScope(SoftDeletingScope::class);
    }
}
