<template>
    <div class="rounded-lg border border-input bg-background">
        <div v-if="isClient && QuillEditor" class="min-h-[240px]">
            <component
                :is="QuillEditor"
                v-model:content="value"
                content-type="html"
                theme="snow"
                :toolbar="toolbar"
                :placeholder="placeholder"
                :read-only="disabled"
                :style="editorStyle"
            />
        </div>
        <div v-else class="p-4 text-sm text-muted-foreground">Loading editor…</div>
    </div>
</template>

<script setup lang="ts">
import '@vueup/vue-quill/dist/vue-quill.snow.css';

import { computed, defineAsyncComponent } from 'vue';

const isClient = typeof window !== 'undefined';

// SSR-safe: only load Quill on the client.
const QuillEditor = isClient
    ? defineAsyncComponent(async () => {
          const mod: any = await import('@vueup/vue-quill');
          return mod.QuillEditor ?? mod.default ?? mod;
      })
    : null;

const props = withDefaults(
    defineProps<{
        modelValue: string;
        placeholder?: string;
        height?: number;
        disabled?: boolean;
        toolbar?: any;
    }>(),
    {
        placeholder: 'Write here…',
        height: 500,
        disabled: false,
        // Inline default (script-setup macro hoisting safe)
        toolbar: () => [
            [{ header: [1, 2, 3, 4, 5, 6, false] }],
            [{ font: [] }],
            [{ size: ['small', false, 'large', 'huge'] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ color: [] }, { background: [] }],
            [{ script: 'sub' }, { script: 'super' }],
            [{ align: [] }],
            [{ list: 'ordered' }, { list: 'bullet' }, { indent: '-1' }, { indent: '+1' }],
            [{ direction: 'rtl' }],
            ['blockquote', 'code-block'],
            ['link', 'image', 'video'],
            ['clean'],
        ],
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const value = computed({
    get: () => props.modelValue,
    set: (v: string) => emit('update:modelValue', v ?? ''),
});

const editorStyle = computed(() => ({
    minHeight: `${props.height}px`,
}));
</script>

