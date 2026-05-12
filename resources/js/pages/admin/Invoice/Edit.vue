<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ExternalLink } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { index as invoicesIndex } from '@/routes/admin/invoices';
import type { Client, InvoiceEdit, StatusOption } from '@/types';
import GeneralSection from './partial/GeneralSection.vue';
import ItemsSection from './partial/ItemsSection.vue';
import PaymentsSection from './partial/PaymentsSection.vue';
import SettingsModal from './partial/SettingsModal.vue';
import SummarySection from './partial/SummarySection.vue';

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
            <ItemsSection :invoice="invoice" />
            <PaymentsSection :invoice="invoice" />
            <SummarySection :invoice="invoice" />
        </div>

    </div>
</template>
