<template>
    <Teleport to="body">
        <div class="pointer-events-none fixed right-4 top-4 z-[100] flex w-full max-w-sm flex-col gap-3">
            <TransitionGroup
                name="toast"
                tag="div"
                class="flex flex-col gap-3"
            >
                <div
                    v-for="t in toasts"
                    :key="t.id"
                    class="pointer-events-auto overflow-hidden rounded-xl border border-border bg-card shadow-lg"
                >
                    <div class="flex items-start gap-3 p-4">
                        <div
                            :class="[
                                'mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-full ring-1 ring-inset',
                                t.variant === 'success' && 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/20 dark:text-emerald-400 dark:ring-emerald-400/30',
                                t.variant === 'error' && 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/20 dark:text-red-400 dark:ring-red-400/30',
                                t.variant === 'info' && 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-900/20 dark:text-blue-400 dark:ring-blue-400/30',
                            ]"
                        >
                            <Icon
                                v-if="t.variant === 'success'"
                                name="check"
                                class="h-4 w-4"
                            />
                            <Icon
                                v-else-if="t.variant === 'error'"
                                name="triangleAlert"
                                class="h-4 w-4"
                            />
                            <Icon
                                v-else
                                name="info"
                                class="h-4 w-4"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <p v-if="t.title" class="text-sm font-semibold text-foreground">
                                {{ t.title }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                {{ t.message }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            aria-label="Dismiss notification"
                            @click="dismissToast(t.id)"
                        >
                            <Icon name="x" class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import Icon from '@/components/Icon.vue';
import { dismissToast, useToasts } from '@/composables/useToast';

const { toasts } = useToasts();
</script>

<style>
.toast-enter-active,
.toast-leave-active {
  transition: all 180ms ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>

