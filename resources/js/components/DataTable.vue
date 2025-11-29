<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, ArrowUpDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Column {
    key: string;
    label: string;
    sortable?: boolean;
}

interface Action {
    label: string;
    onClick: (item: any) => void;
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
}

interface Props {
    items: any[];
    columns?: Column[];
    searchable?: boolean;
    sortable?: boolean;
    actions?: Action[];
    pagination?: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    searchQuery?: string;
    sortColumn?: string;
    sortDirection?: 'asc' | 'desc';
}

const props = withDefaults(defineProps<Props>(), {
    searchable: true,
    sortable: true,
    columns: () => [],
    actions: () => [],
    searchQuery: '',
    sortColumn: '',
    sortDirection: 'asc',
});

const emit = defineEmits<{
    search: [value: string];
    sort: [column: string, direction: 'asc' | 'desc'];
    pageChange: [url: string];
}>();

const searchValue = ref(props.searchQuery);

const defaultColumns: Column[] = [
    { key: 'id', label: 'ID', sortable: true },
    { key: 'name', label: 'Name', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
];

const displayColumns = computed(() => {
    return props.columns.length > 0 ? props.columns : defaultColumns;
});

const handleSearch = (value: string) => {
    searchValue.value = value;
    emit('search', value);
};

const handleSort = (column: string) => {
    if (!props.sortable) {
        return;
    }

    const currentColumn = props.sortColumn;
    const currentDirection = props.sortDirection;

    let newDirection: 'asc' | 'desc' = 'asc';
    if (currentColumn === column && currentDirection === 'asc') {
        newDirection = 'desc';
    }

    emit('sort', column, newDirection);
};

const handlePageChange = (url: string | null) => {
    if (url) {
        emit('pageChange', url);
        router.visit(url);
    }
};

const getSortIcon = (column: string) => {
    if (props.sortColumn !== column) {
        return ArrowUpDown;
    }
    return props.sortDirection === 'asc' ? ChevronRight : ChevronLeft;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Search -->
        <div v-if="searchable" class="flex items-center gap-4">
            <Input
                v-model="searchValue"
                type="text"
                placeholder="Search..."
                class="max-w-sm"
                @input="handleSearch(searchValue)"
            />
        </div>

        <!-- Table -->
        <div class="rounded-md border">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th
                                v-for="column in displayColumns"
                                :key="column.key"
                                class="px-4 py-3 text-left text-sm font-medium"
                            >
                                <button
                                    v-if="column.sortable && sortable"
                                    class="flex items-center gap-2 hover:text-foreground"
                                    @click="handleSort(column.key)"
                                >
                                    <span>{{ column.label }}</span>
                                    <component
                                        :is="getSortIcon(column.key)"
                                        class="size-4"
                                    />
                                </button>
                                <span v-else>{{ column.label }}</span>
                            </th>
                            <th
                                v-if="actions.length > 0"
                                class="px-4 py-3 text-right text-sm font-medium"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in items"
                            :key="item.id"
                            class="border-b transition-colors hover:bg-muted/50"
                        >
                            <td
                                v-for="column in displayColumns"
                                :key="column.key"
                                class="px-4 py-3 text-sm"
                            >
                                {{ item[column.key] }}
                            </td>
                            <td
                                v-if="actions.length > 0"
                                class="px-4 py-3 text-right"
                            >
                                <div class="flex items-center justify-end gap-2">
                                    <Button
                                        v-for="(action, index) in actions"
                                        :key="index"
                                        :variant="action.variant || 'ghost'"
                                        size="sm"
                                        @click="action.onClick(item)"
                                    >
                                        {{ action.label }}
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td
                                :colspan="displayColumns.length + (actions.length > 0 ? 1 : 0)"
                                class="px-4 py-8 text-center text-sm text-muted-foreground"
                            >
                                No data available
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div
            v-if="pagination && pagination.lastPage > 1"
            class="flex items-center justify-between"
        >
            <div class="text-sm text-muted-foreground">
                Showing {{ (pagination.currentPage - 1) * pagination.perPage + 1 }} to
                {{ Math.min(pagination.currentPage * pagination.perPage, pagination.total) }} of
                {{ pagination.total }} results
            </div>
            <div class="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="!pagination.links[0]?.url"
                    @click="handlePageChange(pagination.links[0]?.url || null)"
                >
                    <ChevronLeft class="size-4" />
                    Previous
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="!pagination.links[pagination.links.length - 1]?.url"
                    @click="
                        handlePageChange(
                            pagination.links[pagination.links.length - 1]?.url || null,
                        )
                    "
                >
                    Next
                    <ChevronRight class="size-4" />
                </Button>
            </div>
        </div>
    </div>
</template>

