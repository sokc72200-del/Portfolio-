<script setup>
import { ref, computed, onMounted } from "vue";
import { publicApi } from "@/api";
import ProjectCard from "@/components/ProjectCard.vue";

const projects = ref([]);
const loading = ref(true);
const error = ref("");
const activeSkill = ref(null);

const skillNames = computed(() => {
  const names = new Set();
  projects.value.forEach((p) => p.skills?.forEach((s) => names.add(s.name)));
  return [...names].sort();
});

const filtered = computed(() => {
  if (!activeSkill.value) return projects.value;
  return projects.value.filter((p) =>
    p.skills?.some((s) => s.name === activeSkill.value),
  );
});

onMounted(async () => {
  try {
    const { data } = await publicApi.projects();
    projects.value = data;
  } catch {
    error.value = "Could not load projects.";
  } finally {
    loading.value = false;
  }
});
</script>
<template>
  <section>
    <h1 class="text-2xl font-bold">Projects</h1>

    <p v-if="loading" class="mt-4 text-gray-500">Loading...</p>
    <p v-else-if="error" class="mt-4 text-red-600">{{ error }}</p>
    <p v-else-if="!projects.length" class="mt-4 text-gray-500">
      No projects yet.
    </p>

    <template v-else>
      <div class="mt-4 flex flex-wrap gap-2">
        <button
          class="rounded-full border px-3 py-1 text-sm"
          :class="!activeSkill ? 'bg-gray-900 text-white' : 'bg-white'"
          @click="activeSkill = null"
        >
          All
        </button>
        <button
          v-for="name in skillNames"
          :key="name"
          class="rounded-full border px-3 py-1 text-sm"
          :class="activeSkill === name ? 'bg-gray-900 text-white' : 'bg-white'"
          @click="activeSkill = name"
        >
          {{ name }}
        </button>
      </div>

      <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <ProjectCard v-for="p in filtered" :key="p.id" :project="p" />
      </div>
    </template>
  </section>
</template>
