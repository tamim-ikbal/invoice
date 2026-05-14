<script setup lang="ts">
import { Deferred } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import type { InvoiceItem, Payment } from '@/types';

const props = defineProps<{
    items?: InvoiceItem[];
    payments?: Payment[];
}>();

function formatMoney(amount: number): string {
    return '$' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

const totalAmount = computed(() => {
    if (!props.items) {
        return 0;
    }

    return props.items.reduce((sum, item) => sum + item.quantity * parseFloat(item.amount), 0);
});

const paidAmount = computed(() => {
    if (!props.payments) {
        return 0;
    }

    return props.payments
        .filter((p) => p.status === 'paid')
        .reduce((sum, p) => sum + parseFloat(p.amount), 0);
});

const dueAmount = computed(() => totalAmount.value - paidAmount.value);
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Summary</CardTitle>
        </CardHeader>
        <CardContent>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">Total</span>
                    <Deferred :data="['items', 'payments']">
                        <template #fallback>
                            <Skeleton class="h-6 w-[60px]" />
                        </template>
                        <span class="font-medium">
                            {{ formatMoney(totalAmount) }}
                        </span>
                    </Deferred>
                </div>
                <Separator />
                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">Paid</span>
                    <Deferred :data="['items', 'payments']">
                        <template #fallback>
                            <Skeleton class="h-6 w-[60px]" />
                        </template>
                        <span class="font-medium text-green-600">
                            {{ formatMoney(paidAmount) }}
                        </span>
                    </Deferred>
                </div>
                <Separator />
                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">Due</span>
                    <Deferred :data="['items', 'payments']">
                        <template #fallback>
                            <Skeleton class="h-6 w-[60px]" />
                        </template>
                        <span class="font-medium text-red-600">
                            {{ formatMoney(dueAmount) }}
                        </span>
                    </Deferred>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
