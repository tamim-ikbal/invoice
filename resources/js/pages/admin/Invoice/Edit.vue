<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ExternalLink } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { index as invoicesIndex } from '@/routes/admin/invoices';
import type { Client, InvoiceEdit, StatusOption } from '@/types';
import DeleteModal from './partial/DeleteModal.vue';
import GeneralSection from './partial/GeneralSection.vue';
import PaymentsSection from './partial/PaymentsSection.vue';
import SummarySection from './partial/SummarySection.vue';
import TasksSection from './partial/TasksSection.vue';

type Props = {
    invoice: InvoiceEdit;
    clients: Client[];
    statuses: StatusOption[];
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

        <div class="flex flex-col gap-6">
            <GeneralSection
                :invoice="invoice"
                :clients="clients"
                :statuses="statuses"
            />
            <TasksSection :invoice="invoice" />
            <PaymentsSection :invoice="invoice" />
            <SummarySection :invoice="invoice" />
        </div>

        <Separator />

        <div
            class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10"
        >
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Danger zone</p>
                <p class="text-sm">
                    Deleting this invoice is permanent and cannot be undone.
                </p>
            </div>
            <DeleteModal :invoice-id="invoice.id" />
        </div>
    </div>
</template>
