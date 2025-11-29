<script setup lang="ts">
import ChangePasswordModal from '@/components/ChangePasswordModal.vue';
import DeleteManagerModal from '@/components/DeleteManagerModal.vue';
import DataTable from '@/components/DataTable.vue';
import ManagerForm from '@/components/ManagerForm.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, store, update, destroy, changePassword, toggleActive } from '@/actions/App/Http/Controllers/ManagerController';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Edit, Key, Plus, Power, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Manager {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
}

interface Props {
    managers: {
        data: Manager[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters?: {
        search?: string;
        sort_by?: string;
        sort_order?: string;
    };
}

const props = defineProps<Props>();

const page = usePage();
const user = page.props.auth.user;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Managers',
        href: index().url,
    },
];

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showChangePasswordModal = ref(false);
const showDeleteModal = ref(false);
const selectedManager = ref<Manager | null>(null);

const columns = computed(() => [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'role', label: 'Role', sortable: false },
    { key: 'is_active', label: 'Status', sortable: true },
]);

const tableItems = computed(() => {
    return props.managers.data.map((manager) => ({
        id: manager.id,
        name: manager.name,
        email: manager.email,
        role: manager.role === 'super_manager' ? 'Super Manager' : 'Manager',
        is_active: manager.is_active ? 'Active' : 'Inactive',
    }));
});

const handleSearch = (value: string) => {
    router.get(
        index().url,
        {
            ...props.filters,
            search: value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const handleSort = (column: string, direction: 'asc' | 'desc') => {
    router.get(
        index().url,
        {
            ...props.filters,
            sort_by: column,
            sort_order: direction,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const handlePageChange = (url: string) => {
    router.visit(url);
};

const handleCreate = () => {
    selectedManager.value = null;
    showCreateModal.value = true;
};

const handleEdit = (manager: Manager) => {
    selectedManager.value = manager;
    showEditModal.value = true;
};

const handleDelete = (manager: Manager) => {
    selectedManager.value = manager;
    showDeleteModal.value = true;
};

const handleChangePassword = (manager: Manager) => {
    selectedManager.value = manager;
    showChangePasswordModal.value = true;
};

const handleToggleActive = (manager: Manager) => {
    router.post(toggleActive({ manager: manager.id }).url, {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleFormSuccess = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    selectedManager.value = null;
    router.reload();
};

const handleChangePasswordSuccess = () => {
    showChangePasswordModal.value = false;
    selectedManager.value = null;
    router.reload();
};

const handleDeleteSuccess = () => {
    showDeleteModal.value = false;
    selectedManager.value = null;
    router.reload();
};

const availableManagers = computed(() => {
    if (!selectedManager.value) {
        return [];
    }
    return props.managers.data.filter((m) => m.id !== selectedManager.value!.id);
});

const actions = computed(() => [
    {
        label: 'Edit',
        onClick: (item: any) => handleEdit(props.managers.data.find((m) => m.id === item.id)!),
        variant: 'ghost' as const,
    },
    {
        label: 'Change Password',
        onClick: (item: any) => handleChangePassword(props.managers.data.find((m) => m.id === item.id)!),
        variant: 'ghost' as const,
    },
    {
        label: 'Toggle Active',
        onClick: (item: any) => handleToggleActive(props.managers.data.find((m) => m.id === item.id)!),
        variant: 'ghost' as const,
    },
    {
        label: 'Delete',
        onClick: (item: any) => handleDelete(props.managers.data.find((m) => m.id === item.id)!),
        variant: 'destructive' as const,
    },
]);
</script>

<template>
    <Head title="Managers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Managers</h1>
                    <p class="text-muted-foreground">
                        Manage team members
                    </p>
                </div>
                <Button @click="handleCreate">
                    <Plus class="mr-2 size-4" />
                    Create Manager
                </Button>
            </div>

            <!-- DataTable -->
            <DataTable
                :items="tableItems"
                :columns="columns"
                :pagination="{
                    currentPage: managers.current_page,
                    lastPage: managers.last_page,
                    perPage: managers.per_page,
                    total: managers.total,
                    links: managers.links,
                }"
                :search-query="filters?.search || ''"
                :sort-column="filters?.sort_by || 'id'"
                :sort-direction="(filters?.sort_order as 'asc' | 'desc') || 'asc'"
                :actions="actions"
                @search="handleSearch"
                @sort="handleSort"
                @page-change="handlePageChange"
            />

            <!-- Create Modal -->
            <Dialog v-model:open="showCreateModal">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Create Manager</DialogTitle>
                        <DialogDescription>
                            Add a new manager to the system
                        </DialogDescription>
                    </DialogHeader>
                    <ManagerForm
                        :action="store().url"
                        method="post"
                        mode="create"
                        @success="handleFormSuccess"
                        @cancel="showCreateModal = false"
                    />
                </DialogContent>
            </Dialog>

            <!-- Edit Modal -->
            <Dialog v-model:open="showEditModal">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Edit Manager</DialogTitle>
                        <DialogDescription>
                            Update manager information
                        </DialogDescription>
                    </DialogHeader>
                    <ManagerForm
                        v-if="selectedManager"
                        :manager="selectedManager"
                        :action="update({ manager: selectedManager.id }).url"
                        method="put"
                        mode="edit"
                        @success="handleFormSuccess"
                        @cancel="showEditModal = false"
                    />
                </DialogContent>
            </Dialog>

            <!-- Change Password Modal -->
            <ChangePasswordModal
                v-if="selectedManager"
                :manager="selectedManager"
                :show="showChangePasswordModal"
                :action="changePassword({ manager: selectedManager.id }).url"
                @close="showChangePasswordModal = false"
                @success="handleChangePasswordSuccess"
            />

            <!-- Delete Manager Modal -->
            <DeleteManagerModal
                v-if="selectedManager"
                :manager="selectedManager"
                :managers="availableManagers"
                :show="showDeleteModal"
                :action="destroy({ manager: selectedManager.id }).url"
                @close="showDeleteModal = false"
                @success="handleDeleteSuccess"
            />
        </div>
    </AppLayout>
</template>

