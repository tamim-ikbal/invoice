<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Client } from '@/types';

type Props = {
    client?: Client;
    formAction: object;
};

defineProps<Props>();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Client Information</CardTitle>
        </CardHeader>
        <Form v-bind="formAction" v-slot="{ errors, processing }">
            <CardContent class="space-y-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        class="mt-1 block w-full"
                        name="name"
                        :default-value="client?.name"
                        required
                        autocomplete="name"
                        placeholder="Client name"
                    />
                    <InputError class="mt-2" :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        name="email"
                        :default-value="client?.email"
                        required
                        autocomplete="email"
                        placeholder="Client email"
                    />
                    <InputError class="mt-2" :message="errors.email" />
                </div>
            </CardContent>

            <CardFooter>
                <Button :disabled="processing">Save</Button>
            </CardFooter>
        </Form>
    </Card>
</template>
