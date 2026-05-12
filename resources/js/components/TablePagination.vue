<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import type { PaginationLink } from '@/types';

const props = defineProps<{
    links: PaginationLink[];
}>();

const previousLink = computed(() => props.links[0]);
const nextLink = computed(() => props.links[props.links.length - 1]);
const pages = computed(() => props.links.slice(1, -1));

const hasMultiplePages = computed(() => pages.value.length > 1);
</script>

<template>
    <Pagination v-if="hasMultiplePages" :total="0" class="mt-4">
        <PaginationContent>
            <PaginationItem>
                <PaginationPrevious
                    :as="previousLink.url ? Link : 'button'"
                    :href="previousLink.url ?? undefined"
                    :disabled="!previousLink.url"
                    preserve-scroll
                />
            </PaginationItem>

            <template v-for="(page, index) in pages" :key="index">
                <PaginationItem v-if="page.label === '...'" class="hidden sm:flex">
                    <PaginationEllipsis />
                </PaginationItem>
                <PaginationItem v-else class="hidden sm:flex">
                    <Button
                        :as="page.url ? Link : 'button'"
                        :href="page.url ?? undefined"
                        :variant="page.active ? 'outline' : 'ghost'"
                        :disabled="!page.url"
                        size="icon"
                        preserve-scroll
                    >
                        <span v-html="page.label" />
                    </Button>
                </PaginationItem>
            </template>

            <PaginationItem>
                <PaginationNext
                    :as="nextLink.url ? Link : 'button'"
                    :href="nextLink.url ?? undefined"
                    :disabled="!nextLink.url"
                    preserve-scroll
                />
            </PaginationItem>
        </PaginationContent>
    </Pagination>
</template>
