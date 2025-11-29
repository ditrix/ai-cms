<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Manager {
    id: number;
    name: string;
    email: string;
}

interface Client {
    id?: number;
    name: string;
    email: string;
    manager_id?: number;
}

interface Props {
    client?: Client;
    managers?: Manager[];
    mode?: 'create' | 'edit';
    action: string;
    method?: 'post' | 'put' | 'patch';
    canSelectManager?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    method: 'post',
    canSelectManager: false,
});

const emit = defineEmits<{
    cancel: [];
    success: [];
}>();

const formData = computed(() => {
    if (props.mode === 'edit' && props.client) {
        return {
            name: props.client.name || '',
            email: props.client.email || '',
            manager_id: props.client.manager_id || null,
        };
    }
    return {
        name: '',
        email: '',
        manager_id: null,
    };
});
</script>

<template>
    <Form
        :action="action"
        :method="method"
        class="space-y-6"
        @success="emit('success')"
        v-slot="{ errors, processing, recentlySuccessful }"
    >
        <div class="grid gap-2">
            <Label for="name">Name</Label>
            <Input
                id="name"
                name="name"
                type="text"
                :default-value="formData.name"
                required
                autocomplete="name"
                placeholder="Client name"
            />
            <InputError :message="errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="email">Email</Label>
            <Input
                id="email"
                name="email"
                type="email"
                :default-value="formData.email"
                required
                autocomplete="email"
                placeholder="client@example.com"
            />
            <InputError :message="errors.email" />
        </div>

        <div
            v-if="canSelectManager && managers && managers.length > 0"
            class="grid gap-2"
        >
            <Label for="manager_id">Manager</Label>
            <select
                id="manager_id"
                name="manager_id"
                :default-value="formData.manager_id || ''"
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

        <div class="flex items-center gap-4">
            <Button type="submit" :disabled="processing">
                {{ processing ? 'Saving...' : mode === 'create' ? 'Create Client' : 'Update Client' }}
            </Button>
            <Button
                type="button"
                variant="outline"
                @click="emit('cancel')"
                :disabled="processing"
            >
                Cancel
            </Button>
            <Transition
                enter-active-class="transition ease-in-out"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out"
                leave-to-class="opacity-0"
            >
                <p
                    v-show="recentlySuccessful"
                    class="text-sm text-muted-foreground"
                >
                    Saved.
                </p>
            </Transition>
        </div>
    </Form>
</template>

