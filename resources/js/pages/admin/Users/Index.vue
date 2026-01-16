<template>
    <AppLayout>
        <Head title="Users" />

        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold">Users</h1>
        </div>

        <DataTable :columns="columns" :data="users.data" :actions="actions" :pagination="users" />
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/admin/DataTable.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    users: any;
}>();

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'email_verified_at', label: 'Verified' },
    { key: 'created_at', label: 'Created' },
];

const actions = computed(() => [
    {
        type: 'button',
        label: 'Edit',
        icon: 'pencil',
        onClick: (row: any) => router.visit(`/admin/users/${row.id}/edit`),
    },
]);
</script>

