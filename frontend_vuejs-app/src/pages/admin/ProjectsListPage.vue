<script setup>
import { ref, onMounted } from "vue";
import { FolderKanban, Plus, Pencil, Trash2, Star } from "lucide-vue-next";
import { adminApi } from "@/api";
import { useToastStore } from "@/stores/toast";
import ConfirmModal from "@/components/ConfirmModal.vue";
import { mediaUrl } from "@/utils/media";
import PageHeader from "@/components/PageHeader.vue";
import { Sparkles } from "lucide-vue-next";

const toast = useToastStore();

const projects = ref([]);
const page = ref(1);
const lastPage = ref(1);
const loading = ref(true);

const toDelete = ref(null);
const deleting = ref(false);

const statusClass = {
  published: "bg-ok/15 text-ok",
  draft: "bg-tint/10 text-muted",
  archived: "bg-amber-500/15 text-amber-500",
};

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
  <PageHeader
    title="Projects"
    subtitle="Manage your portfolio work"
    :icon="FolderKanban"
  >
    <RouterLink
      :to="{ name: 'admin-project-create' }"
      class="flex items-center gap-2 rounded-xl bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90"
    >
      <Plus class="h-4 w-4" aria-hidden="true" /> New project
    </RouterLink>
  </PageHeader>

  <p v-if="loading" class="text-muted">Loading...</p>
  <div
    v-else-if="!projects.length"
    class="rounded-2xl border border-dashed border-line p-10 text-center text-muted"
  >
    No projects yet. Create your first one.
  </div>

  <template v-else>
    <!-- Desktop table -->
    <div
      class="hidden overflow-hidden rounded-2xl border border-line bg-surface md:block"
    >
      <table class="w-full text-left text-sm">
        <thead
          class="border-b border-line bg-tint/5 text-xs uppercase tracking-wider text-muted"
        >
          <tr>
            <th class="p-4">Project</th>
            <th class="p-4">Status</th>
            <th class="p-4">Order</th>
            <th class="p-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          <tr v-for="p in projects" :key="p.id" class="hover:bg-tint/5">
            <td class="p-4">
              <div class="flex items-center gap-3">
                <img
                  v-if="p.thumbnail_url"
                  :src="mediaUrl(p.thumbnail_url)"
                  alt=""
                  class="h-11 w-16 rounded-lg object-cover"
                />
                <div v-else class="h-11 w-16 rounded-lg bg-tint/10"></div>
                <span class="font-medium">{{ p.title }}</span>
                <Star
                  v-if="p.featured"
                  class="h-4 w-4 fill-amber-400 text-amber-400"
                  aria-label="Featured"
                />
              </div>
            </td>
            <td class="p-4">
              <span
                class="rounded-full px-2.5 py-1 text-xs capitalize"
                :class="statusClass[p.status]"
                >{{ p.status }}</span
              >
            </td>
            <td class="p-4 text-muted">{{ p.sort_order }}</td>
            <td class="p-4">
              <div class="flex justify-end gap-2">
                <RouterLink
                  :to="{ name: 'admin-project-edit', params: { slug: p.slug } }"
                  class="rounded-lg border border-line p-2 text-muted hover:border-brand hover:text-brand"
                  :aria-label="`Edit ${p.title}`"
                >
                  <Pencil class="h-4 w-4" />
                </RouterLink>
                <button
                  type="button"
                  class="rounded-lg border border-line p-2 text-muted hover:border-danger hover:text-danger"
                  :aria-label="`Delete ${p.title}`"
                  @click="toDelete = p"
                >
                  <Trash2 class="h-4 w-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile cards -->
    <ul class="space-y-3 md:hidden">
      <li
        v-for="p in projects"
        :key="p.id"
        class="overflow-hidden rounded-2xl border border-line bg-surface"
      >
        <img
          v-if="p.thumbnail_url"
          :src="mediaUrl(p.thumbnail_url)"
          alt=""
          class="h-32 w-full object-cover"
        />
        <div class="p-4">
          <div class="flex items-start justify-between gap-2">
            <h2 class="font-semibold">{{ p.title }}</h2>
            <Star
              v-if="p.featured"
              class="h-4 w-4 shrink-0 fill-amber-400 text-amber-400"
              aria-label="Featured"
            />
          </div>
          <div class="mt-2 flex items-center justify-between">
            <span
              class="rounded-full px-2.5 py-1 text-xs capitalize"
              :class="statusClass[p.status]"
              >{{ p.status }}</span
            >
            <div class="flex gap-2">
              <RouterLink
                :to="{ name: 'admin-project-edit', params: { slug: p.slug } }"
                class="flex items-center gap-1.5 rounded-lg border border-line px-3 py-2 text-sm"
              >
                <Pencil class="h-4 w-4" /> Edit
              </RouterLink>
              <button
                type="button"
                class="flex items-center gap-1.5 rounded-lg border border-line px-3 py-2 text-sm text-danger"
                @click="toDelete = p"
              >
                <Trash2 class="h-4 w-4" /> Delete
              </button>
            </div>
          </div>
        </div>
      </li>
    </ul>

    <div
      v-if="lastPage > 1"
      class="mt-5 flex items-center justify-center gap-3 text-sm"
    >
      <button
        :disabled="page <= 1"
        class="rounded-lg border border-line px-4 py-2 disabled:opacity-40"
        @click="load(page - 1)"
      >
        Previous
      </button>
      <span class="text-muted">Page {{ page }} of {{ lastPage }}</span>
      <button
        :disabled="page >= lastPage"
        class="rounded-lg border border-line px-4 py-2 disabled:opacity-40"
        @click="load(page + 1)"
      >
        Next
      </button>
    </div>
  </template>

  <ConfirmModal
    :open="!!toDelete"
    title="Delete project?"
    :message="`“${toDelete?.title}” and its images will be permanently removed.`"
    :loading="deleting"
    @confirm="confirmDelete"
    @cancel="toDelete = null"
  />
</template>