<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Toaster } from '@/components/ui/sonner';
import { useAppearance } from '@/composables/useAppearance';

const { resolvedAppearance, updateAppearance } = useAppearance();

function toggleTheme() {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}
</script>

<template>
    <div class="flex min-h-screen w-full flex-col bg-background">
        <header class="border-b">
            <div
                class="mx-auto flex h-16 w-full max-w-3xl items-center justify-between px-4"
            >
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

            <Button
                variant="ghost"
                size="icon"
                class="h-9 w-9 rounded-full bg-muted/50 hover:bg-muted"
                @click="toggleTheme"
            >
                <!-- Moon icon (shown in light mode) -->
                <svg
                    v-if="resolvedAppearance === 'light'"
                    class="h-5 w-5"
                    viewBox="0 0 32 32"
                    fill="currentColor"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="m30.706 19.721a1 1 0 0 0 -1.042-.234 13.423 13.423 0 0 1 -17.151-17.152 1 1 0 0 0 -1.276-1.278 15.214 15.214 0 0 0 -5.727 3.623 15.422 15.422 0 0 0 21.81 21.81 15.213 15.213 0 0 0 3.623-5.728 1 1 0 0 0 -.237-1.041zm-4.8 5.355a13.422 13.422 0 1 1 -15.859-21.312 15.514 15.514 0 0 0 4.2 13.985 15.51 15.51 0 0 0 13.985 4.2 13.357 13.357 0 0 1 -2.326 3.127z"
                    />
                </svg>
                <!-- Sun icon (shown in dark mode) -->
                <svg
                    v-else
                    class="h-5 w-5"
                    viewBox="0 0 512 512"
                    fill="currentColor"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        d="M256,78.2c8.284,0,15-6.716,15-15V15c0-8.284-6.716-15-15-15s-15,6.716-15,15v48.2C241,71.484,247.716,78.2,256,78.2z"
                    />
                    <path
                        d="M256,433.8c-8.284,0-15,6.716-15,15V497c0,8.284,6.716,15,15,15s15-6.716,15-15v-48.2C271,440.516,264.284,433.8,256,433.8z"
                    />
                    <path
                        d="M497,241h-48.2c-8.284,0-15,6.716-15,15s6.716,15,15,15H497c8.284,0,15-6.716,15-15S505.284,241,497,241z"
                    />
                    <path
                        d="M78.2,256c0-8.284-6.716-15-15-15H15c-8.284,0-15,6.716-15,15s6.716,15,15,15h48.2C71.484,271,78.2,264.284,78.2,256z"
                    />
                    <path
                        d="M402.94,130.272l34.077-34.077c5.858-5.858,5.858-15.355,0-21.213c-5.857-5.858-15.355-5.858-21.213,0l-34.077,34.077c-5.858,5.858-5.858,15.355,0,21.213C387.583,136.129,397.082,136.132,402.94,130.272z"
                    />
                    <path
                        d="M109.06,381.728l-34.077,34.077c-5.858,5.858-5.858,15.355,0,21.213c5.856,5.857,15.355,5.859,21.213,0l34.077-34.077c5.858-5.858,5.858-15.355,0-21.213C124.415,375.869,114.917,375.869,109.06,381.728z"
                    />
                    <path
                        d="M402.94,381.728c-5.857-5.858-15.355-5.858-21.213,0c-5.858,5.858-5.858,15.355,0,21.213l34.077,34.077c5.856,5.857,15.355,5.859,21.213,0c5.858-5.858,5.858-15.355,0-21.213L402.94,381.728z"
                    />
                    <path
                        d="M109.06,130.272c5.856,5.857,15.355,5.859,21.213,0c5.858-5.858,5.858-15.355,0-21.213L96.195,74.982c-5.857-5.858-15.355-5.858-21.213,0c-5.858,5.858-5.858,15.355,0,21.213L109.06,130.272z"
                    />
                    <path
                        d="M256,128.533c-70.285,0-127.467,57.182-127.467,127.467S185.715,383.467,256,383.467S383.467,326.285,383.467,256S326.285,128.533,256,128.533z M256,353.467c-53.743,0-97.467-43.724-97.467-97.467s43.724-97.467,97.467-97.467s97.467,43.724,97.467,97.467S309.743,353.467,256,353.467z"
                    />
                </svg>
                <span class="sr-only">Toggle theme</span>
            </Button>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-3xl flex-1 flex-col px-4 py-8">
            <slot />
        </main>

        <Toaster />
    </div>
</template>
