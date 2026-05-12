<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="flex min-h-screen w-full flex-col bg-background">
        <header class="flex h-16 items-center justify-center border-b px-4">
            <div class="flex items-center gap-2">
                <div
                    class="flex aspect-square size-8 items-center justify-center rounded-md bg-primary text-primary-foreground"
                >
                    <AppLogoIcon
                        class="size-5 fill-current text-white dark:text-black"
                    />
                </div>
                <span class="text-sm font-semibold">Invoice</span>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-3xl flex-1 flex-col px-4 py-8">
            <slot />
        </main>

        <Toaster />
    </div>
</template>
