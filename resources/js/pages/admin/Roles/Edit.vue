<template>
    <AppLayout>
        <Head title="Edit Role" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">Edit Role</h1>
                <Link href="/admin/roles" class="text-muted-foreground hover:text-foreground">
                    Back to Roles
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="space-y-6">
                        <div class="rounded-lg border border-border bg-card p-6">
                            <h2 class="mb-4 text-xl font-semibold">Role Details</h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-medium">
                                        Name <span class="text-destructive">*</span>
                                    </label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="w-full rounded-md border border-input bg-background px-3 py-2"
                                    />
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Slug</label>
                                    <input
                                        v-model="form.slug"
                                        type="text"
                                        class="w-full rounded-md border border-input bg-background px-3 py-2"
                                    />
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Description</label>
                                    <textarea
                                        v-model="form.description"
                                        rows="3"
                                        class="w-full rounded-md border border-input bg-background px-3 py-2"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-lg border border-border bg-card p-6">
                            <h2 class="mb-4 text-xl font-semibold">Permissions</h2>
                            <PermissionTree v-model="form.permissions" :groups="permissionGroups" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link
                        href="/admin/roles"
                        class="rounded-md border border-border bg-background px-4 py-2 hover:bg-muted"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Updating...' : 'Update Role' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PermissionTree from './PermissionTree.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

type PermissionDef = { slug: string; label: string };
type ModuleDef = { key: string; label: string; permissions: PermissionDef[] };
type GroupDef = { key: string; label: string; modules: ModuleDef[] };

const props = defineProps<{
    role: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
    };
    selectedPermissions: string[];
    permissionGroups: GroupDef[];
}>();

const form = useForm({
    name: props.role.name,
    slug: props.role.slug,
    description: props.role.description || '',
    permissions: props.selectedPermissions || ([] as string[]),
});

const submit = () => {
    form.put(`/admin/roles/${props.role.id}`);
};
</script>

