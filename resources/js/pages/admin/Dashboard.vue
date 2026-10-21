<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Banknote, DollarSign, FileText, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes/admin';
import type {
    DashboardPeriod,
    DashboardPeriodOption,
    DashboardStats,
} from '@/types';

type Props = {
    period: DashboardPeriod;
    periods: DashboardPeriodOption[];
    stats: DashboardStats;
};

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Admin Dashboard',
                href: dashboard.url(),
            },
        ],
    },
});

const cards = computed(() => [
    { title: 'Total in USD', value: props.stats.total_usd, Icon: DollarSign },
    { title: 'Total in BDT', value: props.stats.total_bdt, Icon: Banknote },
    {
        title: 'Total Invoices',
        value: props.stats.total_invoices,
        Icon: FileText,
    },
    { title: 'Total Clients', value: props.stats.total_clients, Icon: Users },
]);

function selectPeriod(period: DashboardPeriod) {
    if (period === props.period) {
        return;
    }

    router.get(
        dashboard.url({ query: { period } }),
        {},
        { preserveState: true, preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <Heading title="Dashboard" variant="small" />
            <div
                class="inline-flex gap-1 self-start rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800"
            >
                <button
                    v-for="option in periods"
                    :key="option.value"
                    type="button"
                    @click="selectPeriod(option.value)"
                    :class="[
                        'rounded-md px-3.5 py-1.5 text-sm transition-colors',
                        period === option.value
                            ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                            : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
                    ]"
                >
                    {{ option.label }}
                </button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card v-for="card in cards" :key="card.title">
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0"
                >
                    <CardTitle class="text-sm font-medium">
                        {{ card.title }}
                    </CardTitle>
                    <component
                        :is="card.Icon"
                        class="h-4 w-4 text-muted-foreground"
                    />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ card.value }}</div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
