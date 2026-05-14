<script setup lang="ts">
import { Deferred, Head } from '@inertiajs/vue3';
import { ExternalLink } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardAction,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index as invoicesIndex } from '@/routes/admin/invoices';
import type { Client, InvoiceEdit, InvoiceItem, Payment, StatusOption } from '@/types';
import GeneralSection from './partial/GeneralSection.vue';
import ItemsSection from './partial/ItemsSection.vue';
import PaymentsSection from './partial/PaymentsSection.vue';
import SettingsModal from './partial/SettingsModal.vue';
import SummarySection from './partial/SummarySection.vue';

type Props = {
    invoice: InvoiceEdit;
    clients: Client[];
    statuses: StatusOption[];
    items?: InvoiceItem[];
    payments?: Payment[];
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
</script>

<template>
    <Head title="Edit Invoice" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="flex items-center justify-between">
            <Heading title="Edit Invoice" />
            <div class="flex items-center gap-2">
                <SettingsModal :invoice="invoice" />
                <Button
                    variant="outline"
                    as="a"
                    :href="invoice.public_url"
                    target="_blank"
                >
                    <ExternalLink class="mr-2 h-4 w-4" />
                    View Public URL
                </Button>
            </div>
        </div>

        <div class="flex flex-col gap-6">
            <GeneralSection
                :invoice="invoice"
                :clients="clients"
                :statuses="statuses"
            />

            <Deferred data="items">
                <template #fallback>
                    <Card>
                        <CardHeader>
                            <CardTitle>Items</CardTitle>
                            <CardAction>
                                <Skeleton class="h-8 w-24 rounded-md" />
                            </CardAction>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead
                                            v-if="invoice.settings.show_quantity"
                                            class="w-[100px] text-right"
                                        >
                                            Qty
                                        </TableHead>
                                        <TableHead class="min-w-[110px] text-right">Amount</TableHead>
                                        <TableHead class="w-[70px]" />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="i in 3" :key="i">
                                        <TableCell><Skeleton class="h-4 w-full" /></TableCell>
                                        <TableCell v-if="invoice.settings.show_quantity"><Skeleton class="ml-auto h-4 w-8" /></TableCell>
                                        <TableCell><Skeleton class="ml-auto h-4 w-16" /></TableCell>
                                        <TableCell><Skeleton class="ml-auto h-8 w-8 rounded-md" /></TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </template>

                <ItemsSection :invoice="invoice" :items="items ?? []" />
            </Deferred>

            <Deferred data="payments">
                <template #fallback>
                    <Card>
                        <CardHeader>
                            <CardTitle>Payments</CardTitle>
                            <CardAction>
                                <Skeleton class="h-8 w-28 rounded-md" />
                            </CardAction>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Method</TableHead>
                                        <TableHead class="w-[70px]" />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="i in 2" :key="i">
                                        <TableCell><Skeleton class="h-4 w-16" /></TableCell>
                                        <TableCell><Skeleton class="h-4 w-20" /></TableCell>
                                        <TableCell><Skeleton class="h-5 w-12 rounded-full" /></TableCell>
                                        <TableCell><Skeleton class="h-4 w-16" /></TableCell>
                                        <TableCell><Skeleton class="ml-auto h-8 w-8 rounded-md" /></TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </template>

                <PaymentsSection :invoice="invoice" :payments="payments ?? []" />
            </Deferred>

            <SummarySection :items="items" :payments="payments" />
        </div>

    </div>
</template>
