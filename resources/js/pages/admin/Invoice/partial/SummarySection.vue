<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import type { InvoiceEdit } from '@/types';

type Props = {
    invoice: InvoiceEdit;
};

defineProps<Props>();
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
                    <span class="font-medium">
                        {{ invoice.total_amount }}
                    </span>
                </div>
                <Separator />
                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">Paid</span>
                    <span class="font-medium text-green-600">
                        {{ invoice.paid_amount }}
                    </span>
                </div>
                <Separator />
                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">Due</span>
                    <span class="font-medium text-red-600">
                        {{ invoice.due_amount }}
                    </span>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
