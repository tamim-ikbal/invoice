<script setup lang="ts">
import InvoiceController from '@/actions/App/Http/Controllers/Public/InvoiceController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { CreditCard } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    uid: string;
}>();

type PaymentItem = {
    title: string | null;
    amount: string;
    date: string;
    status: string;
    payment_method: string;
};

const open = ref(false);
const loading = ref(false);
const payments = ref<PaymentItem[]>([]);

async function fetchPayments() {
    loading.value = true;

    try {
        const response = await fetch(
            InvoiceController.payments.url(props.uid),
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            },
        );
        const data = await response.json();
        payments.value = data.payments;
    } finally {
        loading.value = false;
    }
}

watch(
    open,
    (isOpen) => {
        if (isOpen) {
            payments.value = [];
            fetchPayments();
        }
    },
    { immediate: true },
);
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm" :disabled="loading">
                <Spinner v-if="loading" class="mr-1.5 h-4 w-4" />
                <CreditCard v-else class="mr-1.5 h-4 w-4" />
                View Payments
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Payments</DialogTitle>
                <DialogDescription>
                    Payment history for this invoice.
                </DialogDescription>
            </DialogHeader>

            <div v-if="loading" class="flex justify-center py-8">
                <Spinner class="h-6 w-6" />
            </div>

            <template v-else>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Title</TableHead>
                            <TableHead>Amount</TableHead>
                            <TableHead>Date</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Method</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="payments.length">
                            <TableRow
                                v-for="(payment, index) in payments"
                                :key="index"
                            >
                                <TableCell>
                                    {{ payment.title ?? '-' }}
                                </TableCell>
                                <TableCell>
                                    {{ payment.amount }}
                                </TableCell>
                                <TableCell>
                                    {{ payment.date }}
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">
                                        {{ payment.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    {{ payment.payment_method }}
                                </TableCell>
                            </TableRow>
                        </template>
                        <TableEmpty v-else :colspan="5">
                            <p class="text-muted-foreground">
                                No payments recorded yet.
                            </p>
                        </TableEmpty>
                    </TableBody>
                </Table>
            </template>
        </DialogContent>
    </Dialog>
</template>
