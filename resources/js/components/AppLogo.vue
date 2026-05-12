<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md text-sidebar-primary-foreground"
    >
        <div
            class="flex aspect-square size-8 items-center justify-center rounded-md bg-green-100 p-1"
        >
            <AppLogoIcon
                className="size-4 fill-current text-black dark:text-black"
            />
        </div>
    </div>
    <div class="ml-1 flex flex-col justify-between text-left text-sm">
        <span class="truncate leading-tight font-semibold">
            Invoice Manager
        </span>
        <span class="text-xs leading-tight text-muted-foreground"
            >internal</span
        >
    </div>
</template>
