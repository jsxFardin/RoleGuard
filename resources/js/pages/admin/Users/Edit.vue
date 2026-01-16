<template>
    <AppLayout>
        <Head title="Edit User" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">Edit User</h1>
                <Link href="/admin/users" class="text-muted-foreground hover:text-foreground">
                    Back to Users
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="rounded-lg border border-border bg-card p-6">
                    <h2 class="mb-4 text-xl font-semibold">User</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <div class="text-sm font-medium text-muted-foreground">Name</div>
                            <div class="mt-1">{{ user.name }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-muted-foreground">Email</div>
                            <div class="mt-1">{{ user.email }}</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-border bg-card p-6">
                    <h2 class="mb-4 text-xl font-semibold">Roles</h2>
                    <div class="grid gap-2 md:grid-cols-2">
                        <label
                            v-for="role in roles"
                            :key="role.id"
                            class="flex items-center gap-2 rounded-md border border-border bg-background px-3 py-2"
                        >
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-input"
                                :checked="form.roles.includes(role.id)"
                                @change="toggleRole(role.id, ($event.target as HTMLInputElement).checked)"
                            />
                            <span class="font-medium">{{ role.name }}</span>
                            <span class="text-xs text-muted-foreground">({{ role.slug }})</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link
                        href="/admin/users"
                        class="rounded-md border border-border bg-background px-4 py-2 hover:bg-muted"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    user: { id: number; name: string; email: string };
    roles: Array<{ id: number; name: string; slug: string }>;
    selectedRoles: number[];
}>();

const form = useForm({
    roles: props.selectedRoles || ([] as number[]),
});

const toggleRole = (id: number, checked: boolean) => {
    const next = new Set(form.roles);
    if (checked) next.add(id);
    else next.delete(id);
    form.roles = Array.from(next);
};

const submit = () => {
    form.put(`/admin/users/${props.user.id}`);
};
</script>

