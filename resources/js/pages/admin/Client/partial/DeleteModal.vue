<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import ClientController from '@/actions/App/Http/Controllers/Admin/ClientController';
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

type Props = {
    clientId: number;
};

const props = defineProps<Props>();

const open = defineModel<boolean>('open', { default: false });
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent>
            <Form
                v-bind="
                    ClientController.destroy.form({
                        client: props.clientId,
                    })
                "
                :options="{
                    preserveScroll: true,
                    onSuccess: () => {
                        open = false;
                    },
                }"
                class="space-y-6"
                v-slot="{ processing }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>
                        Are you sure you want to delete this client?
                    </DialogTitle>
                    <DialogDescription>
                        This will also remove the client from any associated
                        invoices. This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                    >
                        Delete Client
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
