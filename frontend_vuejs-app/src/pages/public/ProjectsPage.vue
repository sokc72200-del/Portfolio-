<script setup>
import { ref, computed, onMounted } from "vue";
import { publicApi } from "@/api";
import ProjectCard from "@/components/ProjectCard.vue";

const projects = ref([]);
const loading = ref(true);
const error = ref("");
const active = ref(null);

const skillNames = computed(() => {
  const names = new Set();
  projects.value.forEach((p) => p.skills?.forEach((s) => names.add(s.name)));
  return [...names].sort();
});

const filtered = computed(() =>
  active.value
    ? projects.value.filter((p) =>
        p.skills?.some((s) => s.name === active.value),
      )
    : projects.value,
);

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
    <p class="text-xs font-semibold uppercase tracking-widest text-accent">
      Selected portfolio
    </p>
    <h1 class="mt-1 text-3xl font-extrabold tracking-tight sm:text-4xl">
      Featured work
    </h1>

    <p v-if="loading" class="mt-8 text-muted">Loading...</p>
    <p v-else-if="error" class="mt-8 text-red-400">{{ error }}</p>
    <p v-else-if="!projects.length" class="mt-8 text-muted">No projects yet.</p>

    <template v-else>
      <div
        class="mt-6 flex flex-wrap gap-1 rounded-2xl border border-line bg-surface p-1 sm:inline-flex"
      >
        <button
          type="button"
          class="rounded-xl px-4 py-2 text-sm transition"
          :class="
            !active
              ? 'bg-white/10 font-semibold text-white'
              : 'text-muted hover:text-white'
          "
          @click="active = null"
        >
          All
        </button>
        <button
          v-for="name in skillNames"
          :key="name"
          type="button"
          class="rounded-xl px-4 py-2 text-sm transition"
          :class="
            active === name
              ? 'bg-white/10 font-semibold text-white'
              : 'text-muted hover:text-white'
          "
          @click="active = name"
        >
          {{ name }}
        </button>
      </div>

      <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <ProjectCard v-for="p in filtered" :key="p.id" :project="p" />
      </div>
    </template>
  </section>
</template>
