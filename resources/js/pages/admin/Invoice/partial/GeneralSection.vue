<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardAction,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { update } from '@/routes/admin/invoices';
import type { Client, InvoiceEdit, StatusOption } from '@/types';

type Props = {
    invoice: InvoiceEdit;
    clients: Client[];
    statuses: StatusOption[];
};

const props = defineProps<Props>();

const form = useForm({
    title: props.invoice.title,
    client_id: props.invoice.client?.id ?? null,
    status: props.invoice.status,
    date: props.invoice.date,
});

function save() {
    form.put(update.url(props.invoice.id));
}
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>General Information</CardTitle>
            <CardAction>
                <Button size="sm" :disabled="form.processing" @click="save">
                    Save
                </Button>
            </CardAction>
        </CardHeader>
        <CardContent>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="title">Title</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        placeholder="Invoice title"
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="client_id">Client</Label>
                    <Select v-model="form.client_id" class="w-full">
                        <SelectTrigger id="client_id" class="w-full">
                            <SelectValue placeholder="No client" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="null">No client</SelectItem>
                            <SelectItem
                                v-for="client in clients"
                                :key="client.id"
                                :value="client.id"
                            >
                                {{ client.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.client_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="status">Status</Label>
                    <Select v-model="form.status">
                        <SelectTrigger id="status" class="w-full">
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="status in statuses"
                                :key="status.value"
                                :value="status.value"
                            >
                                {{ status.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.status" />
                </div>

                <div class="grid gap-2">
                    <Label for="date">Date</Label>
                    <Input id="date" v-model="form.date" type="date" />
                    <InputError :message="form.errors.date" />
                </div>
            </div>
        </CardContent>
    </Card>
</template>
