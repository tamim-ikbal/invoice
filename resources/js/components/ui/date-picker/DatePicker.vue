<script setup lang="ts">
import type { DateValue } from 'reka-ui';
import {
    CalendarDate,
    getLocalTimeZone,
    today,
} from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

const props = defineProps<{
    id?: string;
    placeholder?: string;
    disabled?: boolean;
}>();

const modelValue = defineModel<string>({ default: '' });

const open = ref(false);

const calendarValue = computed<DateValue | undefined>({
    get() {
        if (!modelValue.value) {
            return undefined;
        }

        const [year, month, day] = modelValue.value.split('-').map(Number);

        return new CalendarDate(year, month, day);
    },
    set(value: DateValue | undefined) {
        if (!value) {
            modelValue.value = '';

            return;
        }

        const year = String(value.year).padStart(4, '0');
        const month = String(value.month).padStart(2, '0');
        const day = String(value.day).padStart(2, '0');

        modelValue.value = `${year}-${month}-${day}`;
        open.value = false;
    },
});

const displayValue = computed(() => {
    if (!modelValue.value) {
        return '';
    }

    const [year, month, day] = modelValue.value.split('-').map(Number);
    const date = new Date(year, month - 1, day);

    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                :id="props.id"
                variant="outline"
                :disabled="props.disabled"
                :class="
                    cn(
                        'w-full justify-start text-left font-normal',
                        !modelValue && 'text-muted-foreground',
                    )
                "
            >
                <CalendarIcon class="mr-2 h-4 w-4" />
                {{ displayValue || placeholder || 'Pick a date' }}
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0" align="start">
            <Calendar
                v-model="calendarValue"
                :default-placeholder="today(getLocalTimeZone())"
                layout="month-and-year"
                initial-focus
            />
        </PopoverContent>
    </Popover>
</template>
