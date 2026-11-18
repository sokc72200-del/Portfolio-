<script setup>
import { ref, computed, onMounted } from "vue";
import { publicApi } from "@/api";
import { mediaUrl } from "@/utils/media";
import ProjectCard from "@/components/ProjectCard.vue";

const profile = ref(null);
const projects = ref([]);
const skillCount = ref(0);
const loading = ref(true);
const error = ref("");

const bioSnippet = computed(() => {
  const bio = profile.value?.bio?.trim();
  if (!bio) return "";
  return bio.length <= 240
    ? bio
    : bio.slice(0, 240).replace(/\s+\S*$/, "") + "…";
});

onMounted(async () => {
  try {
    const [p, pr, sk] = await Promise.all([
      publicApi.profile(),
      publicApi.projects({ featured: 1 }),
      publicApi.skills(),
    ]);
    profile.value = p.data.profile;
    projects.value = pr.data;
    skillCount.value = Object.values(sk.data).flat().length;
  } catch {
    error.value = "Could not load data. Please try again later.";
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div>
    <p v-if="loading" class="py-10 text-muted">Loading...</p>
    <p v-else-if="error" class="py-10 text-red-400">{{ error }}</p>

    <template v-else>
      <!-- Availability pill -->
      <span
        class="inline-flex items-center gap-2 rounded-full border border-line bg-surface px-4 py-1.5 text-sm text-gray-300"
      >
        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
        Available for new projects
      </span>

      <!-- Hero card -->
      <section
        class="mt-6 max-w-3xl rounded-3xl border border-line bg-surface/80 p-6 sm:p-8"
      >
        <div class="flex gap-1.5" aria-hidden="true">
          <span class="h-3 w-3 rounded-full bg-rose-400"></span>
          <span class="h-3 w-3 rounded-full bg-amber-400"></span>
          <span class="h-3 w-3 rounded-full bg-emerald-400"></span>
        </div>
        <hr class="my-5 border-line" />

        <div
          class="flex flex-col items-center gap-6 text-center sm:flex-row sm:text-left"
        >
          <img
            v-if="profile?.avatar_url"
            :src="mediaUrl(profile.avatar_url)"
            :alt="profile.full_name"
            class="h-28 w-28 shrink-0 rounded-full object-cover p-1 ring-2 ring-brand"
          />
          <div
            v-else
            class="flex h-28 w-28 shrink-0 items-center justify-center rounded-full bg-ink text-3xl font-bold ring-2 ring-brand"
          >
            {{ profile?.full_name?.[0] ?? "?" }}
          </div>

          <div>
            <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">
              {{ profile?.full_name ?? "Your Name" }}
            </h1>
            <p v-if="profile?.title" class="mt-1 font-medium text-accent">
              {{ profile.title }}
            </p>
            <p v-if="profile?.address" class="mt-1 text-sm text-muted">
              📍 {{ profile.address }}
            </p>
          </div>
        </div>

        <div
          v-if="bioSnippet"
          class="mt-6 rounded-2xl border border-line bg-ink/60 p-4"
        >
          <p class="text-xs font-semibold uppercase tracking-wider text-accent">
            About me
          </p>
          <p class="mt-2 text-fg">{{ bioSnippet }}</p>
        </div>

        <!-- Real stats instead of invented numbers -->
        <div class="mt-4 grid grid-cols-2 gap-3">
          <div class="rounded-2xl border border-line bg-ink/60 p-4">
            <p class="text-2xl font-bold">{{ projects.length }}</p>
            <p class="text-sm text-muted">Featured projects</p>
          </div>
          <div class="rounded-2xl border border-line bg-ink/60 p-4">
            <p class="text-2xl font-bold">{{ skillCount }}</p>
            <p class="text-sm text-muted">Skills and tools</p>
          </div>
        </div>

        <div class="mt-6 flex flex-wrap justify-center gap-3 sm:justify-start">
          <RouterLink
            to="/projects"
            class="rounded-full bg-fg px-5 py-2.5 text-sm font-semibold text-ink hover:bg-gray-200"
          >
            View work
          </RouterLink>
          <RouterLink
            to="/contact"
            class="rounded-full border border-line px-5 py-2.5 text-sm hover:border-fg"
          >
            Contact me
          </RouterLink>
          <a
            v-if="profile?.resume_url"
            :href="mediaUrl(profile.resume_url)"
            target="_blank"
            rel="noopener noreferrer"
            class="rounded-full border border-line px-5 py-2.5 text-sm hover:border-fg"
          >
            Download CV
          </a>
        </div>
      </section>

      <!-- Featured work -->
      <section class="mt-16">
        <p class="text-xs font-semibold uppercase tracking-widest text-accent">
          Selected portfolio
        </p>
        <div class="mt-1 flex items-end justify-between gap-4">
          <h2 class="text-3xl font-extrabold tracking-tight">Featured work</h2>
          <RouterLink to="/projects" class="text-sm text-muted hover:text-fg"
            >All projects →</RouterLink
          >
        </div>

        <p v-if="!projects.length" class="mt-6 text-muted">
          No featured projects yet.
        </p>
        <div v-else class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          <ProjectCard v-for="p in projects" :key="p.id" :project="p" />
        </div>
      </section>
    </template>
  </div>
</template>
