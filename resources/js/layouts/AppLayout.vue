<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import ToastContainer from '@/components/admin/ToastContainer.vue';
import { toast } from '@/composables/useToast';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage<any>();

watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;

        if (flash.success) toast({ variant: 'success', message: String(flash.success) });
        if (flash.error) toast({ variant: 'error', message: String(flash.error) });
        if (flash.warning) toast({ variant: 'info', title: 'Warning', message: String(flash.warning) });
        if (flash.info) toast({ variant: 'info', message: String(flash.info) });
    },
    { immediate: true },
);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <slot />
        </div>
        <ToastContainer />
    </AppLayout>
</template>
