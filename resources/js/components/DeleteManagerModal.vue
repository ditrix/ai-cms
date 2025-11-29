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
import { computed } from 'vue';

interface Manager {
    id: number;
    name: string;
    email: string;
}

interface Props {
    manager: Manager;
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

const availableManagers = computed(() => {
    return props.managers.filter((m) => m.id !== props.manager.id);
});

const handleSuccess = () => {
    emit('success');
    emit('close');
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete Manager</DialogTitle>
                <DialogDescription>
                    Are you sure you want to delete {{ manager.name }}? All clients will be transferred to another manager.
                </DialogDescription>
            </DialogHeader>

            <Form
                :action="action"
                method="delete"
                class="space-y-4"
                @success="handleSuccess"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-2">
                    <Label for="new_manager_id">Transfer clients to</Label>
                    <select
                        id="new_manager_id"
                        name="new_manager_id"
                        required
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                    >
                        <option value="">Select a manager</option>
                        <option
                            v-for="m in availableManagers"
                            :key="m.id"
                            :value="m.id"
                        >
                            {{ m.name }} ({{ m.email }})
                        </option>
                    </select>
                    <InputError :message="errors.new_manager_id" />
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
                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                    >
                        {{ processing ? 'Deleting...' : 'Delete Manager' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>

