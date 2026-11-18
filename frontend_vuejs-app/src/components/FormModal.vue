<script setup>
defineProps({
  open: Boolean,
  title: String,
  saving: Boolean,
});
defineEmits(["close", "submit"]);
</script>

<template>
  <div
    v-if="open"
    class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4"
    @click.self="$emit('close')"
  >
    <form
      class="max-h-[90vh] w-full max-w-md space-y-4 overflow-y-auto rounded-lg bg-white p-6 shadow-lg"
      novalidate
      @submit.prevent="$emit('submit')"
    >
      <h2 class="text-lg font-semibold">{{ title }}</h2>
      <slot />
      <div class="flex justify-end gap-2 pt-2">
        <button
          type="button"
          class="rounded border px-4 py-2 text-sm"
          @click="$emit('close')"
        >
          Cancel
        </button>
        <button
          :disabled="saving"
          class="rounded bg-gray-900 px-4 py-2 text-sm text-white disabled:opacity-50"
        >
          {{ saving ? "Saving..." : "Save" }}
        </button>
      </div>
    </form>
  </div>
</template>
