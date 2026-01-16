<script setup lang="ts">
import { computed, reactive } from 'vue';
import IndeterminateCheckbox from '@/components/admin/IndeterminateCheckbox.vue';

type PermissionDef = { slug: string; label: string };
type ModuleDef = { key: string; label: string; permissions: PermissionDef[] };
type GroupDef = { key: string; label: string; modules: ModuleDef[] };

const props = defineProps<{
    groups: GroupDef[];
    modelValue: string[];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string[]): void;
}>();

const selected = computed({
    get: () => new Set(props.modelValue),
    set: (set: Set<string>) => emit('update:modelValue', Array.from(set)),
});

const expanded = reactive({
    groups: new Set<string>(),
    modules: new Set<string>(),
});

const expandAll = () => {
    expanded.groups = new Set(props.groups.map((g) => g.key));
    expanded.modules = new Set(
        props.groups.flatMap((g) => g.modules.map((m) => `${g.key}:${m.key}`)),
    );
};

const collapseAll = () => {
    expanded.groups = new Set();
    expanded.modules = new Set();
};

const groupPermissionSlugs = (group: GroupDef) =>
    group.modules.flatMap((m) => m.permissions.map((p) => p.slug));

const modulePermissionSlugs = (module: ModuleDef) => module.permissions.map((p) => p.slug);

const toggleMany = (slugs: string[], value: boolean) => {
    const set = new Set(selected.value);
    for (const slug of slugs) {
        if (value) set.add(slug);
        else set.delete(slug);
    }
    selected.value = set;
};

const stateFor = (slugs: string[]) => {
    const set = selected.value;
    const count = slugs.filter((s) => set.has(s)).length;
    return {
        checked: count > 0 && count === slugs.length,
        indeterminate: count > 0 && count < slugs.length,
    };
};

const toggleGroupExpanded = (key: string) => {
    const next = new Set(expanded.groups);
    if (next.has(key)) next.delete(key);
    else next.add(key);
    expanded.groups = next;
};

const toggleModuleExpanded = (key: string) => {
    const next = new Set(expanded.modules);
    if (next.has(key)) next.delete(key);
    else next.add(key);
    expanded.modules = next;
};
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center gap-2">
            <button
                type="button"
                class="rounded-md border border-border bg-background px-3 py-2 text-sm hover:bg-muted"
                @click="expandAll"
            >
                Expand All
            </button>
            <button
                type="button"
                class="rounded-md border border-border bg-background px-3 py-2 text-sm hover:bg-muted"
                @click="collapseAll"
            >
                Collapse All
            </button>
        </div>

        <div class="rounded-lg border border-border bg-card">
            <div
                v-for="group in groups"
                :key="group.key"
                class="border-b border-border last:border-b-0"
            >
                <div class="flex items-center justify-between gap-4 p-4">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="text-muted-foreground hover:text-foreground"
                            @click="toggleGroupExpanded(group.key)"
                            :aria-label="expanded.groups.has(group.key) ? 'Collapse group' : 'Expand group'"
                        >
                            <span class="text-lg leading-none">
                                {{ expanded.groups.has(group.key) ? '▾' : '▸' }}
                            </span>
                        </button>

                        <div class="flex items-center gap-2">
                            <IndeterminateCheckbox
                                :model-value="stateFor(groupPermissionSlugs(group)).checked"
                                :indeterminate="stateFor(groupPermissionSlugs(group)).indeterminate"
                                @update:model-value="toggleMany(groupPermissionSlugs(group), $event)"
                            />
                            <div>
                                <div class="font-medium">{{ group.label }}</div>
                                <div class="text-xs text-muted-foreground">
                                    Expand to see all modules.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="expanded.groups.has(group.key)" class="px-4 pb-4">
                    <div class="space-y-3 rounded-md border border-border bg-background p-4">
                        <div v-for="module in group.modules" :key="module.key">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <button
                                        type="button"
                                        class="text-muted-foreground hover:text-foreground"
                                        @click="toggleModuleExpanded(`${group.key}:${module.key}`)"
                                        :aria-label="
                                            expanded.modules.has(`${group.key}:${module.key}`)
                                                ? 'Collapse module'
                                                : 'Expand module'
                                        "
                                    >
                                        <span class="text-lg leading-none">
                                            {{
                                                expanded.modules.has(`${group.key}:${module.key}`)
                                                    ? '▾'
                                                    : '▸'
                                            }}
                                        </span>
                                    </button>

                                    <div class="flex items-center gap-2">
                                        <IndeterminateCheckbox
                                            :model-value="stateFor(modulePermissionSlugs(module)).checked"
                                            :indeterminate="stateFor(modulePermissionSlugs(module)).indeterminate"
                                            @update:model-value="toggleMany(modulePermissionSlugs(module), $event)"
                                        />
                                        <div class="font-medium">{{ module.label }}</div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="expanded.modules.has(`${group.key}:${module.key}`)"
                                class="mt-3 grid gap-2 pl-8 sm:grid-cols-2 lg:grid-cols-4"
                            >
                                <label
                                    v-for="p in module.permissions"
                                    :key="p.slug"
                                    class="flex items-center gap-2 rounded-md border border-border bg-card px-3 py-2 text-sm"
                                >
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-input"
                                        :checked="selected.has(p.slug)"
                                        @change="toggleMany([p.slug], ($event.target as HTMLInputElement).checked)"
                                    />
                                    <span>{{ p.label }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

