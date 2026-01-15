<template>
    <AppLayout>
        <Head title="Edit Service" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">Edit Service</h1>
                <Link
                    href="/admin/services"
                    class="text-muted-foreground hover:text-foreground"
                >
                    Back to Services
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="space-y-6">
                        <!-- Basic Information -->
                        <div class="rounded-lg border border-border bg-card p-6">
                            <h2 class="mb-4 text-xl font-semibold">Basic Information</h2>

                            <div class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-medium">
                                        Parent Service (optional)
                                    </label>
                                    <select
                                        v-model="form.parent_id"
                                        class="w-full rounded-md border border-input bg-background px-3 py-2"
                                    >
                                        <option :value="null">None (Main Service)</option>
                                        <option
                                            v-for="parent in parentServices"
                                            :key="parent.id"
                                            :value="parent.id"
                                        >
                                            {{ parent.title }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">
                                        Title <span class="text-destructive">*</span>
                                    </label>
                                    <input
                                        v-model="form.title"
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
                                        required
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

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Icon</label>
                                    <input
                                        v-model="form.icon"
                                        type="text"
                                        class="w-full rounded-md border border-input bg-background px-3 py-2"
                                        placeholder="e.g., building, users, calculator"
                                    />
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Image URL</label>
                                    <input
                                        v-model="form.image"
                                        type="text"
                                        class="w-full rounded-md border border-input bg-background px-3 py-2"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Settings -->
                        <div class="rounded-lg border border-border bg-card p-6">
                            <h2 class="mb-4 text-xl font-semibold">Settings</h2>

                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        id="is_active"
                                        class="rounded border-input"
                                    />
                                    <label for="is_active" class="text-sm font-medium">
                                        Active
                                    </label>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Sort Order</label>
                                    <input
                                        v-model.number="form.sort_order"
                                        type="number"
                                        class="w-full rounded-md border border-input bg-background px-3 py-2"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Content -->
                        <div class="rounded-lg border border-border bg-card p-6">
                            <h2 class="mb-4 text-xl font-semibold">Content</h2>
                            <AppTextEditor
                                v-model="form.content"
                                :height="500"
                            />
                        </div>

                        <!-- SEO -->
                        <div class="rounded-lg border border-border bg-card p-6">
                            <h2 class="mb-4 text-xl font-semibold">SEO Settings</h2>

                            <div class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-medium">Meta Title</label>
                                    <input
                                        v-model="form.meta_title"
                                        type="text"
                                        class="w-full rounded-md border border-input bg-background px-3 py-2"
                                    />
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium">Meta Description</label>
                                    <textarea
                                        v-model="form.meta_description"
                                        rows="3"
                                        class="w-full rounded-md border border-input bg-background px-3 py-2"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link
                        href="/admin/services"
                        class="rounded-md border border-border bg-background px-4 py-2 hover:bg-muted"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Updating...' : 'Update Service' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import AppTextEditor from '@/components/AppTextEditor.vue';

interface Props {
    service: {
        id: number;
        parent_id: number | null;
        slug: string;
        title: string;
        description: string | null;
        content: string | null;
        icon: string | null;
        image: string | null;
        meta_title: string | null;
        meta_description: string | null;
        is_active: boolean;
        sort_order: number;
    };
    parentServices: Array<{
        id: number;
        title: string;
    }>;
}

const props = defineProps<Props>();

const form = useForm({
    parent_id: props.service.parent_id,
    slug: props.service.slug,
    title: props.service.title,
    description: props.service.description || '',
    content: props.service.content || '',
    icon: props.service.icon || '',
    image: props.service.image || '',
    meta_title: props.service.meta_title || '',
    meta_description: props.service.meta_description || '',
    is_active: props.service.is_active,
    sort_order: props.service.sort_order,
});

const submit = () => {
    form.put(`/admin/services/${props.service.id}`);
};
</script>
