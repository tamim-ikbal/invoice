<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClientController from '@/actions/App/Http/Controllers/Admin/ClientController';
import Heading from '@/components/Heading.vue';
import { index } from '@/routes/admin/clients';
import type { Client } from '@/types';
import DeleteModal from './partial/DeleteModal.vue';
import ClientForm from './partial/Form.vue';

type Props = {
    client: Client;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Clients',
                href: index.url(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Edit Client" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <Heading title="Edit Client" />

        <ClientForm
            :client="client"
            :form-action="
                ClientController.update.form({ client: props.client.id })
            "
        />

        <div class="space-y-6">
            <Heading
                variant="small"
                title="Delete client"
                description="Delete this client and remove them from associated invoices"
            />
            <div
                class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10"
            >
                <div
                    class="relative space-y-0.5 text-red-600 dark:text-red-100"
                >
                    <p class="font-medium">Warning</p>
                    <p class="text-sm">
                        Please proceed with caution, this cannot be undone.
                    </p>
                </div>
                <DeleteModal :client-id="client.id" />
            </div>
        </div>
    </div>
</template>
