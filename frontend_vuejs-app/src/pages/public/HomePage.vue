<script setup>
import { ref, onMounted } from "vue";
import { publicApi } from "@/api/";
import ProjectCard from "@/components/ProjectCard.vue";
import { mediaUrl } from "@/utils/media";

const profile = ref(null);
const projects = ref([]);
const loading = ref(true);
const error = ref("");

onMounted(async () => {
  try {
    const [profileRes, projectsRes] = await Promise.all([
      publicApi.profile(),
      publicApi.projects({ featured: 1 }),
    ]);
    profile.value = profileRes.data.profile;
    projects.value = projectsRes.data;
  } catch {
    error.value = "Failed to load projects. Please try again later.";
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <section class="py-6">
    <p v-if="loading" class="text-gray-500">Loading...</p>
    <p v-else-if="error" class="text-red-600">{{ error }}</p>

    <template v-else>
      <div class="flex flex-col items-start gap-6 sm:flex-row sm:items-center">
        <img
          v-if="profile?.avatar_url"
          :src="mediaUrl(profile.avatar_url)"
          :alt="profile.full_name"
          class="h-28 w-28 rounded-full object-cover"
        />
        <div>
          <h1 class="text-3xl font-bold">
            Hi, I'm {{ profile?.full_name ?? "a developer" }}
          </h1>
          <p class="mt-1 text-lg text-gray-600">{{ profile?.title }}</p>
          <div class="mt-4 flex gap-3">
            <RouterLink
              to="/projects"
              class="rounded bg-gray-900 px-4 py-2 text-sm text-white"
            >
              View projects
            </RouterLink>
            <RouterLink
              to="/contact"
              class="rounded border border-gray-300 px-4 py-2 text-sm"
            >
              Contact me
            </RouterLink>
            <a
              v-if="profile?.resume_url"
              :href="mediaUrl(profile.resume_url)"
              target="_blank"
              rel="noopener"
              class="rounded border border-gray-300 px-4 py-2 text-sm"
            >
              Download CV
            </a>
          </div>
        </div>
      </div>

      <h2 class="mt-12 mb-4 text-xl font-semibold">Featured Projects</h2>
      <p v-if="!projects.length" class="text-gray-500">
        No featured projects yet.
      </p>
      <div v-else class="grid gap-5 sm:grid-cols-2">
        <ProjectCard v-for="p in projects" :key="p.id" :project="p" />
      </div>
    </template>
  </section>
</template>
