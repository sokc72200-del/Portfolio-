<script setup>
import { onMounted, ref } from "vue";
import { adminApi } from "@/api";
import ConfirmModal from "@/components/ConfirmModal.vue";
import { useToastStore } from "@/stores/toast";

const toast = useToastStore();
const messages = ref([]);
const selectedMessage = ref(null);
const page = ref(1);
const lastPage = ref(1);
const unreadCount = ref(0);
const loading = ref(true);
const detailLoading = ref(false);
const deleteTarget = ref(null);
const deleting = ref(false);

async function loadMessages(targetPage = 1) {
  loading.value = true;
  try {
    const { data } = await adminApi.messages({ page: targetPage });
    messages.value = data.messages.data;
    page.value = data.messages.current_page;
    lastPage.value = data.messages.last_page;
    unreadCount.value = data.unread_count;
  } catch {
    toast.error("Could not load messages.");
  } finally {
    loading.value = false;
  }
}

async function openMessage(message) {
  selectedMessage.value = message;
  detailLoading.value = true;
  try {
    const { data } = await adminApi.message(message.id);
    selectedMessage.value = data;
    const row = messages.value.find((item) => item.id === data.id);
    if (row) row.is_read = true;
    unreadCount.value = Math.max(0, unreadCount.value - (message.is_read ? 0 : 1));
  } catch {
    toast.error("Could not open this message.");
  } finally {
    detailLoading.value = false;
  }
}

async function deleteMessage() {
  deleting.value = true;
  try {
    await adminApi.deleteMessage(deleteTarget.value.id);
    toast.success("Message deleted.");
    if (selectedMessage.value?.id === deleteTarget.value.id) {
      selectedMessage.value = null;
    }
    deleteTarget.value = null;
    await loadMessages(messages.value.length === 1 && page.value > 1 ? page.value - 1 : page.value);
  } catch {
    toast.error("Could not delete the message.");
  } finally {
    deleting.value = false;
  }
}

onMounted(() => loadMessages());
</script>

<template>
  <section>
    <div class="flex flex-wrap items-end justify-between gap-2">
      <div>
        <h1 class="text-2xl font-bold">Messages</h1>
        <p class="mt-1 text-sm text-gray-600">{{ unreadCount }} unread</p>
      </div>
    </div>

    <p v-if="loading" class="mt-5 text-gray-500">Loading messages...</p>
    <p v-else-if="!messages.length" class="mt-5 text-gray-500">No messages received yet.</p>
    <div v-else class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <button
          v-for="message in messages"
          :key="message.id"
          class="block w-full border-b p-4 text-left last:border-0 hover:bg-gray-50"
          :class="selectedMessage?.id === message.id ? 'bg-gray-50' : ''"
          @click="openMessage(message)"
        >
          <span class="flex items-center justify-between gap-2">
            <span class="truncate font-medium">{{ message.name }}</span>
            <span v-if="!message.is_read" class="shrink-0 rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800">Unread</span>
          </span>
          <span class="mt-1 block truncate text-sm">{{ message.subject || "No subject" }}</span>
          <span class="mt-1 block truncate text-xs text-gray-500">{{ message.email }}</span>
        </button>
      </div>

      <article class="min-h-48 rounded-lg bg-white p-5 shadow">
        <p v-if="detailLoading" class="text-gray-500">Loading message...</p>
        <p v-else-if="!selectedMessage" class="text-gray-500">Select a message to read it.</p>
        <template v-else>
          <div class="flex items-start justify-between gap-3">
            <div>
              <h2 class="text-lg font-semibold">{{ selectedMessage.subject || "No subject" }}</h2>
              <p class="mt-1 text-sm text-gray-600">{{ selectedMessage.name }} · <a :href="`mailto:${selectedMessage.email}`" class="text-blue-700 hover:underline">{{ selectedMessage.email }}</a></p>
              <p class="mt-1 text-xs text-gray-500">{{ new Date(selectedMessage.created_at).toLocaleString() }}</p>
            </div>
            <button class="text-sm text-red-700 hover:underline" @click="deleteTarget = selectedMessage">Delete</button>
          </div>
          <p class="mt-5 whitespace-pre-line break-words text-sm leading-relaxed">{{ selectedMessage.body }}</p>
        </template>
      </article>
    </div>

    <div v-if="lastPage > 1" class="mt-4 flex items-center justify-center gap-3 text-sm">
      <button :disabled="page <= 1" class="rounded border px-3 py-1 disabled:opacity-40" @click="loadMessages(page - 1)">Previous</button>
      <span>Page {{ page }} of {{ lastPage }}</span>
      <button :disabled="page >= lastPage" class="rounded border px-3 py-1 disabled:opacity-40" @click="loadMessages(page + 1)">Next</button>
    </div>

    <ConfirmModal :open="!!deleteTarget" title="Delete message?" message="This message will be permanently deleted." :loading="deleting" @confirm="deleteMessage" @cancel="deleteTarget = null" />
  </section>
</template>
