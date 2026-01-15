import { reactive } from 'vue';

export type ToastVariant = 'success' | 'error' | 'info';

export type Toast = {
    id: string;
    title?: string;
    message: string;
    variant: ToastVariant;
    timeoutMs?: number;
};

const state = reactive({
    toasts: [] as Toast[],
});

function uid() {
    return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

export function toast(input: Omit<Toast, 'id'>) {
    const id = uid();
    const t: Toast = {
        id,
        timeoutMs: input.timeoutMs ?? 3500,
        ...input,
    };

    state.toasts.unshift(t);

    if (t.timeoutMs && t.timeoutMs > 0) {
        window.setTimeout(() => dismissToast(id), t.timeoutMs);
    }
}

export function dismissToast(id: string) {
    const idx = state.toasts.findIndex((t) => t.id === id);
    if (idx !== -1) state.toasts.splice(idx, 1);
}

export function useToasts() {
    return state;
}

