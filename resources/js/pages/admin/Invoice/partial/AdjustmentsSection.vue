<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Link2, MoreHorizontal, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AdjustmentController from '@/actions/App/Http/Controllers/Admin/AdjustmentController';
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
import type {
    Adjustment,
    InvoiceEdit,
    PendingAdjustment,
    TargetInvoice,
} from '@/types';

type Props = {
    invoice: InvoiceEdit;
    adjustments: Adjustment[];
    pendingAdjustments?: PendingAdjustment[];
    targetInvoices?: TargetInvoice[];
};

const props = defineProps<Props>();

const RELOAD_KEYS = ['adjustments', 'pendingAdjustments', 'invoice'];

const UNAPPLIED = 'unapplied';

const addOpen = ref(false);
const editingAdjustment = ref<Adjustment | null>(null);
const editOpen = ref(false);
const deleteAdjustment = ref<Adjustment | null>(null);
const deleteOpen = ref(false);
const linkOpen = ref(false);

const pending = computed(() => props.pendingAdjustments ?? []);
const targets = computed(() => props.targetInvoices ?? []);

function invoiceLabel(invoice: TargetInvoice): string {
    return `${invoice.invoice_no ?? `#${invoice.id}`} — ${invoice.title}`;
}

const addForm = useForm({
    amount: '',
    target_invoice_id: UNAPPLIED,
    note: '',
});

function submitAdd() {
    addForm
        .transform((data) => ({
            ...data,
            target_invoice_id:
                data.target_invoice_id === UNAPPLIED
                    ? null
                    : data.target_invoice_id,
        }))
        .post(AdjustmentController.store.url({ invoice: props.invoice.id }), {
            preserveScroll: true,
            only: RELOAD_KEYS,
            onSuccess: () => {
                addOpen.value = false;
                addForm.reset();
            },
        });
}

const editForm = useForm({
    amount: '',
    target_invoice_id: UNAPPLIED,
    note: '',
});

function openEdit(adjustment: Adjustment) {
    editingAdjustment.value = adjustment;
    editForm.amount = adjustment.amount;
    editForm.target_invoice_id = adjustment.invoice
        ? String(adjustment.invoice.id)
        : UNAPPLIED;
    editForm.note = adjustment.note ?? '';
    editOpen.value = true;
}

function submitEdit() {
    if (!editingAdjustment.value) {
        return;
    }

    editForm
        .transform((data) => ({
            ...data,
            target_invoice_id:
                data.target_invoice_id === UNAPPLIED
                    ? null
                    : data.target_invoice_id,
        }))
        .put(
            AdjustmentController.update.url({
                invoice: props.invoice.id,
                adjustment: editingAdjustment.value.id,
            }),
            {
                preserveScroll: true,
                only: RELOAD_KEYS,
                onSuccess: () => {
                    editOpen.value = false;
                },
            },
        );
}

function openDelete(adjustment: Adjustment) {
    deleteAdjustment.value = adjustment;
    deleteOpen.value = true;
}

const deleteForm = useForm({});

function submitDelete() {
    if (!deleteAdjustment.value) {
        return;
    }

    deleteForm.delete(
        AdjustmentController.destroy.url({
            invoice: props.invoice.id,
            adjustment: deleteAdjustment.value.id,
        }),
        {
            preserveScroll: true,
            only: RELOAD_KEYS,
            onSuccess: () => {
                deleteOpen.value = false;
            },
        },
    );
}

// The server takes the target invoice from the route; this field only carries validation errors back.
const linkForm = useForm({ target_invoice_id: '' });

function submitLink(adjustment: PendingAdjustment) {
    linkForm.post(
        AdjustmentController.link.url({
            invoice: props.invoice.id,
            adjustment: adjustment.id,
        }),
        {
            preserveScroll: true,
            only: RELOAD_KEYS,
            onSuccess: () => {
                linkOpen.value = false;
            },
        },
    );
}

