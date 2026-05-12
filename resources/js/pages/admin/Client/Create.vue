<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ClientController from '@/actions/App/Http/Controllers/Admin/ClientController';
import Heading from '@/components/Heading.vue';
import { index, create } from '@/routes/admin/clients';
import ClientForm from './partial/Form.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Clients',
                href: index.url(),
            },
            {
                title: 'New Client',
                href: create.url(),
            },
        ],
    },
});
</script>

<template>
    <Head title="New Client" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <Heading title="New Client" />

        <ClientForm :form-action="ClientController.store.form()" />
    </div>
</template>
