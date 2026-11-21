<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: "" },
  icon: { type: [Object, Function], default: null },
});
</script>
<template>
  <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-3">
      <span
        v-if="icon"
        class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/15 text-brand"
      >
        <component :is="icon" class="h-5 w-5" aria-hidden="true" />
      </span>
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ title }}</h1>
        <p v-if="subtitle" class="text-sm text-muted">{{ subtitle }}</p>
      </div>
    </div>
    <slot />
  </div>
</template>
