<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    modelValue: boolean;
    indeterminate?: boolean;
    disabled?: boolean;
    id?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
}>();

const el = ref<HTMLInputElement | null>(null);

const checked = computed({
    get: () => props.modelValue,
    set: (v: boolean) => emit('update:modelValue', v),
});

const syncIndeterminate = () => {
    if (!el.value) return;
    el.value.indeterminate = Boolean(props.indeterminate) && !props.modelValue;
};

onMounted(syncIndeterminate);
watch(() => [props.indeterminate, props.modelValue], syncIndeterminate);
</script>

<template>
    <input
        ref="el"
        :id="id"
        v-model="checked"
        type="checkbox"
        :disabled="disabled"
        class="h-4 w-4 rounded border-input"
    />
</template>

