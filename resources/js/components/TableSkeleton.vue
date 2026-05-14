<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

withDefaults(
    defineProps<{
        columns?: number;
        rows?: number;
        headers?: string[];
    }>(),
    {
        columns: 4,
        rows: 5,
        headers: () => [],
    },
);
</script>

<template>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead
                    v-for="i in (headers.length || columns)"
                    :key="i"
                >
                    <template v-if="headers.length">
                        {{ headers[i - 1] }}
                    </template>
                    <Skeleton v-else class="h-4 w-20" />
                </TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="row in rows" :key="row">
                <TableCell
                    v-for="col in (headers.length || columns)"
                    :key="col"
                >
                    <Skeleton class="h-4 w-full" />
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
