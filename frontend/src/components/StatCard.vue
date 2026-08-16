<script setup>
defineProps({
    label: String,
    value: [String, Number],
    helper: String,
    icon: [Object, Function],
    highlighted: Boolean,
})
</script>

<template>
    <div
        class="flex min-h-28 items-center gap-4 rounded-lg border p-5 shadow-sm"
        :class="highlighted ? 'border-emerald-200 bg-emerald-50/50' : 'border-slate-200 bg-white'"
    >
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-600">
            <component :is="icon" class="h-7 w-7" />
        </div>
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase text-slate-500">{{ label }}</p>
            <p class="mt-1 truncate text-2xl font-bold text-slate-900">{{ value }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ helper }}</p>
        </div>
    </div>
</template>
