<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InvoiceController from '@/actions/App/Http/Controllers/Admin/InvoiceController';
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
    invoiceId: number;
};

const props = defineProps<Props>();

const open = defineModel<boolean>('open', { default: false });
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent>
            <Form
                v-bind="
                    InvoiceController.destroy.form({ invoice: props.invoiceId })
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
                        Are you sure you want to delete this invoice?
                    </DialogTitle>
                    <DialogDescription>
                        Once this invoice is deleted, all of its data, items,
                        and payments will be permanently removed. This action
                        cannot be undone.
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
                        Delete Invoice
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
