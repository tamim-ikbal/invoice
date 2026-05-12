<script setup lang="ts">
import InvoiceItemController from '@/actions/App/Http/Controllers/Admin/InvoiceItemController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardAction,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { InvoiceEdit } from '@/types';
import { Form, useForm } from '@inertiajs/vue3';
import { MoreHorizontal, Plus } from 'lucide-vue-next';
import { ref } from 'vue';

type Props = {
    invoice: InvoiceEdit;
};

const props = defineProps<Props>();

const editingItem = ref<{
    id: number;
    name: string;
    quantity: number;
    amount: string;
} | null>(null);
const editOpen = ref(false);
const deleteItem = ref<{ id: number; name: string } | null>(null);
const deleteOpen = ref(false);

const editForm = useForm({
    name: '',
    quantity: 1,
    amount: '',
});

function openEdit(item: {
    id: number;
    name: string;
    quantity: number;
    amount: string;
}) {
    editingItem.value = item;
    editForm.name = item.name;
    editForm.quantity = item.quantity;
    editForm.amount = item.amount;
    editOpen.value = true;
}

function submitEdit() {
    if (!editingItem.value) {
        return;
    }

    editForm.put(
        InvoiceItemController.update.url({
            invoice: props.invoice.id,
            invoiceItem: editingItem.value.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                editOpen.value = false;
            },
        },
    );
}

function openDelete(item: { id: number; name: string }) {
    deleteItem.value = item;
    deleteOpen.value = true;
}

const deleteForm = useForm({});

function submitDelete() {
    if (!deleteItem.value) {
        return;
    }

    deleteForm.delete(
        InvoiceItemController.destroy.url({
            invoice: props.invoice.id,
            invoiceItem: deleteItem.value.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                deleteOpen.value = false;
            },
        },
    );
}
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Items</CardTitle>
            <CardAction>
                <Dialog>
                    <DialogTrigger as-child>
                        <Button variant="outline" size="sm">
                            <Plus class="mr-1 h-4 w-4" />
                            Add Item
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <Form
                            v-bind="
                                InvoiceItemController.store.form(
                                    props.invoice.id,
                                )
                            "
                            :options="{ preserveScroll: true }"
                            class="space-y-6"
                            v-slot="{ errors, processing }"
                            resetOnSuccess
                        >
                            <DialogHeader>
                                <DialogTitle>Add Item</DialogTitle>
                                <DialogDescription>
                                    Add a new item to this invoice.
                                </DialogDescription>
                            </DialogHeader>

                            <div class="grid gap-4">
                                <div class="grid gap-2">
                                    <Label for="item-name">Name</Label>
                                    <Input
                                        id="item-name"
                                        name="name"
                                        placeholder="Item name"
                                        required
                                    />
                                    <InputError :message="errors.name" />
                                </div>

                                <div
                                    v-if="invoice.settings.show_quantity"
                                    class="grid gap-2"
                                >
                                    <Label for="item-quantity">Quantity</Label>
                                    <Input
                                        id="item-quantity"
                                        name="quantity"
                                        type="number"
                                        min="1"
                                        :default-value="1"
                                        required
                                    />
                                    <InputError :message="errors.quantity" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="item-amount">Amount</Label>
                                    <Input
                                        id="item-amount"
                                        name="amount"
                                        type="number"
                                        step="0.01"
                                        placeholder="0.00"
                                        required
                                    />
                                    <InputError :message="errors.amount" />
                                </div>
                            </div>

                            <DialogFooter class="gap-2">
                                <DialogClose as-child>
                                    <Button variant="secondary">Cancel</Button>
                                </DialogClose>
                                <Button type="submit" :disabled="processing">
                                    Add Item
                                </Button>
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>
            </CardAction>
        </CardHeader>
        <CardContent>
            <Table v-if="invoice.items.length">
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead
                            v-if="invoice.settings.show_quantity"
                            class="w-[100px] text-right"
                        >
                            Qty
                        </TableHead>
                        <TableHead class="w-[180px] text-right">
                            Amount
                        </TableHead>
                        <TableHead class="w-[70px]" />
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="item in invoice.items" :key="item.id">
                        <TableCell>{{ item.name }}</TableCell>
                        <TableCell
                            v-if="invoice.settings.show_quantity"
                            class="text-right"
                        >
                            {{ item.quantity }}
                        </TableCell>
                        <TableCell class="text-right">
                            ${{ item.amount }}
                        </TableCell>
                        <TableCell>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                    >
                                        <MoreHorizontal class="h-4 w-4" />
                                        <span class="sr-only"
                                            >Item actions</span
                                        >
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click="openEdit(item)">
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        class="text-destructive"
                                        @click="openDelete(item)"
                                    >
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <p v-else class="py-6 text-center text-sm text-muted-foreground">
                No items added yet. Click "Add Item" to get started.
            </p>
        </CardContent>
    </Card>

    <!-- Edit Item Dialog -->
    <Dialog v-model:open="editOpen">
        <DialogContent>
            <form class="space-y-6" @submit.prevent="submitEdit">
                <DialogHeader>
                    <DialogTitle>Edit Item</DialogTitle>
                    <DialogDescription>
                        Update this item's details.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="edit-item-name">Name</Label>
                        <Input
                            id="edit-item-name"
                            v-model="editForm.name"
                            placeholder="Item name"
                            required
                        />
                        <InputError :message="editForm.errors.name" />
                    </div>

                    <div
                        v-if="invoice.settings.show_quantity"
                        class="grid gap-2"
                    >
                        <Label for="edit-item-quantity">Quantity</Label>
                        <Input
                            id="edit-item-quantity"
                            v-model="editForm.quantity"
                            type="number"
                            min="1"
                            required
                        />
                        <InputError :message="editForm.errors.quantity" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-item-amount">Amount</Label>
                        <Input
                            id="edit-item-amount"
                            v-model="editForm.amount"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                            required
                        />
                        <InputError :message="editForm.errors.amount" />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="editForm.processing">
                        Save Changes
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Item Confirmation -->
    <Dialog v-model:open="deleteOpen">
        <DialogContent>
            <form class="space-y-6" @submit.prevent="submitDelete">
                <DialogHeader class="space-y-3">
                    <DialogTitle>Delete item?</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete "{{
                            deleteItem?.name
                        }}"? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="deleteForm.processing"
                    >
                        Delete Item
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
