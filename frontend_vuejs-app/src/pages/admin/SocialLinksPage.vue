<script setup>
import { onMounted, reactive, ref } from "vue";
import { adminApi } from "@/api";
import ConfirmModal from "@/components/ConfirmModal.vue";
import { useToastStore } from "@/stores/toast";

const toast = useToastStore();
const links = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingId = ref(null);
const deleteTarget = ref(null);
const deleting = ref(false);
const errors = ref({});

const emptyForm = () => ({ platform: "", url: "", icon: "", sort_order: 0 });
const form = reactive(emptyForm());

function resetForm() {
  Object.assign(form, emptyForm());
  editingId.value = null;
  errors.value = {};
}

function editLink(link) {
  Object.assign(form, {
    platform: link.platform,
    url: link.url,
    icon: link.icon ?? "",
    sort_order: link.sort_order ?? 0,
  });
  editingId.value = link.id;
  errors.value = {};
}

async function loadLinks() {
  loading.value = true;
  try {
    const { data } = await adminApi.socialLinks();
    links.value = data;
  } catch {
    toast.error("Could not load social links.");
  } finally {
    loading.value = false;
  }
}

async function saveLink() {
  saving.value = true;
  errors.value = {};
  try {
    if (editingId.value) {
      await adminApi.updateSocialLink(editingId.value, { ...form });
      toast.success("Social link updated.");
    } else {
      await adminApi.createSocialLink({ ...form });
      toast.success("Social link created.");
    }
    resetForm();
    await loadLinks();
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors ?? {};
      toast.error("Please check the highlighted fields.");
    } else {
      toast.error("Could not save the social link.");
    }
  } finally {
    saving.value = false;
  }
}

async function deleteLink() {
  deleting.value = true;
  try {
    await adminApi.deleteSocialLink(deleteTarget.value.id);
    toast.success("Social link deleted.");
    deleteTarget.value = null;
    await loadLinks();
  } catch {
    toast.error("Could not delete the social link.");
  } finally {
    deleting.value = false;
  }
}

onMounted(loadLinks);
</script>

<template>
  <section class="max-w-4xl">
    <h1 class="text-2xl font-bold">Social links</h1>
    <form class="mt-5 grid gap-4 rounded-lg bg-white p-5 shadow sm:grid-cols-2" @submit.prevent="saveLink">
      <label class="text-sm">
        Platform *
        <input v-model="form.platform" required maxlength="255" class="mt-1 w-full rounded border px-3 py-2" />
        <span v-if="errors.platform" class="mt-1 block text-red-600">{{ errors.platform[0] }}</span>
      </label>
      <label class="text-sm">
        URL *
        <input v-model="form.url" type="url" required maxlength="255" class="mt-1 w-full rounded border px-3 py-2" />
        <span v-if="errors.url" class="mt-1 block text-red-600">{{ errors.url[0] }}</span>
      </label>
      <label class="text-sm">
        Icon (optional)
        <input v-model="form.icon" maxlength="255" class="mt-1 w-full rounded border px-3 py-2" />
        <span v-if="errors.icon" class="mt-1 block text-red-600">{{ errors.icon[0] }}</span>
      </label>
      <label class="text-sm">
        Sort order
        <input v-model.number="form.sort_order" type="number" min="0" class="mt-1 w-full rounded border px-3 py-2" />
        <span v-if="errors.sort_order" class="mt-1 block text-red-600">{{ errors.sort_order[0] }}</span>
      </label>
      <div class="flex gap-2 sm:col-span-2">
        <button :disabled="saving" class="rounded bg-gray-900 px-4 py-2 text-white disabled:opacity-50">
          {{ saving ? "Saving..." : editingId ? "Save changes" : "Add social link" }}
        </button>
        <button v-if="editingId" type="button" class="rounded border px-4 py-2" @click="resetForm">Cancel edit</button>
      </div>
    </form>

    <p v-if="loading" class="mt-5 text-gray-500">Loading social links...</p>
    <div v-else-if="links.length" class="mt-5 overflow-x-auto rounded-lg bg-white shadow">
      <table class="w-full text-left text-sm">
        <thead class="border-b bg-gray-50 text-gray-600"><tr><th class="p-3">Platform</th><th class="p-3">URL</th><th class="p-3">Order</th><th class="p-3 text-right">Actions</th></tr></thead>
        <tbody>
          <tr v-for="link in links" :key="link.id" class="border-b last:border-0">
            <td class="p-3 font-medium">{{ link.platform }}</td>
            <td class="max-w-xs truncate p-3"><a :href="link.url" target="_blank" rel="noopener noreferrer" class="text-blue-700 hover:underline">{{ link.url }}</a></td>
            <td class="p-3">{{ link.sort_order }}</td>
            <td class="p-3 text-right">
              <button class="mr-3 text-blue-700 hover:underline" @click="editLink(link)">Edit</button>
              <button class="text-red-700 hover:underline" @click="deleteTarget = link">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-else class="mt-5 text-gray-500">No social links added yet.</p>

    <ConfirmModal :open="!!deleteTarget" title="Delete social link?" :message="`Delete ${deleteTarget?.platform}?`" :loading="deleting" @confirm="deleteLink" @cancel="deleteTarget = null" />
  </section>
</template>
