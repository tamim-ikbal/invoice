<script setup lang="ts">
import { useMediaQuery } from '@vueuse/core';
import { computed, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        text: string;
        wordLimit?: number;
    }>(),
    {
        wordLimit: 10,
    },
);

const isMobile = useMediaQuery('(max-width: 767px)');
const expanded = ref(false);

const words = computed(() => props.text.split(/\s+/));
const shouldTruncate = computed(
    () => isMobile.value && words.value.length > props.wordLimit,
);

const displayText = computed(() => {
    if (!shouldTruncate.value || expanded.value) {
        return props.text;
    }

    return words.value.slice(0, props.wordLimit).join(' ') + '...';
});
</script>

<template>
    <span>
        {{ displayText }}
        <button
            v-if="shouldTruncate"
            type="button"
            class="ml-1 text-xs text-primary underline"
            @click="expanded = !expanded"
        >
            {{ expanded ? 'Read less' : 'Read more' }}
        </button>
    </span>
</template>
