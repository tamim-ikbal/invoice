<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
import type { Invoice } from '@/types';
import CreateModal from './partial/CreateModal.vue';
import DeleteModal from './partial/DeleteModal.vue';

type Props = {
    invoices: {
        data: Invoice[];
        links: Record<string, string | null>[];
        meta?: Record<string, unknown>;
    };
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

function statusVariant(status: string) {
    switch (status) {
        case 'draft':
            return 'secondary';
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

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="flex items-center justify-between">
            <Heading title="Invoices" />
            <CreateModal />
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Title</TableHead>
                    <TableHead>Client</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Date</TableHead>
                    <TableHead class="text-right">Total</TableHead>
                    <TableHead class="w-[70px]" />
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
                            <Badge :variant="statusVariant(invoice.status)">
                                {{ invoice.status }}
                            </Badge>
                        </TableCell>
                        <TableCell>{{ invoice.date }}</TableCell>
                        <TableCell class="text-right">
                            ${{ Number(invoice.total_amount).toFixed(2) }}
                        </TableCell>
                        <TableCell>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                    >
                                        <MoreHorizontal class="h-4 w-4" />
                                        <span class="sr-only">Open menu</span>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem as-child>
                                        <Link :href="edit.url(invoice.id)">
                                            Edit
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DeleteModal :invoice-id="invoice.id" />
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                </template>
                <TableEmpty v-else :colspan="6">
                    <p class="text-muted-foreground">
                        No invoices found. Create your first invoice to get
                        started.
                    </p>
                </TableEmpty>
            </TableBody>
        </Table>
    </div>
</template>
