<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { FileText, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { defineAsyncComponent, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import TablePagination from '@/components/TablePagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index } from '@/routes/admin/clients';
import type { Client, Paginated } from '@/types';

const CreateModal = defineAsyncComponent(
    () => import('./partial/CreateModal.vue'),
);
const EditModal = defineAsyncComponent(
    () => import('./partial/EditModal.vue'),
);
const DeleteModal = defineAsyncComponent(
    () => import('./partial/DeleteModal.vue'),
);
const InvoicesModal = defineAsyncComponent(
    () => import('./partial/InvoicesModal.vue'),
);

type Props = {
    clients: Paginated<Client>;
};

defineProps<Props>();

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

const createOpen = ref(false);

const editingClient = ref<Client | null>(null);
const editOpen = ref(false);

const deletingClient = ref<Client | null>(null);
const deleteOpen = ref(false);

const viewingClient = ref<Client | null>(null);
const invoicesOpen = ref(false);

function openEdit(client: Client) {
    editingClient.value = client;
    editOpen.value = true;
}

function openDelete(client: Client) {
    deletingClient.value = client;
    deleteOpen.value = true;
}

function openInvoices(client: Client) {
    viewingClient.value = client;
    invoicesOpen.value = true;
}
</script>

<template>
    <Head title="Clients" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <Heading title="Clients" />
            <Button @click="createOpen = true">
                <Plus class="mr-2 h-4 w-4" />
                New Client
            </Button>
        </div>

        <Card>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Invoices</TableHead>
                                <TableHead class="w-[130px]">
                                    <span class="sr-only">Actions</span>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="clients.data.length">
                                <TableRow
                                    v-for="client in clients.data"
                                    :key="client.id"
                                >
                                    <TableCell class="font-medium">
                                        {{ client.name }}
                                    </TableCell>
                                    <TableCell>{{ client.email }}</TableCell>
                                    <TableCell>{{
                                        client.invoices_count ?? 0
                                    }}</TableCell>
                                    <TableCell>
                                        <div
                                            class="flex items-center justify-end gap-1"
                                        >
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-8 w-8"
                                                @click="openInvoices(client)"
                                            >
                                                <FileText class="h-4 w-4" />
                                                <span class="sr-only"
                                                    >View Invoices</span
                                                >
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-8 w-8"
                                                @click="openEdit(client)"
                                            >
                                                <Pencil class="h-4 w-4" />
                                                <span class="sr-only">Edit</span>
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-8 w-8 text-destructive hover:text-destructive"
                                                @click="openDelete(client)"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                                <span class="sr-only">Delete</span>
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableEmpty v-else :colspan="4">
                                No clients found.
                            </TableEmpty>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <TablePagination :links="clients.meta.links" />
    </div>

    <CreateModal v-model:open="createOpen" />

    <EditModal
        v-if="editingClient"
        v-model:open="editOpen"
        :client="editingClient"
    />

    <DeleteModal
        v-if="deletingClient"
        v-model:open="deleteOpen"
        :client-id="deletingClient.id"
    />

    <InvoicesModal
        v-if="viewingClient"
        v-model:open="invoicesOpen"
        :client="viewingClient"
    />
</template>
