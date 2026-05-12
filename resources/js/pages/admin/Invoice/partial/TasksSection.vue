<script setup lang="ts">
import { Form, useForm } from '@inertiajs/vue3';
import { MoreHorizontal, Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import TaskController from '@/actions/App/Http/Controllers/Admin/TaskController';
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

type Props = {
    invoice: InvoiceEdit;
};

const props = defineProps<Props>();

const editingTask = ref<{ id: number; name: string; amount: number } | null>(
    null,
);
const editOpen = ref(false);
const deleteTask = ref<{ id: number; name: string } | null>(null);
const deleteOpen = ref(false);

const editForm = useForm({
    name: '',
    amount: 0,
});

function openEdit(task: { id: number; name: string; amount: number }) {
    editingTask.value = task;
    editForm.name = task.name;
    editForm.amount = task.amount;
    editOpen.value = true;
}

function submitEdit() {
    if (!editingTask.value) {
        return;
    }

    editForm.put(
        TaskController.update.url({
            invoice: props.invoice.id,
            task: editingTask.value.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                editOpen.value = false;
            },
        },
    );
}

function openDelete(task: { id: number; name: string }) {
    deleteTask.value = task;
    deleteOpen.value = true;
}

const deleteForm = useForm({});

function submitDelete() {
    if (!deleteTask.value) {
        return;
    }

    deleteForm.delete(
        TaskController.destroy.url({
            invoice: props.invoice.id,
            task: deleteTask.value.id,
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
            <CardTitle>Tasks</CardTitle>
            <CardAction>
                <Dialog>
                    <DialogTrigger as-child>
                        <Button variant="outline" size="sm">
                            <Plus class="mr-1 h-4 w-4" />
                            Add Task
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <Form
                            v-bind="TaskController.store.form(props.invoice.id)"
                            :options="{ preserveScroll: true }"
                            class="space-y-6"
                            v-slot="{ errors, processing }"
                        >
                            <DialogHeader>
                                <DialogTitle>Add Task</DialogTitle>
                                <DialogDescription>
                                    Add a new task to this invoice.
                                </DialogDescription>
                            </DialogHeader>

                            <div class="grid gap-4">
                                <div class="grid gap-2">
                                    <Label for="task-name">Name</Label>
                                    <Input
                                        id="task-name"
                                        name="name"
                                        placeholder="Task name"
                                        required
                                    />
                                    <InputError :message="errors.name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="task-amount">Amount</Label>
                                    <Input
                                        id="task-amount"
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
                                    Add Task
                                </Button>
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>
            </CardAction>
        </CardHeader>
        <CardContent>
            <Table v-if="invoice.tasks.length">
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead class="w-[180px] text-right"
                            >Amount</TableHead
                        >
                        <TableHead class="w-[70px]" />
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="task in invoice.tasks" :key="task.id">
                        <TableCell>{{ task.name }}</TableCell>
                        <TableCell class="text-right">
                            ${{ Number(task.amount).toFixed(2) }}
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
                                            >Task actions</span
                                        >
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click="openEdit(task)">
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        class="text-destructive"
                                        @click="openDelete(task)"
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
                No tasks added yet. Click "Add Task" to get started.
            </p>
        </CardContent>
    </Card>

    <!-- Edit Task Dialog -->
    <Dialog v-model:open="editOpen">
        <DialogContent>
            <form class="space-y-6" @submit.prevent="submitEdit">
                <DialogHeader>
                    <DialogTitle>Edit Task</DialogTitle>
                    <DialogDescription>
                        Update this task's details.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="edit-task-name">Name</Label>
                        <Input
                            id="edit-task-name"
                            v-model="editForm.name"
                            placeholder="Task name"
                            required
                        />
                        <InputError :message="editForm.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-task-amount">Amount</Label>
                        <Input
                            id="edit-task-amount"
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

    <!-- Delete Task Confirmation -->
    <Dialog v-model:open="deleteOpen">
        <DialogContent>
            <form class="space-y-6" @submit.prevent="submitDelete">
                <DialogHeader class="space-y-3">
                    <DialogTitle>Delete task?</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete "{{
                            deleteTask?.name
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
                        Delete Task
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
