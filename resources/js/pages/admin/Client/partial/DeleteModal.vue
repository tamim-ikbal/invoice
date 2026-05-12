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
    DialogTrigger,
} from '@/components/ui/dialog';

type Props = {
    clientId: number;
};

const props = defineProps<Props>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="destructive">Delete client</Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="
                    ClientController.destroy.form({
                        client: props.clientId,
                    })
                "
                :options="{
                    preserveScroll: true,
                }"
                class="space-y-6"
                v-slot="{ processing, reset, clearErrors }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle
                        >Are you sure you want to delete this
                        client?</DialogTitle
                    >
                    <DialogDescription>
                        This will also remove the client from any associated
                        invoices. This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button
                            variant="secondary"
                            @click="
                                () => {
                                    clearErrors();
                                    reset();
                                }
                            "
                        >
                            Cancel
                        </Button>
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
