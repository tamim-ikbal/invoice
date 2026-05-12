<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
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
import type { Client } from '@/types';

const props = defineProps<{
    client: Client;
}>();

const open = defineModel<boolean>('open', { default: false });

const form = useForm({
    name: props.client.name,
    email: props.client.email,
});

watch(
    () => props.client,
    (client) => {
        form.name = client.name;
        form.email = client.email;
    },
);

function submit() {
    form.put(ClientController.update.url({ client: props.client.id }), {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent>
            <form class="space-y-6" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>Edit Client</DialogTitle>
                    <DialogDescription>
                        Update this client's details.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="edit-client-name">Name</Label>
                        <Input
                            id="edit-client-name"
                            v-model="form.name"
                            placeholder="Client name"
                            required
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-client-email">Email</Label>
                        <Input
                            id="edit-client-email"
                            v-model="form.email"
                            type="email"
                            placeholder="Client email"
                            required
                        />
                        <InputError :message="form.errors.email" />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="form.processing">
                        Save Changes
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
