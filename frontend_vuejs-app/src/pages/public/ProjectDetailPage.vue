<script setup>
import { ref, watch } from "vue";
import { useRoute } from "vue-router";
import { publicApi } from "@/api";
import { mediaUrl } from "@/utils/media";

const route = useRoute();
const project = ref(null);
const loading = ref(true);
const notFound = ref(false);
const error = ref("");

async function load(slug) {
  loading.value = true;
  notFound.value = false;
  error.value = "";
  try {
    const { data } = await publicApi.project(slug);
    project.value = data;
  } catch (e) {
    if (e.response?.status === 404) notFound.value = true;
    else error.value = "Could not load this project. ";
  } finally {
    loading.value = false;
  }
}

watch(() => route.params.slug, load, { immediate: true });
</script>
<template>
  <section>
    <RouterLink to="/projects" class="text-sm text-gray-600 hover:underline">
      &larr; Back to projects
    </RouterLink>

    <p v-if="loading" class="mt-6 text-gray-500">Loading...</p>

    <div v-else-if="notFound" class="mt-6">
      <h1 class="text-2xl font-bold">Project not found</h1>
      <p class="mt-1 text-gray-600">
        It may have been removed or is not published yet.
      </p>
    </div>

    <p v-else-if="error" class="mt-6 text-red-600">{{ error }}</p>

    <article v-else-if="project" class="mt-6">
      <h1 class="text-3xl font-bold">{{ project.title }}</h1>
      <p class="mt-2 text-gray-600">{{ project.summary }}</p>

      <div class="mt-4 flex gap-3">
        <a
          v-if="project.live_url"
          :href="project.live_url"
          target="_blank"
          rel="noopener"
          class="rounded bg-gray-900 px-4 py-2 text-sm text-white"
        >
          Live demo
        </a>
        <a
          v-if="project.repo_url"
          :href="project.repo_url"
          target="_blank"
          rel="noopener"
          class="rounded border border-gray-300 px-4 py-2 text-sm"
        >
          Source code
        </a>
      </div>

      <img
        v-if="project.thumbnail_url"
        :src="mediaUrl(project.thumbnail_url)"
        :alt="project.title"
        class="mt-6 w-full rounded-lg object-cover"
      />

      <ul v-if="project.skills?.length" class="mt-6 flex flex-wrap gap-2">
        <li
          v-for="skill in project.skills"
          :key="skill.id"
          class="rounded-full bg-gray-200 px-3 py-1 text-sm"
        >
          {{ skill.name }}
        </li>
      </ul>

      <!-- whitespace-pre-line keeps line breaks and avoids v-html (XSS risk) -->
      <p
        v-if="project.description"
        class="mt-6 whitespace-pre-line leading-relaxed"
      >
        {{ project.description }}
      </p>

      <div v-if="project.images?.length" class="mt-8">
        <h2 class="mb-3 text-xl font-semibold">Gallery</h2>
        <div class="grid gap-4 sm:grid-cols-2">
          <figure v-for="img in project.images" :key="img.id">
            <img
              :src="mediaUrl(img.image_url)"
              :alt="img.caption || project.title"
              loading="lazy"
              class="w-full rounded-lg object-cover"
            />
            <figcaption v-if="img.caption" class="mt-1 text-sm text-gray-500">
              {{ img.caption }}
            </figcaption>
          </figure>
        </div>
      </div>
    </article>
  </section>
</template>
