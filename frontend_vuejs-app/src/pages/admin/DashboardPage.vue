<script setup>
import { ref, onMounted } from "vue";
import { adminApi } from "@/api";

const stats = ref({ projects: 0, skills: 0, unread: 0 });
const loading = ref(true);
const error = ref("");

onMounted(async () => {
  try {
    const [p, s, m] = await Promise.all([
      adminApi.projects({ page: 1 }),
      adminApi.skills(),
      adminApi.messages({ unread: 1 }),
    ]);
    stats.value = {
      projects: p.data.total,
      skills: s.data.length,
      unread: m.data.unread_count,
    };
  } catch {
    error.value = "Could not load dashboard statistics.";
  } finally {
    loading.value = false;
  }
});
</script>
<template>
  <h1 class="text-2xl font-bold">Dashboard</h1>

  <p v-if="loading" class="mt-4 text-gray-500">Loading...</p>
  <p v-else-if="error" class="mt-4 text-red-600">{{ error }}</p>
  <div v-else class="mt-6 grid gap-4 sm:grid-cols-3">
    <RouterLink
      to="/admin/projects"
      class="rounded-lg bg-white p-5 shadow hover:shadow-md"
    >
      <p class="text-sm text-gray-500">Projects</p>
      <p class="mt-1 text-3xl font-bold">{{ stats.projects }}</p>
    </RouterLink>
    <div class="rounded-lg bg-white p-5 shadow">
      <p class="text-sm text-gray-500">Skills</p>
      <p class="mt-1 text-3xl font-bold">{{ stats.skills }}</p>
    </div>
    <div class="rounded-lg bg-white p-5 shadow">
      <p class="text-sm text-gray-500">Unread messages</p>
      <p class="mt-1 text-3xl font-bold">{{ stats.unread }}</p>
    </div>
  </div>
</template>
