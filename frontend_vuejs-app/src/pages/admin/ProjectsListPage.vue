<script setup>
import { ref, onMounted } from "vue";
import { adminApi } from "@/api";
import { useToastStore } from "@/stores/toast";
import ConfirmModal from "@/components/ConfirmModal.vue";
import { mediaUrl } from "@/utils/media";

const toast = useToastStore();

const projects = ref([]);
const page = ref(1);
const lastPage = ref(1);
const loading = ref(true);

const toDelete = ref(null);
const deleting = ref(false);

async function load(p = 1) {
  loading.value = true;
  try {
    const { data } = await adminApi.projects({ page: p });
    projects.value = data.data;
    page.value = data.current_page;
    lastPage.value = data.last_page;
  } catch {
    toast.error("Could not load projects. ");
  } finally {
    loading.value = false;
  }
}

async function confirmDelete() {
  deleting.value = true;
  try {
    await adminApi.deleteProject(toDelete.value.slug);
    toast.success("Project delete.");
    toDelete.value = null;

    await load(
      projects.value.length === 1 && page.value > 1
        ? page.value - 1
        : page.value,
    );
  } catch {
    toast.error("Delete failed.");
  } finally {
    deleting.value = false;
  }
}

onMounted(() => load());
</script>

<template>
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">Projects</h1>
    <RouterLink
      :to="{ name: 'admin-project-create' }"
      class="rounded bg-gray-900 px-4 py-2 text-sm text-white"
    >
      + New project
    </RouterLink>
  </div>
  <p v-if="loading" class="mt-6 text-gray-500">Loading...</p>
  <p v-else-if="!projects.length" class="mt-6 text-gray-500">
    No projects yet. Create your first one.
  </p>

  <div v-else class="mt-6 overflow-x-auto rounded-lg bg-white shadow">
    <table class="w-full text-left text-sm">
      <thead class="border-b bg-gray-50 text-gray-600">
        <tr>
          <th class="p-3">Project</th>
          <th class="p-3">Status</th>
          <th class="p-3">Featured</th>
          <th class="p-3">Order</th>
          <th class="p-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="p in projects" :key="p.id" class="border-b last:border-0">
          <td class="p-3">
            <div class="flex items-center gap-3">
              <img
                v-if="p.thumbnail_url"
                :src="mediaUrl(p.thumbnail_url)"
                alt=""
                class="h-10 w-14 rounded object-cover"
              />
              <div v-else class="h-10 w-14 rounded bg-gray-200"></div>
              <span class="font-medium">{{ p.title }}</span>
            </div>
          </td>
          <td class="p-3">
            <span
              class="rounded-full px-2 py-0.5 text-xs"
              :class="
                p.status === 'published'
                  ? 'bg-green-100 text-green-700'
                  : 'bg-gray-200 text-gray-700'
              "
            >
              {{ p.status }}
            </span>
          </td>
          <td class="p-3">{{ p.featured ? "★" : "-" }}</td>
          <td class="p-3">{{ p.sort_order }}</td>
          <td class="p-3 text-right">
            <RouterLink
              :to="{ name: 'admin-project-edit', params: { slug: p.slug } }"
              class="mr-3 text-blue-600 hover:underline"
              >Edit</RouterLink
            >
            <button class="text-red-600 hover:underline" @click="toDelete = p">
              Delete
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div
    v-if="lastPage > 1"
    class="mt-4 flex items-center justify-center gap-3 text-sm"
  >
    <button
      :disabled="page <= 1"
      class="rounded border px-3 py-1 disabled:opacity-40"
      @click="load(page - 1)"
    >
      Previous
    </button>
    <span>Page {{ page }} of {{ lastPage }}</span>
    <button
      :disabled="page >= lastPage"
      class="rounded border px-3 py-1 disabled:opacity-40"
      @click="load(page + 1)"
    >
      Next
    </button>
  </div>

  <ConfirmModal
    :open="!!toDelete"
    title="Delete project?"
    :message="`“${toDelete?.title}” and its images will be permanently removed.`"
    :loading="deleting"
    @confirm="confirmDelete"
    @cancel="toDelete = null"
  />
</template>
