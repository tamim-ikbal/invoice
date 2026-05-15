<script setup lang="ts">
import ReadMoreText from '@/components/ReadMoreText.vue';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Head } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';

const PaymentsModal = defineAsyncComponent(
    () => import('./partial/PaymentsModal.vue'),
);

interface Props {
    invoice: {
        uid: string;
        title: string;
        date: string | null;
        status: string;
        client: { name: string } | null;
        items: Array<{ name: string; quantity: number; amount: string }>;
        total_amount: string;
        paid_amount: string;
        due_amount: string;
        settings: { show_quantity: boolean };
        invoice_no: string | null;
    };
}

const { invoice } = defineProps<Props>();
</script>

<template>
    <Head :title="'Invoice - ' + invoice.title" />

    <Card>
        <CardHeader>
            <div class="flex items-center justify-between gap-4 md:gap-6">
                <div class="flex flex-col">
                    <CardTitle>{{ invoice.title }}</CardTitle>
                    <CardDescription class="text-sm text-muted-foreground">
                        <template v-if="invoice.client">
                            {{ invoice.client.name }}
                        </template>
                        <template v-if="invoice.date">
                            - {{ invoice.date }}
                        </template>
                    </CardDescription>
                </div>
                <div>
                    <h1
                        class="text-base font-bold md:text-xl"
                        v-if="invoice.invoice_no"
                    >
                        {{ invoice.invoice_no }}
                    </h1>
                </div>
            </div>
        </CardHeader>

        <CardContent>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead
                            v-if="invoice.settings?.show_quantity"
                            class="text-right"
                        >
                            Qty
                        </TableHead>
                        <TableHead class="min-w-[110px] text-right">
                            Amount
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="item in invoice.items" :key="item.name">
                        <TableCell class="whitespace-normal">
                            <ReadMoreText :text="item.name" />
                        </TableCell>
                        <TableCell
                            v-if="invoice.settings?.show_quantity"
                            class="text-right"
                        >
                            {{ item.quantity }}
                        </TableCell>
                        <TableCell class="text-right">
                            {{ item.amount }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <Separator class="my-6" />

            <div class="space-y-0">
                <div class="flex justify-between py-2">
                    <span>Total</span>
                    <span>{{ invoice.total_amount }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span>Paid Amount</span>
                    <span>{{ invoice.paid_amount }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span>Due Amount</span>
                    <span class="font-semibold">{{ invoice.due_amount }}</span>
                </div>
            </div>

            <div class="flex justify-start pt-4">
                <PaymentsModal :uid="invoice.uid" />
            </div>
        </CardContent>
    </Card>
</template>
