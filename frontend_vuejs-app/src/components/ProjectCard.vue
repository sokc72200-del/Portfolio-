<script setup>
import { mediaUrl } from "@/utils/media";

defineProps({
  project: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <RouterLink
    :to="{ name: 'project-detail', params: { slug: project.slug } }"
    class="group flex flex-col overflow-hidden rounded-2xl border border-line bg-surface transition hover:-translate-y-1 hover:border-accent/50"
  >
    <img
      v-if="project.thumbnail_url"
      :src="mediaUrl(project.thumbnail_url)"
      :alt="project.title"
      loading="lazy"
      class="h-48 w-full object-cover"
    />
    <div
      v-else
      class="h-48 w-full bg-gradient-to-br from-brand/40 to-accent/30"
    ></div>

    <div class="flex flex-1 flex-col p-5">
      <div class="flex items-start justify-between gap-3">
        <h3 class="text-lg font-semibold">{{ project.title }}</h3>
        <span
          class="text-muted transition group-hover:text-accent"
          aria-hidden="true"
          >↗</span
        >
      </div>
      <p class="mt-2 line-clamp-3 text-sm text-muted">{{ project.summary }}</p>

      <ul
        v-if="project.skills?.length"
        class="mt-auto flex flex-wrap gap-2 pt-4"
      >
        <li
          v-for="skill in project.skills"
          :key="skill.id"
          class="rounded-lg bg-white/5 px-2.5 py-1 text-xs text-gray-300"
        >
          {{ skill.name }}
        </li>
      </ul>
    </div>
  </RouterLink>
</template>
