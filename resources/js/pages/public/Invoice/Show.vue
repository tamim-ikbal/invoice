<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
    };
}

const { invoice } = defineProps<Props>();
</script>

<template>
    <Head :title="'Invoice - ' + invoice.title" />

    <Card>
        <CardHeader>
            <CardTitle>{{ invoice.title }}</CardTitle>
            <CardDescription>
                <template v-if="invoice.client">
                    {{ invoice.client.name }} &bull;
                </template>
                {{ invoice.date ?? 'No date' }}
            </CardDescription>
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
                        <TableHead class="text-right">Amount</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="item in invoice.items" :key="item.name">
                        <TableCell>{{ item.name }}</TableCell>
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
        </CardContent>
    </Card>
</template>
