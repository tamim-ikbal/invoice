<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="flex min-h-screen w-full flex-col bg-background">
        <header class="flex h-16 items-center justify-center border-b px-4">
            <div class="flex items-center gap-2">
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
                <div
                    class="ml-1 flex flex-col justify-between text-left text-sm"
                >
                    <span class="truncate leading-tight font-semibold">
                        Invoice Manager
                    </span>
                    <span class="text-xs leading-tight text-muted-foreground"
                        >internal</span
                    >
                </div>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-3xl flex-1 flex-col px-4 py-8">
            <slot />
        </main>

        <Toaster />
    </div>
</template>
