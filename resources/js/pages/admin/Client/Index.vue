<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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
import { index, create, edit } from '@/routes/admin/clients';
import type { Client } from '@/types';
import DeleteModal from './partial/DeleteModal.vue';

type Props = {
    clients: {
        data: Client[];
    };
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
</script>

<template>
    <Head title="Clients" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <Heading title="Clients" />
            <Button as-child>
                <Link :href="create.url()">New Client</Link>
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
                            <TableHead class="w-12">
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
                                <TableCell>
                                    <Link
                                        :href="edit.url(client.id)"
                                        class="font-medium hover:underline"
                                    >
                                        {{ client.name }}
                                    </Link>
                                </TableCell>
                                <TableCell>{{ client.email }}</TableCell>
                                <TableCell>{{
                                    client.invoices_count ?? 0
                                }}</TableCell>
                                <TableCell>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                            >
                                                <MoreHorizontal />
                                                <span class="sr-only"
                                                    >Actions</span
                                                >
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child>
                                                <Link
                                                    :href="
                                                        edit.url(client.id)
                                                    "
                                                >
                                                    Edit
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DeleteModal
                                                :client-id="client.id"
                                            />
                                        </DropdownMenuContent>
                                    </DropdownMenu>
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
    </div>
</template>
