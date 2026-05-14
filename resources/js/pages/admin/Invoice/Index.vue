<script setup lang="ts">
import { Deferred, Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import TablePagination from '@/components/TablePagination.vue';
import TableSkeleton from '@/components/TableSkeleton.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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
import { index as invoicesIndex, edit } from '@/routes/admin/invoices';
import type { Invoice, Paginated } from '@/types';
import CreateModal from './partial/CreateModal.vue';
import DeleteModal from './partial/DeleteModal.vue';

type Props = {
    invoices?: Paginated<Invoice>;
};

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Invoices',
                href: invoicesIndex.url(),
            },
        ],
    },
});

const navigatingId = ref<number | null>(null);
const deletingInvoice = ref<Invoice | null>(null);
const deleteOpen = ref(false);

function navigateEdit(invoice: Invoice) {
    navigatingId.value = invoice.id;
    router.visit(edit.url(invoice.id), {
        onFinish: () => {
            navigatingId.value = null;
        },
    });
}

function openDelete(invoice: Invoice) {
    deletingInvoice.value = invoice;
    deleteOpen.value = true;
}

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
    <Head title="Invoices" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <Heading title="Invoices" />
            <CreateModal />
        </div>

        <Deferred data="invoices">
            <template #fallback>
                <Card>
                    <CardContent>
                        <TableSkeleton
                            :columns="6"
                            :rows="5"
                            :headers="['Title', 'Client', 'Status', 'Date', 'Total', '']"
                        />
                    </CardContent>
                </Card>
            </template>

            <Card>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Title</TableHead>
                                <TableHead>Client</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead class="text-right">Total</TableHead>
                                <TableHead class="w-[100px]" />
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="invoices?.data.length">
                                <TableRow
                                    v-for="invoice in invoices.data"
                                    :key="invoice.id"
                                >
                                    <TableCell>
                                        <Link
                                            :href="edit.url(invoice.id)"
                                            class="font-medium underline-offset-4 hover:underline"
                                        >
                                            {{ invoice.title }}
                                        </Link>
                                    </TableCell>
                                    <TableCell>
                                        {{ invoice.client?.name ?? '-' }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            :variant="
                                                statusVariant(invoice.status)
                                            "
                                        >
                                            {{ invoice.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>{{
                                        invoice.date ?? '-'
                                    }}</TableCell>
                                    <TableCell class="text-right">
                                        {{ invoice.total_amount }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center justify-end gap-1">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-8 w-8"
                                                :disabled="navigatingId === invoice.id"
                                                @click="navigateEdit(invoice)"
                                            >
                                                <Spinner v-if="navigatingId === invoice.id" />
                                                <Pencil v-else class="h-4 w-4" />
                                                <span class="sr-only">Edit</span>
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-8 w-8 text-destructive hover:text-destructive"
                                                @click="openDelete(invoice)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                                <span class="sr-only">Delete</span>
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableEmpty v-else :colspan="6">
                                <p class="text-muted-foreground">
                                    No invoices found. Create your first invoice to
                                    get started.
                                </p>
                            </TableEmpty>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <TablePagination v-if="invoices" :links="invoices.meta.links" />
        </Deferred>
    </div>

    <DeleteModal
        v-if="deletingInvoice"
        v-model:open="deleteOpen"
        :invoice-id="deletingInvoice.id"
    />
</template>
