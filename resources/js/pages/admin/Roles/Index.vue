<template>
    <AppLayout>
        <Head title="Roles" />

        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold">Roles</h1>
            <Link
                href="/admin/roles/create"
                class="rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90"
            >
                Create Role
            </Link>
        </div>

        <DataTable :columns="columns" :data="roles.data" :actions="actions" :pagination="roles" />

        <ConfirmDialog
            v-model:open="confirmOpen"
            title="Delete role?"
            description="This action cannot be undone."
            confirm-text="Delete"
            confirm-variant="destructive"
            :loading="deleting"
            loading-text="Deleting..."
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/admin/DataTable.vue';
import ConfirmDialog from '@/components/admin/ConfirmDialog.vue';
import { toast } from '@/composables/useToast';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    roles: any;
}>();

const page = usePage<any>();
const canDelete = computed(() => (page.props.auth?.permissions || []).includes('roles.delete'));

const confirmOpen = ref(false);
const pendingDeleteId = ref<number | null>(null);
const deleting = ref(false);

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'slug', label: 'Slug' },
    { key: 'users_count', label: 'Users' },
];

const actions = computed(() => {
    const base: any[] = [
        {
            type: 'button',
            label: 'Edit',
            icon: 'pencil',
            onClick: (row: any) => router.visit(`/admin/roles/${row.id}/edit`),
        },
    ];

    if (canDelete.value) {
        base.push({
            type: 'button',
            label: 'Delete',
            icon: 'trash',
            variant: 'destructive',
            onClick: (row: any) => {
                pendingDeleteId.value = row.id;
                confirmOpen.value = true;
            },
        });
    }

    return base;
});

const confirmDelete = () => {
    if (!pendingDeleteId.value) return;
    deleting.value = true;
    router.delete(`/admin/roles/${pendingDeleteId.value}`, {
        onSuccess: () => toast({ variant: 'success', message: 'Role deleted.' }),
        onError: () => toast({ variant: 'error', message: 'Failed to delete role.' }),
        onFinish: () => {
            pendingDeleteId.value = null;
            confirmOpen.value = false;
            deleting.value = false;
        },
    });
};
</script>

