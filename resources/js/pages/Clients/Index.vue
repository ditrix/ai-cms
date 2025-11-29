<script setup lang="ts">
import ChangeManagerModal from '@/components/ChangeManagerModal.vue';
import ClientForm from '@/components/ClientForm.vue';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, store, update, destroy, changeManager } from '@/actions/App/Http/Controllers/ClientController';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Edit, Plus, Trash2, UserCog } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Client {
    id: number;
    name: string;
    email: string;
    manager_id?: number;
    manager?: {
        id: number;
        name: string;
        email: string;
    };
}

interface Manager {
    id: number;
    name: string;
    email: string;
}

interface Props {
    clients: {
        data: Client[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    managers?: Manager[];
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
        title: 'Clients',
        href: index().url,
    },
];

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showChangeManagerModal = ref(false);
const selectedClient = ref<Client | null>(null);

const canSelectManager = computed(() => {
    return user.role === 'super_manager' || user.role === 'admin';
});

const columns = computed(() => {
    const cols = [
        { key: 'id', label: 'ID', sortable: true },
        { key: 'name', label: 'Name', sortable: true },
        { key: 'email', label: 'Email', sortable: true },
    ];

    if (canSelectManager.value) {
        cols.push({ key: 'manager', label: 'Manager', sortable: false });
    }

    return cols;
});

const tableItems = computed(() => {
    return props.clients.data.map((client) => {
        const item: any = {
            id: client.id,
            name: client.name,
            email: client.email,
        };

        if (canSelectManager.value && client.manager) {
            item.manager = `${client.manager.name} (${client.manager.email})`;
        }

        return item;
    });
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
    selectedClient.value = null;
    showCreateModal.value = true;
};

const handleEdit = (client: Client) => {
    selectedClient.value = client;
    showEditModal.value = true;
};

const handleDelete = (client: Client) => {
    if (confirm(`Are you sure you want to delete ${client.name}?`)) {
        router.delete(destroy({ client: client.id }).url);
    }
};

const handleChangeManager = (client: Client) => {
    selectedClient.value = client;
    showChangeManagerModal.value = true;
};

const handleFormSuccess = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    selectedClient.value = null;
    router.reload();
};

const handleChangeManagerSuccess = () => {
    showChangeManagerModal.value = false;
    selectedClient.value = null;
    router.reload();
};

const actions = computed(() => {
    const actionList: Array<{
        label: string;
        onClick: (item: any) => void;
        variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
    }> = [
        {
            label: 'Edit',
            onClick: (item) => handleEdit(props.clients.data.find((c) => c.id === item.id)!),
            variant: 'ghost',
        },
        {
            label: 'Delete',
            onClick: (item) => handleDelete(props.clients.data.find((c) => c.id === item.id)!),
            variant: 'destructive',
        },
    ];

    if (canSelectManager.value) {
        actionList.splice(1, 0, {
            label: 'Change Manager',
            onClick: (item) => handleChangeManager(props.clients.data.find((c) => c.id === item.id)!),
            variant: 'ghost',
        });
    }

    return actionList;
});
</script>

<template>
    <Head title="Clients" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Clients</h1>
                    <p class="text-muted-foreground">
                        Manage your clients
                    </p>
                </div>
                <Button @click="handleCreate">
                    <Plus class="mr-2 size-4" />
                    Create Client
                </Button>
            </div>

            <!-- DataTable -->
            <DataTable
                :items="tableItems"
                :columns="columns"
                :pagination="{
                    currentPage: clients.current_page,
                    lastPage: clients.last_page,
                    perPage: clients.per_page,
                    total: clients.total,
                    links: clients.links,
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
                        <DialogTitle>Create Client</DialogTitle>
                        <DialogDescription>
                            Add a new client to the system
                        </DialogDescription>
                    </DialogHeader>
                    <ClientForm
                        :managers="managers"
                        :can-select-manager="canSelectManager"
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
                        <DialogTitle>Edit Client</DialogTitle>
                        <DialogDescription>
                            Update client information
                        </DialogDescription>
                    </DialogHeader>
                    <ClientForm
                        v-if="selectedClient"
                        :client="selectedClient"
                        :managers="managers"
                        :can-select-manager="canSelectManager"
                        :action="update({ client: selectedClient.id }).url"
                        method="put"
                        mode="edit"
                        @success="handleFormSuccess"
                        @cancel="showEditModal = false"
                    />
                </DialogContent>
            </Dialog>

            <!-- Change Manager Modal -->
            <ChangeManagerModal
                v-if="selectedClient && managers"
                :client="selectedClient"
                :managers="managers"
                :show="showChangeManagerModal"
                :action="changeManager({ client: selectedClient.id }).url"
                @close="showChangeManagerModal = false"
                @success="handleChangeManagerSuccess"
            />
        </div>
    </AppLayout>
</template>

