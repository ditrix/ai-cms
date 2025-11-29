<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Form } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Manager {
    id: number;
    name: string;
    email: string;
}

interface Client {
    id: number;
    name: string;
    email: string;
    manager_id?: number;
    manager?: Manager;
}

interface Props {
    client: Client;
    managers: Manager[];
    show: boolean;
    action: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
    success: [];
}>();

const isOpen = computed({
    get: () => props.show,
    set: (value) => {
        if (!value) {
            emit('close');
        }
    },
});

const currentManager = computed(() => {
    return props.managers.find((m) => m.id === props.client.manager_id);
});

const selectedManagerId = ref<number | string>(props.client.manager_id || '');

const handleSuccess = () => {
    emit('success');
    emit('close');
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Change Manager</DialogTitle>
                <DialogDescription>
                    Select a new manager for {{ client.name }}
                </DialogDescription>
            </DialogHeader>

            <Form
                :action="action"
                method="post"
                class="space-y-4"
                @success="handleSuccess"
                v-slot="{ errors, processing }"
            >
                <div v-if="currentManager" class="rounded-md bg-muted p-3">
                    <p class="text-sm text-muted-foreground">Current Manager:</p>
                    <p class="font-medium">
                        {{ currentManager.name }} ({{ currentManager.email }})
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="manager_id">New Manager</Label>
                    <select
                        id="manager_id"
                        name="manager_id"
                        v-model="selectedManagerId"
                        required
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                    >
                        <option value="">Select a manager</option>
                        <option
                            v-for="manager in managers"
                            :key="manager.id"
                            :value="manager.id"
                        >
                            {{ manager.name }} ({{ manager.email }})
                        </option>
                    </select>
                    <InputError :message="errors.manager_id" />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('close')"
                        :disabled="processing"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Changing...' : 'Change Manager' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>