function unlink(adjustment: Adjustment) {
    linkForm.delete(
        AdjustmentController.unlink.url({
            invoice: props.invoice.id,
            adjustment: adjustment.id,
        }),
        {
            preserveScroll: true,
            only: RELOAD_KEYS,
        },
    );
}
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Adjustments</CardTitle>
            <CardAction class="flex gap-2">
                <Dialog v-model:open="linkOpen">
                    <DialogTrigger as-child>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="!pending.length"
                        >
                            <Link2 class="mr-1 h-4 w-4" />
                            Link Balance
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Link Balance</DialogTitle>
                            <DialogDescription>
                                Carry an unapplied balance from an earlier
                                invoice into this one.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <div
                                v-for="item in pending"
                                :key="item.id"
                                class="flex items-center justify-between gap-4 rounded-md border p-3"
                            >
                                <div class="min-w-0">
                                    <p class="text-sm font-medium">
                                        {{ item.invoice.invoice_no }} — ${{
                                            item.amount
                                        }}
                                    </p>
                                    <p
                                        class="truncate text-xs text-muted-foreground"
                                    >
                                        {{ item.note ?? 'No note' }}
                                    </p>
                                </div>
                                <Button
                                    size="sm"
                                    :disabled="linkForm.processing"
                                    @click="submitLink(item)"
                                >
                                    Link
                                </Button>
                            </div>
                        </div>

                        <InputError :message="linkForm.errors.target_invoice_id" />

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button variant="secondary">Close</Button>
                            </DialogClose>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <Dialog v-model:open="addOpen">
                    <DialogTrigger as-child>
                        <Button variant="outline" size="sm">
                            <Plus class="mr-1 h-4 w-4" />
                            Carry Forward
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form class="space-y-6" @submit.prevent="submitAdd">
                            <DialogHeader>
                                <DialogTitle>Carry Balance Forward</DialogTitle>
                                <DialogDescription>
                                    Settle part of this invoice by moving it
                                    onto another one.
                                </DialogDescription>
                            </DialogHeader>

                            <div class="grid gap-4">
                                <div class="grid gap-2">
                                    <Label for="adjustment-amount"
                                        >Amount</Label
                                    >
                                    <Input
                                        id="adjustment-amount"
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
                                    <Label for="adjustment-target"
                                        >Carry To</Label
                                    >
                                    <Select
                                        v-model="addForm.target_invoice_id"
                                    >
                                        <SelectTrigger id="adjustment-target">
                                            <SelectValue
                                                placeholder="Select an invoice"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem :value="UNAPPLIED">
                                                Decide later (unapplied)
                                            </SelectItem>
                                            <SelectItem
                                                v-for="target in targets"
                                                :key="target.id"
                                                :value="String(target.id)"
                                            >
                                                {{ invoiceLabel(target) }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        :message="
                                            addForm.errors.target_invoice_id
                                        "
                                    />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="adjustment-note">Note</Label>
                                    <Input
                                        id="adjustment-note"
                                        v-model="addForm.note"
                                        placeholder="e.g. Balance carried forward"
                                    />
                                    <InputError :message="addForm.errors.note" />
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
                                    Carry Forward
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </CardAction>
        </CardHeader>
        <CardContent>
            <Table v-if="adjustments.length">
                <TableHeader>
                    <TableRow>
                        <TableHead>Direction</TableHead>
                        <TableHead>Amount</TableHead>
                        <TableHead>Invoice</TableHead>
                        <TableHead>Note</TableHead>
                        <TableHead class="w-[70px]" />
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="adjustment in adjustments"
                        :key="`${adjustment.direction}-${adjustment.id}`"
                    >
                        <TableCell>
                            <Badge
                                :variant="
                                    adjustment.direction === 'out'
                                        ? 'default'
                                        : 'outline'
                                "
                            >
                                {{
                                    adjustment.direction === 'out'
                                        ? 'Carried out'
                                        : 'Carried in'
                                }}
                            </Badge>
                        </TableCell>
                        <TableCell> ${{ adjustment.amount }} </TableCell>
                        <TableCell>
                            <template v-if="adjustment.invoice">
                                {{ adjustment.invoice.invoice_no }}
                            </template>
                            <Badge v-else variant="secondary">Unapplied</Badge>
                        </TableCell>
                        <TableCell>
                            {{ adjustment.note ?? '-' }}
                        </TableCell>
                        <TableCell>
                            <DropdownMenu
                                v-if="adjustment.direction === 'out'"
                            >
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                    >
                                        <MoreHorizontal class="h-4 w-4" />
                                        <span class="sr-only"
                                            >Adjustment actions</span
                                        >
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem
                                        @click="openEdit(adjustment)"
                                    >
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        class="text-destructive"
                                        @click="openDelete(adjustment)"
                                    >
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                            <Button
                                v-else
                                variant="ghost"
                                size="sm"
                                :disabled="linkForm.processing"
                                @click="unlink(adjustment)"
                            >
                                Remove
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <p v-else class="py-6 text-center text-sm text-muted-foreground">
                No adjustments yet. Click "Carry Forward" to move part of this
                invoice onto another one.
            </p>
        </CardContent>
    </Card>

    <!-- Edit Adjustment Dialog -->
    <Dialog v-model:open="editOpen">
        <DialogContent>
            <form class="space-y-6" @submit.prevent="submitEdit">
                <DialogHeader>
                    <DialogTitle>Edit Adjustment</DialogTitle>
                    <DialogDescription>
                        Update this carried balance.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="edit-adjustment-amount">Amount</Label>
                        <Input
                            id="edit-adjustment-amount"
                            v-model="editForm.amount"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                            required
                        />
                        <InputError :message="editForm.errors.amount" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-adjustment-target">Carry To</Label>
                        <Select v-model="editForm.target_invoice_id">
                            <SelectTrigger id="edit-adjustment-target">
                                <SelectValue placeholder="Select an invoice" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="UNAPPLIED">
                                    Decide later (unapplied)
                                </SelectItem>
                                <SelectItem
                                    v-for="target in targets"
                                    :key="target.id"
                                    :value="String(target.id)"
                                >
                                    {{ invoiceLabel(target) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError
                            :message="editForm.errors.target_invoice_id"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-adjustment-note">Note</Label>
                        <Input
                            id="edit-adjustment-note"
                            v-model="editForm.note"
                            placeholder="e.g. Balance carried forward"
                        />
                        <InputError :message="editForm.errors.note" />
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

    <!-- Delete Adjustment Confirmation -->
    <Dialog v-model:open="deleteOpen">
        <DialogContent>
            <form class="space-y-6" @submit.prevent="submitDelete">
                <DialogHeader class="space-y-3">
                    <DialogTitle>Delete adjustment?</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this ${{
                            deleteAdjustment?.amount ?? ''
                        }}
                        carried balance? The amount will go back to this
                        invoice's due.
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
                        Delete Adjustment
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
