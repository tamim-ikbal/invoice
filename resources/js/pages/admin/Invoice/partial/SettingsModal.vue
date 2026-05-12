<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Settings } from 'lucide-vue-next';
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
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import type { InvoiceEdit } from '@/types';

type Props = {
    invoice: InvoiceEdit;
};

const props = defineProps<Props>();

const form = useForm({
    show_quantity: props.invoice.settings.show_quantity,
});

function submit() {
    form.patch(
        InvoiceController.updateSettings.url({ invoice: props.invoice.id }),
        {
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="outline" size="sm">
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Button>
        </DialogTrigger>
        <DialogContent>
            <form class="space-y-6" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>Invoice Settings</DialogTitle>
                    <DialogDescription>
                        Configure display settings for this invoice.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <Label for="show-quantity" class="cursor-pointer">
                            Show Quantity
                        </Label>
                        <Switch
                            id="show-quantity"
                            :checked="form.show_quantity"
                            @update:checked="form.show_quantity = $event"
                        />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="form.processing">
                        Save Settings
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
