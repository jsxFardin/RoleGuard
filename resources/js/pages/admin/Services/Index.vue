<template>
    <AppLayout>
        <Head title="Services Management" />

        <ConfirmDialog
            v-model:open="confirmOpen"
            title="Delete service?"
            description="This will permanently delete the service. This action cannot be undone."
            :details="confirmDetails"
            confirm-text="Delete"
            confirm-variant="destructive"
            :loading="deleting"
            loading-text="Deleting..."
            @confirm="confirmDelete"
        />

        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold">Services Management</h1>
            <Link
                :href="servicesCreate().url"
                class="rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90"
            >
                Add New Service
            </Link>
        </div>

        <!-- Filters -->
        <div class="rounded-lg border border-border bg-card p-4">
            <div class="grid gap-4 md:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-medium">Parent</label>
                    <select
                        v-model="selectedParent"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        @change="handleFilter"
                    >
                        <option value="">All</option>
                        <option value="none">Parent services (no parent)</option>
                        <option v-for="p in parentServices" :key="p.id" :value="String(p.id)">
                            {{ p.title }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">Status</label>
                    <select
                        v-model="selectedStatus"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        @change="handleFilter"
                    >
                        <option value="">All</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">Search</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search title or description..."
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        @input="handleSearch"
                    />
                </div>

                <div class="flex items-end">
                    <button
                        type="button"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                        @click="resetFilters"
                    >
                        Reset filters
                    </button>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <DataTable :columns="columns" :data="services.data" :actions="actions" :pagination="pagination">
            <template #cell-title="{ row }">
                <div class="flex items-center gap-2">
                    <span v-if="row.parent_id" class="text-muted-foreground">└─</span>
                    <span class="font-semibold text-foreground">{{ row.title }}</span>
                </div>
            </template>

            <template #cell-parent="{ row }">
                <span v-if="row.parent" class="text-sm text-foreground">
                    {{ row.parent.title }}
                </span>
                <span v-else class="text-sm font-medium text-muted-foreground">—</span>
            </template>

            <template #cell-is_active="{ value }">
                <span :class="[
                    'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold shadow-sm transition-all',
                    value
                        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-900/20 dark:text-emerald-400 dark:ring-emerald-400/30'
                        : 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20 dark:bg-gray-800/50 dark:text-gray-400 dark:ring-gray-400/20',
                ]">
                    <span :class="[
                        'h-2 w-2 rounded-full',
                        value ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400',
                    ]" />
                    {{ value ? 'Active' : 'Inactive' }}
                </span>
            </template>

            <template #cell-sort_order="{ value }">
                <span class="inline-flex items-center justify-center rounded-md bg-muted px-2.5 py-1 text-sm font-mono font-semibold text-muted-foreground">
                    {{ value }}
                </span>
            </template>
        </DataTable>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/admin/DataTable.vue';
import ConfirmDialog from '@/components/admin/ConfirmDialog.vue';
import {
    index as servicesIndex,
    create as servicesCreate,
    edit as servicesEdit,
    destroy as servicesDestroy,
} from '@/routes/admin/services';

interface Props {
    services: {
        data: Array<{
            id: number;
            title: string;
            parent_id: number | null;
            is_active: boolean;
            sort_order: number;
            parent?: { id: number; title: string } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        from: number | null;
        to: number | null;
        total: number;
    };
    parentServices: Array<{ id: number; title: string }>;
    filters?: {
        search?: string;
        status?: string;
        parent?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || '');
const selectedParent = ref(props.filters?.parent || '');

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'parent', label: 'Parent' },
    { key: 'is_active', label: 'Status', align: 'center' as const },
    { key: 'sort_order', label: 'Sort', align: 'center' as const },
];

const pagination = computed(() => ({
    links: props.services.links,
    from: props.services.from,
    to: props.services.to,
    total: props.services.total,
}));

const confirmOpen = ref(false);
const deleting = ref(false);
const pendingDelete = ref<{ id: number; title: string } | null>(null);
const confirmDetails = computed(() => pendingDelete.value?.title || '');

const openDeleteDialog = (row: any) => {
    pendingDelete.value = { id: row.id, title: row.title };
    confirmOpen.value = true;
};

const confirmDelete = () => {
    if (!pendingDelete.value) return;

    deleting.value = true;
    router.delete(servicesDestroy({ service: pendingDelete.value.id }).url, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            confirmOpen.value = false;
            pendingDelete.value = null;
        },
    });
};

const actions = [
    {
        type: 'link' as const,
        label: 'Edit',
        icon: 'pencil',
        href: (row: any) => servicesEdit({ service: row.id }),
    },
    {
        type: 'button' as const,
        label: 'Delete',
        icon: 'trash2',
        variant: 'destructive' as const,
        onClick: (row: any) => openDeleteDialog(row),
    },
];

const handleSearch = () => {
    router.get(
        servicesIndex().url,
        {
            search: search.value || null,
            status: selectedStatus.value || null,
            parent: selectedParent.value || null,
        },
        { preserveState: true, replace: true },
    );
};

const handleFilter = () => {
    router.get(
        servicesIndex().url,
        {
            search: search.value || null,
            status: selectedStatus.value || null,
            parent: selectedParent.value || null,
        },
        { preserveState: true, replace: true },
    );
};

const resetFilters = () => {
    search.value = '';
    selectedStatus.value = '';
    selectedParent.value = '';

    router.get(servicesIndex().url, {}, { preserveState: true, replace: true });
};
</script>
