<template>
    <AppLayout>
        <Head title="Create Role" />

        <div class="space-y-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                        Create Role
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Define a role and assign permissions to control access across the admin.
                    </p>
                </div>

                <Link
                    :href="rolesIndex().url"
                    class="inline-flex items-center text-sm text-muted-foreground hover:text-foreground"
                >
                    Back to Roles
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Role Details</CardTitle>
                                <CardDescription>
                                    Basic info used for display and programmatic checks.
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="name">
                                        Name <span class="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        required
                                        placeholder="e.g. Admin"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="slug">Slug</Label>
                                    <Input
                                        id="slug"
                                        v-model="form.slug"
                                        type="text"
                                        placeholder="Auto-generated from name"
                                    />
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        Used in code and database. Leave empty to auto-generate.
                                    </p>
                                    <InputError :message="form.errors.slug" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="description">Description</Label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="w-full rounded-md border border-input bg-background px-3 py-2"
                                        placeholder="Optional notes about this role..."
                                    ></textarea>
                                    <InputError :message="form.errors.description" />
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div class="space-y-6">
                        <Card>
                            <CardHeader class="space-y-1">
                                <CardTitle>Permissions</CardTitle>
                                <CardDescription>
                                    Select what this role can do. Selected:
                                    <span class="font-medium text-foreground">
                                        {{ form.permissions.length }}
                                    </span>
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <PermissionTree
                                    v-model="form.permissions"
                                    :groups="permissionGroups"
                                />
                                <InputError :message="form.errors.permissions" />
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <div
                    class="sticky bottom-4 z-10 rounded-xl border border-border bg-background/80 p-4 shadow-sm backdrop-blur"
                >
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <Button as-child variant="secondary">
                            <Link :href="rolesIndex().url">Cancel</Link>
                        </Button>
                        <Button type="submit" :loading="form.processing">
                            Create Role
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PermissionTree from './PermissionTree.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { index as rolesIndex, store as rolesStore } from '@/routes/admin/roles';

type PermissionDef = { slug: string; label: string };
type ModuleDef = { key: string; label: string; permissions: PermissionDef[] };
type GroupDef = { key: string; label: string; modules: ModuleDef[] };

defineProps<{
    permissionGroups: GroupDef[];
}>();

const form = useForm({
    name: '',
    slug: '',
    description: '',
    permissions: [] as string[],
});

const submit = () => {
    form.post(rolesStore().url);
};
</script>

