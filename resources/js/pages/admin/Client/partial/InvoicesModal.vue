<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import ClientController from '@/actions/App/Http/Controllers/Admin/ClientController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
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
import { edit as invoiceEdit } from '@/routes/admin/invoices';
import type { Client, Invoice, PaginationLink } from '@/types';

const props = defineProps<{
    client: Client;
}>();

const open = defineModel<boolean>('open', { default: false });

type PaginatedInvoices = {
    data: Invoice[];
    meta: {
        current_page: number;
        last_page: number;
        links: PaginationLink[];
    };
};

const invoices = ref<PaginatedInvoices | null>(null);
const loading = ref(false);

async function fetchInvoices(url?: string) {
    loading.value = true;

    try {
        const fetchUrl =
            url ?? ClientController.invoices.url({ client: props.client.id });
        const response = await fetch(fetchUrl, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        invoices.value = await response.json();
    } finally {
        loading.value = false;
    }
}

watch(open, (isOpen) => {
    if (isOpen) {
        invoices.value = null;
        fetchInvoices();
    }
}, { immediate: true });

function statusVariant(status: string) {
    switch (status) {
        case 'draft':
            return 'secondary';
        case 'in_progress':
            return 'outline';
        case 'sent':
            return 'outline';
        case 'paid':
            return 'default';
        default:
            return 'secondary';
    }
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-3xl">
            <DialogHeader>
                <DialogTitle>{{ client.name }}'s Invoices</DialogTitle>
                <DialogDescription>
                    Viewing all invoices for this client.
                </DialogDescription>
            </DialogHeader>

            <div v-if="loading && !invoices" class="flex justify-center py-8">
                <Spinner class="h-6 w-6" />
            </div>

            <template v-else-if="invoices">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Title</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Date</TableHead>
                            <TableHead class="text-right">Total</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="invoices.data.length">
                            <TableRow
                                v-for="invoice in invoices.data"
                                :key="invoice.id"
                            >
                                <TableCell>
                                    <Link
                                        :href="invoiceEdit.url(invoice.id)"
                                        class="font-medium underline-offset-4 hover:underline"
                                    >
                                        {{ invoice.title }}
                                    </Link>
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="statusVariant(invoice.status)"
                                    >
                                        {{ invoice.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ invoice.date ?? '-' }}</TableCell>
                                <TableCell class="text-right">
                                    {{ invoice.total_amount }}
                                </TableCell>
                            </TableRow>
                        </template>
                        <TableEmpty v-else :colspan="4">
                            <p class="text-muted-foreground">
                                No invoices found for this client.
                            </p>
                        </TableEmpty>
                    </TableBody>
                </Table>

                <div
                    v-if="invoices.meta.last_page > 1"
                    class="flex items-center justify-center gap-2 pt-2"
                >
                    <template
                        v-for="(link, index) in invoices.meta.links"
                        :key="index"
                    >
                        <Button
                            v-if="link.label !== '...'"
                            variant="outline"
                            size="sm"
                            :disabled="!link.url || link.active || loading"
                            @click="link.url && fetchInvoices(link.url)"
                        >
                            <span v-html="link.label" />
                        </Button>
                        <span v-else class="px-1 text-muted-foreground">
                            ...
                        </span>
                    </template>
                </div>
            </template>
        </DialogContent>
    </Dialog>
</template>
