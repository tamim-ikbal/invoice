<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
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

interface Props {
    invoice: {
        uid: string;
        title: string;
        date: string;
        status: string;
        client: { name: string } | null;
        tasks: Array<{ name: string; amount: number }>;
        total_amount: number;
        paid_amount: number;
        due_amount: number;
    };
}

const { invoice } = defineProps<Props>();

function formatAmount(amount: number): string {
    return `$${amount.toFixed(2)}`;
}
</script>

<template>
    <Head :title="'Invoice - ' + invoice.title" />

    <Card>
        <CardHeader>
            <CardTitle>Payment for #{{ invoice.title }}</CardTitle>
            <CardDescription>
                <template v-if="invoice.client">
                    {{ invoice.client.name }} &bull;
                </template>
                {{ invoice.date }}
            </CardDescription>
        </CardHeader>

        <CardContent>
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead class="text-right">Amount</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="task in invoice.tasks" :key="task.name">
                        <TableCell>{{ task.name }}</TableCell>
                        <TableCell class="text-right">{{
                            formatAmount(task.amount)
                        }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <Separator class="my-6" />

            <div class="space-y-0">
                <div class="flex justify-between py-2">
                    <span>Total</span>
                    <span>{{ formatAmount(invoice.total_amount) }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span>Paid Amount</span>
                    <span>{{ formatAmount(invoice.paid_amount) }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span>Due Amount</span>
                    <span class="font-semibold">{{
                        formatAmount(invoice.due_amount)
                    }}</span>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
