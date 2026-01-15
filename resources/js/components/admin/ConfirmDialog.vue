<template>
    <Dialog :open="open" @update:open="(v) => emit('update:open', v)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader class="space-y-3">
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <div v-if="details" class="rounded-lg border border-border bg-muted/30 p-3 text-sm text-muted-foreground">
                {{ details }}
            </div>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button variant="secondary" :disabled="loading">
                        {{ cancelText }}
                    </Button>
                </DialogClose>

                <Button
                    :variant="confirmVariant"
                    :disabled="loading"
                    @click="emit('confirm')"
                >
                    <span v-if="loading">{{ loadingText }}</span>
                    <span v-else>{{ confirmText }}</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

withDefaults(
    defineProps<{
        open: boolean;
        title: string;
        description: string;
        details?: string;
        confirmText?: string;
        cancelText?: string;
        confirmVariant?: 'default' | 'destructive' | 'secondary';
        loading?: boolean;
        loadingText?: string;
    }>(),
    {
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        confirmVariant: 'destructive',
        loading: false,
        loadingText: 'Working...',
    },
);

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'confirm'): void;
}>();
</script>

