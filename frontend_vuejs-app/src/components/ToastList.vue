<script setup>
import { useToastStore } from "@/stores/toast";
const toast = useToastStore();
</script>

<template>
  <div class="fixed right-4 top-4 z-50 space-y-2" aria-live="polite">
    <div
      v-for="t in toast.items"
      :key="t.id"
      class="rounded px-4 py-2 text-sm text-white shadow"
      :class="t.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
    >
      {{ t.text }}
    </div>
  </div>
</template>
