<template>
    <AppLayout>

        <Head title="FAQs Management" />
        <ConfirmDialog
            v-model:open="confirmOpen"
            title="Delete FAQ?"
            description="This will permanently delete the FAQ. This action cannot be undone."
            :details="confirmDetails"
            confirm-text="Delete"
            confirm-variant="destructive"
            :loading="deleting"
            loading-text="Deleting..."
            @confirm="confirmDelete"
        />
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold">FAQs Management</h1>
            <Link :href="faqsCreate().url"
                class="rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90">
                Add New FAQ
            </Link>
        </div>

        <!-- Filters -->
        <div class="rounded-lg border border-border bg-card p-4">
            <div class="grid gap-4 md:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-medium">Category</label>
                    <select v-model="selectedCategory"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        @change="handleFilter">
                        <option value="">All Categories</option>
                        <option v-for="category in categories" :key="category" :value="category">
                            {{ category }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">Service</label>
                    <select v-model="selectedService"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        @change="handleFilter">
                        <option value="">All Services</option>
                        <option v-for="service in services" :key="service.id" :value="service.id">
                            {{ service.title }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium">Status</label>
                    <select v-model="selectedStatus"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        @change="handleFilter">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">Search</label>
                    <input v-model="search" type="text" placeholder="Search questions or answers..."
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        @input="handleSearch" />
                </div>

                <div class="flex items-end">
                    <button type="button"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                        @click="resetFilters">
                        Reset filters
                    </button>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <DataTable :columns="columns" :data="faqs.data" :actions="actions" :pagination="pagination">
            <template #cell-question="{ row }">
                <div class="max-w-lg">
                    <div class="font-semibold text-foreground">
                        {{ row.question }}
                    </div>
                    <div class="mt-1.5 text-sm leading-relaxed text-muted-foreground line-clamp-2"
                        v-html="truncateHtml(row.answer, 80)" />
                </div>
            </template>

            <template #cell-category="{ value }">
                <span v-if="value"
                    class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary ring-1 ring-inset ring-primary/20">
                    {{ value }}
                </span>
                <span v-else class="text-sm font-medium text-muted-foreground">—</span>
            </template>

            <template #cell-service="{ row }">
                <span v-if="row.service"
                    class="inline-flex items-center rounded-md bg-muted/50 px-2.5 py-1 text-sm font-medium text-foreground">
                    {{ row.service.title }}
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
                <span
                    class="inline-flex items-center justify-center rounded-md bg-muted px-2.5 py-1 text-sm font-mono font-semibold text-muted-foreground">
                    {{ value }}
                </span>
            </template>

            <template #empty>
                <div class="text-muted-foreground">
                    <p class="text-sm">No FAQs found.</p>
                    <Link :href="faqsCreate().url" class="mt-2 inline-block text-sm text-primary hover:underline">
                        Create your first FAQ
                    </Link>
                </div>
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
    index as faqsIndex,
    destroy as faqsDestroy,
    edit as faqsEdit,
    create as faqsCreate,
} from '@/routes/admin/faqs';

interface Props {
    faqs: {
        data: Array<{
            id: number;
            question: string;
            answer: string;
            category: string | null;
            service_id: number | null;
            sort_order: number;
            is_active: boolean;
            service?: {
                title: string;
            } | null;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        from: number | null;
        to: number | null;
        total: number;
    };
    services: Array<{
        id: number;
        title: string;
    }>;
    categories: string[];
    filters?: {
        category?: string;
        service?: string;
        search?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || '');
const selectedService = ref(props.filters?.service || '');
const selectedStatus = ref(props.filters?.status || '');

const columns = [
    { key: 'question', label: 'Question' },
    { key: 'category', label: 'Category' },
    { key: 'service', label: 'Service' },
    { key: 'is_active', label: 'Status', align: 'center' as const },
    { key: 'sort_order', label: 'Sort', align: 'center' as const },
];

const actions = [
    {
        type: 'link' as const,
        label: 'Edit',
        icon: 'pencil',
        href: (row: any) => faqsEdit({ faq: row.id }),
    },
    {
        type: 'button' as const,
        label: 'Delete',
        icon: 'trash2',
        variant: 'destructive' as const,
        onClick: (row: any) => openDeleteDialog(row),
    },
];

const pagination = computed(() => ({
    links: props.faqs.links,
    from: props.faqs.from,
    to: props.faqs.to,
    total: props.faqs.total,
}));

const confirmOpen = ref(false);
const deleting = ref(false);
const pendingDelete = ref<{ id: number; question: string } | null>(null);
const confirmDetails = computed(() => pendingDelete.value?.question || '');

const openDeleteDialog = (row: any) => {
    pendingDelete.value = { id: row.id, question: row.question };
    confirmOpen.value = true;
};

const confirmDelete = () => {
    if (!pendingDelete.value) return;

    deleting.value = true;
    router.delete(faqsDestroy({ faq: pendingDelete.value.id }).url, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            confirmOpen.value = false;
            pendingDelete.value = null;
        },
    });
};

const handleSearch = () => {
    router.get(
        faqsIndex().url,
        {
            search: search.value || null,
            category: selectedCategory.value || null,
            service: selectedService.value || null,
            status: selectedStatus.value || null,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const handleFilter = () => {
    router.get(
        faqsIndex().url,
        {
            search: search.value || null,
            category: selectedCategory.value || null,
            service: selectedService.value || null,
            status: selectedStatus.value || null,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const resetFilters = () => {
    search.value = '';
    selectedCategory.value = '';
    selectedService.value = '';
    selectedStatus.value = '';

    router.get(
        faqsIndex().url,
        {},
        {
            preserveState: true,
            replace: true,
        }
    );
};

const truncateHtml = (html: string, length: number) => {
    // Remove HTML tags for length calculation
    const text = html.replace(/<[^>]*>/g, '');
    if (text.length <= length) return html;
    // Truncate and add ellipsis
    return text.substring(0, length) + '...';
};
</script>
