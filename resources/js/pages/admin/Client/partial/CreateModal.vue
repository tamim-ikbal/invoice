<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import ClientController from '@/actions/App/Http/Controllers/Admin/ClientController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const open = defineModel<boolean>('open', { default: false });
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent>
            <Form
                v-bind="ClientController.store.form()"
                :options="{
                    preserveScroll: true,
                    onSuccess: () => {
                        open = false;
                    },
                }"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <DialogHeader>
                    <DialogTitle>New Client</DialogTitle>
                    <DialogDescription>
                        Add a new client to your account.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="client-name">Name</Label>
                        <Input
                            id="client-name"
                            name="name"
                            placeholder="Client name"
                            required
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="client-email">Email</Label>
                        <Input
                            id="client-email"
                            name="email"
                            type="email"
                            placeholder="Client email"
                            required
                        />
                        <InputError :message="errors.email" />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">
                        Create Client
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
