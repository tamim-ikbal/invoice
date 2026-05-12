<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { MoreHorizontal, Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import PaymentController from '@/actions/App/Http/Controllers/Admin/PaymentController';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardAction,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { DatePicker } from '@/components/ui/date-picker';
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { InvoiceEdit } from '@/types';

type Payment = InvoiceEdit['payments'][number];

type Props = {
    invoice: InvoiceEdit;
};

const props = defineProps<Props>();

function today() {
    return new Date().toISOString().split('T')[0];
}

const addOpen = ref(false);
const editingPayment = ref<Payment | null>(null);
const editOpen = ref(false);
const deletePayment = ref<Payment | null>(null);
const deleteOpen = ref(false);

const addForm = useForm({
    amount: '',
    date: today(),
    status: 'unpaid',
    payment_method: 'payoneer',
});

function submitAdd() {
    addForm.post(
        PaymentController.store.url({ invoice: props.invoice.id }),
        {
            preserveScroll: true,
            onSuccess: () => {
                addOpen.value = false;
                addForm.reset();
                addForm.date = today();
            },
        },
    );
}

const editForm = useForm({
    amount: '',
    date: '',
    status: 'unpaid',
    payment_method: 'payoneer',
});

function openEdit(payment: Payment) {
    editingPayment.value = payment;
    editForm.amount = payment.amount;
    editForm.date = payment.date;
    editForm.status = payment.status;
    editForm.payment_method = payment.payment_method;
    editOpen.value = true;
}

function submitEdit() {
    if (!editingPayment.value) {
        return;
    }

    editForm.put(
        PaymentController.update.url({
            invoice: props.invoice.id,
            payment: editingPayment.value.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                editOpen.value = false;
            },
        },
    );
}

function openDelete(payment: Payment) {
    deletePayment.value = payment;
    deleteOpen.value = true;
}

const deleteForm = useForm({});

function submitDelete() {
    if (!deletePayment.value) {
        return;
    }

    deleteForm.delete(
        PaymentController.destroy.url({
            invoice: props.invoice.id,
            payment: deletePayment.value.id,
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
            <CardTitle>Payments</CardTitle>
            <CardAction>
                <Dialog v-model:open="addOpen">
                    <DialogTrigger as-child>
                        <Button variant="outline" size="sm">
                            <Plus class="mr-1 h-4 w-4" />
                            Add Payment
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form class="space-y-6" @submit.prevent="submitAdd">
                            <DialogHeader>
                                <DialogTitle>Add Payment</DialogTitle>
                                <DialogDescription>
                                    Record a new payment for this invoice.
                                </DialogDescription>
                            </DialogHeader>

                            <div class="grid gap-4">
                                <div class="grid gap-2">
                                    <Label for="payment-amount">Amount</Label>
                                    <Input
                                        id="payment-amount"
                                        v-model="addForm.amount"
                                        type="number"
                                        step="0.01"
                                        placeholder="0.00"
                                        required
                                    />
                                    <InputError
                                        :message="addForm.errors.amount"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="payment-date">Date</Label>
                                    <DatePicker
                                        id="payment-date"
                                        v-model="addForm.date"
                                    />
                                    <InputError
                                        :message="addForm.errors.date"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="payment-status">Status</Label>
                                    <Select v-model="addForm.status">
                                        <SelectTrigger id="payment-status">
                                            <SelectValue placeholder="Status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="paid">
                                                Paid
                                            </SelectItem>
                                            <SelectItem value="unpaid">
                                                Unpaid
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        :message="addForm.errors.status"
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="payment-method">Method</Label>
                                    <Select v-model="addForm.payment_method">
                                        <SelectTrigger id="payment-method">
                                            <SelectValue placeholder="Method" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="payoneer">
                                                Payoneer
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        :message="
                                            addForm.errors.payment_method
                                        "
                                    />
                                </div>
                            </div>

                            <DialogFooter class="gap-2">
                                <DialogClose as-child>
                                    <Button variant="secondary">Cancel</Button>
                                </DialogClose>
                                <Button
                                    type="submit"
                                    :disabled="addForm.processing"
                                >
                                    Add Payment
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </CardAction>
        </CardHeader>
        <CardContent>
            <Table v-if="invoice.payments.length">
                <TableHeader>
                    <TableRow>
                        <TableHead>Amount</TableHead>
                        <TableHead>Date</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Method</TableHead>
                        <TableHead class="w-[70px]" />
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="payment in invoice.payments"
                        :key="payment.id"
                    >
                        <TableCell>
                            ${{ payment.amount }}
                        </TableCell>
                        <TableCell>{{ payment.date }}</TableCell>
                        <TableCell>
                            <Badge
                                :variant="
                                    payment.status === 'paid'
                                        ? 'default'
                                        : 'secondary'
                                "
                            >
                                {{ payment.status }}
                            </Badge>
                        </TableCell>
                        <TableCell class="capitalize">
                            {{ payment.payment_method }}
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
                                            >Payment actions</span
                                        >
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem
                                        @click="openEdit(payment)"
                                    >
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        class="text-destructive"
                                        @click="openDelete(payment)"
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
                No payments recorded yet. Click "Add Payment" to record one.
            </p>
        </CardContent>
    </Card>

    <!-- Edit Payment Dialog -->
    <Dialog v-model:open="editOpen">
        <DialogContent>
            <form class="space-y-6" @submit.prevent="submitEdit">
                <DialogHeader>
                    <DialogTitle>Edit Payment</DialogTitle>
                    <DialogDescription>
                        Update this payment's details.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="edit-payment-amount">Amount</Label>
                        <Input
                            id="edit-payment-amount"
                            v-model="editForm.amount"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                            required
                        />
                        <InputError :message="editForm.errors.amount" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-payment-date">Date</Label>
                        <DatePicker
                            id="edit-payment-date"
                            v-model="editForm.date"
                        />
                        <InputError :message="editForm.errors.date" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-payment-status">Status</Label>
                        <Select v-model="editForm.status">
                            <SelectTrigger id="edit-payment-status">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="paid">Paid</SelectItem>
                                <SelectItem value="unpaid">Unpaid</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="editForm.errors.status" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-payment-method">Method</Label>
                        <Select v-model="editForm.payment_method">
                            <SelectTrigger id="edit-payment-method">
                                <SelectValue placeholder="Method" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="payoneer"
                                    >Payoneer</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <InputError :message="editForm.errors.payment_method" />
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

    <!-- Delete Payment Confirmation -->
    <Dialog v-model:open="deleteOpen">
        <DialogContent>
            <form class="space-y-6" @submit.prevent="submitDelete">
                <DialogHeader class="space-y-3">
                    <DialogTitle>Delete payment?</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this
                        ${{ deletePayment?.amount ?? '' }} payment? This action
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
                        :disabled="deleteForm.processing"
                    >
                        Delete Payment
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
