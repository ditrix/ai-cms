<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Manager {
    id?: number;
    name: string;
    email: string;
    role?: string;
    is_active?: boolean;
}

interface Props {
    manager?: Manager;
    mode?: 'create' | 'edit';
    action: string;
    method?: 'post' | 'put' | 'patch';
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    method: 'post',
});

const emit = defineEmits<{
    cancel: [];
    success: [];
}>();

const formData = computed(() => {
    if (props.mode === 'edit' && props.manager) {
        return {
            name: props.manager.name || '',
            email: props.manager.email || '',
            role: props.manager.role || 'manager',
            is_active: props.manager.is_active ?? true,
        };
    }
    return {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'manager',
        is_active: true,
    };
});

const roles = [
    { value: 'manager', label: 'Manager' },
    { value: 'super_manager', label: 'Super Manager' },
    { value: 'admin', label: 'Admin' },
];
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
                placeholder="Manager name"
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
                placeholder="manager@example.com"
            />
            <InputError :message="errors.email" />
        </div>

        <div v-if="mode === 'create'" class="grid gap-2">
            <Label for="password">Password</Label>
            <Input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Password"
            />
            <InputError :message="errors.password" />
        </div>

        <div v-if="mode === 'create'" class="grid gap-2">
            <Label for="password_confirmation">Confirm Password</Label>
            <Input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Confirm password"
            />
            <InputError :message="errors.password_confirmation" />
        </div>

        <div class="grid gap-2">
            <Label for="role">Role</Label>
            <select
                id="role"
                name="role"
                :value="formData.role"
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
            >
                <option
                    v-for="roleOption in roles"
                    :key="roleOption.value"
                    :value="roleOption.value"
                    :selected="roleOption.value === formData.role"
                >
                    {{ roleOption.label }}
                </option>
            </select>
            <InputError :message="errors.role" />
        </div>

        <div class="flex items-center gap-2">
            <Checkbox
                id="is_active"
                name="is_active"
                :checked="formData.is_active"
            />
            <Label for="is_active" class="cursor-pointer">
                Active
            </Label>
            <InputError :message="errors.is_active" />
        </div>

        <div class="flex items-center gap-4">
            <Button type="submit" :disabled="processing">
                {{ processing ? 'Saving...' : mode === 'create' ? 'Create Manager' : 'Update Manager' }}
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

