<script setup>
defineProps({
  open: Boolean,
  title: { type: String, default: "Are you sure?" },
  message: { type: String, default: "" },
  confirmText: { type: String, default: "Delete" },
  loading: Boolean,
});
defineEmits(["confirm", "cancel"]);
</script>

<template>
  <div
    v-if="open"
    class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4"
    @click.self="$emit('cancel')"
  >
    <div
      class="w-full max-w-sm rounded-lg bg-white p-5 shadow-lg"
      role="dialog"
      aria-modal="true"
    >
      <h2 class="text-lg font-semibold">{{ title }}</h2>
      <p class="mt-2 text-sm text-gray-600">{{ message }}</p>
      <div class="mt-5 flex justify-end gap-2">
        <button
          class="rounded border px-4 py-1.5 text-sm"
          @click="$emit('cancel')"
        >
          Cancel
        </button>
        <button
          :disabled="loading"
          class="rounded bg-red-600 px-4 py-1.5 text-sm text-white disabled:opacity-50"
          @click="$emit('confirm')"
        >
          {{ loading ? "Working..." : confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>
