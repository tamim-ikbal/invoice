<script setup lang="ts">
import { ScrollText } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import InvoiceController from '@/actions/App/Http/Controllers/Admin/InvoiceController';
import ReadMoreText from '@/components/ReadMoreText.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
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
import type { InvoiceEdit, InvoiceViewLog, PaginationLink } from '@/types';

const props = defineProps<{
    invoice: InvoiceEdit;
}>();

const open = ref(false);
const loading = ref(false);

type PaginatedViewLogs = {
    data: InvoiceViewLog[];
    meta: {
        current_page: number;
        last_page: number;
        links: PaginationLink[];
    };
};

const logs = ref<PaginatedViewLogs | null>(null);

async function fetchLogs(url?: string) {
    loading.value = true;

    try {
        const fetchUrl =
            url ??
            InvoiceController.viewLogs.url({ invoice: props.invoice.id });
        const response = await fetch(fetchUrl, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        logs.value = await response.json();
    } finally {
        loading.value = false;
    }
}

watch(
    open,
    (isOpen) => {
        if (isOpen) {
            logs.value = null;
            fetchLogs();
        }
    },
    { immediate: true },
);
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm">
                <ScrollText class="mr-2 h-4 w-4" />
                Logs
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-4xl">
            <DialogHeader>
                <DialogTitle>View Logs</DialogTitle>
                <DialogDescription>
                    Tracking visits to the public invoice page.
                </DialogDescription>
            </DialogHeader>

            <div v-if="loading && !logs" class="flex justify-center py-8">
                <Spinner class="h-6 w-6" />
            </div>

            <template v-else-if="logs">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>IP</TableHead>
                            <TableHead>Browser</TableHead>
                            <TableHead>Country</TableHead>
                            <TableHead>Viewed At</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="logs.data.length">
                            <TableRow
                                v-for="log in logs.data"
                                :key="log.id"
                            >
                                <TableCell class="font-mono text-sm">
                                    {{ log.ip }}
                                </TableCell>
                                <TableCell class="whitespace-normal">
                                    <ReadMoreText :text="log.browser" />
                                </TableCell>
                                <TableCell>
                                    {{ log.country ?? '-' }}
                                </TableCell>
                                <TableCell class="whitespace-nowrap">
                                    {{ log.viewed_at }}
                                </TableCell>
                            </TableRow>
                        </template>
                        <TableEmpty v-else :colspan="4">
                            <p class="text-muted-foreground">
                                No views recorded yet.
                            </p>
                        </TableEmpty>
                    </TableBody>
                </Table>

                <div
                    v-if="logs.meta.last_page > 1"
                    class="flex items-center justify-center gap-2 pt-2"
                >
                    <template
                        v-for="(link, index) in logs.meta.links"
                        :key="index"
                    >
                        <Button
                            v-if="link.label !== '...'"
                            variant="outline"
                            size="sm"
                            :disabled="!link.url || link.active || loading"
                            @click="link.url && fetchLogs(link.url)"
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
