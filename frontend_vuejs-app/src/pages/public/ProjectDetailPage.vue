<script setup>
import { ref, computed, watch } from "vue";
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
    else error.value = "Could not load this project.";
  } finally {
    loading.value = false;
  }
}

watch(() => route.params.slug, load, { immediate: true });

// ---- Description parser -------------------------------------------------
// Turns plain text into blocks: { type: 'heading' | 'list' | 'p', ... }
const HEADING_RE = /^[A-Z][A-Z0-9 &/,'-]{3,40}:?$/;
const BULLET_RE = /^[-*]\s+(.*)$/;

function parseDescription(text) {
  const blocks = [];
  let paragraph = [];
  let list = null;

  const flushParagraph = () => {
    if (paragraph.length) {
      blocks.push({ type: "p", text: paragraph.join("\n") });
      paragraph = [];
    }
  };
  const flushList = () => {
    if (list) {
      blocks.push({ type: "list", items: list });
      list = null;
    }
  };

  for (const raw of text.split(/\r?\n/)) {
    const line = raw.trim();

    if (!line) {
      flushParagraph();
      flushList();
      continue;
    }

    const bullet = line.match(BULLET_RE);
    if (bullet) {
      flushParagraph();
      (list ??= []).push(bullet[1]);
      continue;
    }

    if (HEADING_RE.test(line)) {
      flushParagraph();
      flushList();
      blocks.push({ type: "heading", text: line.replace(/:$/, "") });
      continue;
    }

    flushList();
    paragraph.push(line);
  }

  flushParagraph();
  flushList();
  return blocks;
}

const blocks = computed(() =>
  project.value?.description ? parseDescription(project.value.description) : [],
);
</script>

<template>
  <section class="mx-auto max-w-4xl">
    <RouterLink to="/projects" class="text-sm text-muted hover:text-white">
      &larr; Back to work
    </RouterLink>

    <p v-if="loading" class="mt-8 text-muted">Loading...</p>

    <div
      v-else-if="notFound"
      class="mt-8 rounded-2xl border border-line bg-surface p-8"
    >
      <h1 class="text-2xl font-bold">Project not found</h1>
      <p class="mt-2 text-muted">
        It may have been removed or is not published yet.
      </p>
      <RouterLink
        to="/projects"
        class="mt-4 inline-block text-accent hover:underline"
      >
        See all projects
      </RouterLink>
    </div>

    <p v-else-if="error" class="mt-8 text-red-400">{{ error }}</p>

    <article v-else-if="project" class="mt-6">
      <p class="text-xs font-semibold uppercase tracking-widest text-accent">
        Case study
      </p>
      <h1 class="mt-1 text-3xl font-extrabold tracking-tight sm:text-4xl">
        {{ project.title }}
      </h1>
      <p class="mt-3 max-w-2xl text-lg text-muted">{{ project.summary }}</p>

      <div class="mt-6 flex flex-wrap gap-3">
        <a
          v-if="project.repo_url"
          :href="project.repo_url"
          target="_blank"
          rel="noopener noreferrer"
          class="rounded-full border border-line px-5 py-2.5 text-sm hover:border-white"
        >
          Source code ↗
        </a>
      </div>

      <img
        v-if="project.thumbnail_url"
        :src="mediaUrl(project.thumbnail_url)"
        :alt="project.title"
        class="mt-8 w-full rounded-3xl border border-line object-cover"
      />

      <ul v-if="project.skills?.length" class="mt-6 flex flex-wrap gap-2">
        <li
          v-for="skill in project.skills"
          :key="skill.id"
          class="rounded-lg bg-white/5 px-3 py-1.5 text-sm text-gray-200"
        >
          {{ skill.name }}
        </li>
      </ul>

      <!-- Parsed description: plain text bindings only, no v-html -->
      <div
        v-if="blocks.length"
        class="mt-10 space-y-3 rounded-3xl border border-line bg-surface/80 p-6 leading-relaxed text-gray-300 sm:p-8"
      >
        <template v-for="(block, i) in blocks" :key="i">
          <h2
            v-if="block.type === 'heading'"
            class="mt-8 border-b border-line pb-2 text-xs font-semibold uppercase tracking-widest text-accent first:mt-0"
          >
            {{ block.text }}
          </h2>

          <ul
            v-else-if="block.type === 'list'"
            class="list-disc space-y-1.5 pl-6 marker:text-accent"
          >
            <li v-for="(item, j) in block.items" :key="j">{{ item }}</li>
          </ul>

          <p v-else class="whitespace-pre-line">{{ block.text }}</p>
        </template>
      </div>

      <div v-if="project.images?.length" class="mt-12">
        <p class="text-xs font-semibold uppercase tracking-widest text-accent">
          Screenshots
        </p>
        <h2 class="mt-1 text-2xl font-extrabold tracking-tight">Gallery</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
          <figure v-for="img in project.images" :key="img.id">
            <img
              :src="mediaUrl(img.image_url)"
              :alt="img.caption || project.title"
              loading="lazy"
              class="w-full rounded-2xl border border-line object-cover"
            />
            <figcaption v-if="img.caption" class="mt-2 text-sm text-muted">
              {{ img.caption }}
            </figcaption>
          </figure>
        </div>
      </div>
    </article>
  </section>
</template>
