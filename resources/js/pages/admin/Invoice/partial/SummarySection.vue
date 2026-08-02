<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import type { InvoiceEdit } from '@/types';

const props = defineProps<{
    invoice: InvoiceEdit;
}>();

function toNumber(amount: string): number {
    return parseFloat(amount.replace(/[^0-9.-]/g, ''));
}

const hasCarriedIn = computed(() => toNumber(props.invoice.adjustment_in_amount) > 0);
const hasCarriedOut = computed(() => toNumber(props.invoice.adjustment_out_amount) > 0);

/**
 * A carried out balance can outgrow what is left on the invoice when items or
 * payments change after the fact. The due clamps at zero, so warn instead.
 */
const overCarried = computed(
    () =>
        toNumber(props.invoice.adjustment_out_amount) >
        toNumber(props.invoice.total_amount) -
            toNumber(props.invoice.paid_amount),
);
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Summary</CardTitle>
        </CardHeader>
        <CardContent>
            <div class="space-y-3">
                <template v-if="hasCarriedIn">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">Items</span>
                        <span class="font-medium">
                            {{ invoice.items_amount }}
                        </span>
                    </div>
                    <Separator />
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">
                            Carried forward from previous invoice
                        </span>
                        <span class="font-medium">
                            {{ invoice.adjustment_in_amount }}
                        </span>
                    </div>
                    <Separator />
                </template>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">Total</span>
                    <span class="font-medium">{{ invoice.total_amount }}</span>
                </div>
                <Separator />
                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">Paid</span>
                    <span class="font-medium text-green-600">
                        {{ invoice.paid_amount }}
                    </span>
                </div>
                <Separator />

                <template v-if="hasCarriedOut">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">
                            Carried to next invoice
                        </span>
                        <span class="font-medium">
                            {{ invoice.adjustment_out_amount }}
                        </span>
                    </div>
                    <Separator />
                </template>

                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">Due</span>
                    <span class="font-medium text-red-600">
                        {{ invoice.due_amount }}
                    </span>
                </div>

                <p v-if="overCarried" class="text-xs text-red-600">
                    More has been carried forward than this invoice still owes.
                    Reduce the adjustment or add the missing items back.
                </p>
            </div>
        </CardContent>
    </Card>
</template>
