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
    class="group block overflow-hidden rounded-lg bg-white shadow transition hover:shadow-md"
  >
    <img
      v-if="project.thumbnail_url"
      :src="mediaUrl(project.thumbnail_url)"
      :alt="project.title"
      loading="lazy"
      class="h-48 w-full object-cover transition group-hover:scale-105"
    />
    <div
      v-else
      class="flex h-44 items-center justify-center bg-gray-200 text-gray-500"
    >
      No image
    </div>
    <div class="p-4">
      <h3 class="font-semibold group-hover:underline">{{ project.title }}</h3>
      <p class="mt-1 text-sm text-gray-600">{{ project.summary }}</p>

      <ul v-if="project.skills?.length" class="mt-3 flex flex-wrap gap-2">
        <li
          v-for="skill in project.skills"
          :key="skill.id"
          class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-700"
        >
          {{ skill.name }}
        </li>
      </ul>
    </div>
  </RouterLink>
</template>
