<script setup>
import { ref, computed, onMounted } from "vue";
import { publicApi } from "@/api";
import { mediaUrl } from "@/utils/media";
import { formatMonthYear } from "@/utils/format";

const profile = ref(null);
const skills = ref({}); // { frontend: [...], backend: [...] }
const experiences = ref([]);
const educations = ref([]);
const loading = ref(true);
const error = ref("");
const tab = ref("all");

const timeline = computed(() => {
  const work = experiences.value.map((e) => ({
    key: "w" + e.id,
    kind: "work",
    title: e.position,
    sub: e.company_name,
    desc: e.description,
    start: e.start_date,
    end: e.is_current ? null : e.end_date,
    current: !!e.is_current,
  }));
  const edu = educations.value.map((e) => ({
    key: "e" + e.id,
    kind: "education",
    title: e.degree + (e.field ? " in " + e.field : ""),
    sub: e.school,
    desc: "",
    start: e.start_date,
    end: e.end_date,
    current: !e.end_date,
  }));
  return [...work, ...edu]
    .filter((item) => tab.value === "all" || item.kind === tab.value)
    .sort((a, b) => new Date(b.start) - new Date(a.start));
});

onMounted(async () => {
  try {
    const [p, s, ex, ed] = await Promise.all([
      publicApi.profile(),
      publicApi.skills(),
      publicApi.experiences(),
      publicApi.educations(),
    ]);
    profile.value = p.data.profile;
    skills.value = s.data;
    experiences.value = ex.data;
    educations.value = ed.data;
  } catch {
    error.value = "Could not load the page. Please try again later.";
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <section>
    <p v-if="loading" class="text-muted">Loading...</p>
    <p v-else-if="error" class="text-red-400">{{ error }}</p>

    <template v-else>
      <div class="grid gap-10 lg:grid-cols-5">
        <!-- Left: intro -->
        <div class="lg:col-span-2">
          <p
            class="text-xs font-semibold uppercase tracking-widest text-accent"
          >
            Background
          </p>
          <h1 class="mt-1 text-3xl font-extrabold tracking-tight sm:text-4xl">
            About &amp; experience
          </h1>

          <div
            class="mt-6 overflow-hidden rounded-3xl border border-line bg-surface"
          >
            <img
              v-if="profile?.avatar_url"
              :src="mediaUrl(profile.avatar_url)"
              :alt="profile.full_name"
              class="aspect-square w-full object-cover"
            />
            <div class="flex items-center justify-between gap-3 p-4">
              <div class="min-w-0">
                <p class="truncate font-semibold">{{ profile?.full_name }}</p>
                <p v-if="profile?.address" class="truncate text-sm text-muted">
                  {{ profile.address }}
                </p>
              </div>
              <a
                v-if="profile?.resume_url"
                :href="mediaUrl(profile.resume_url)"
                target="_blank"
                rel="noopener noreferrer"
                class="shrink-0 rounded-full bg-tint/10 px-4 py-2 text-sm font-semibold hover:bg-tint/20"
              >
                Download CV
              </a>
            </div>
          </div>

          <p
            v-if="profile?.bio"
            class="mt-6 whitespace-pre-line leading-relaxed text-soft"
          >
            {{ profile.bio }}
          </p>
        </div>

        <!-- Right: timeline -->
        <div class="lg:col-span-3">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <h2
              class="text-sm font-semibold uppercase tracking-widest text-muted"
            >
              Career &amp; education
            </h2>
            <div
              class="flex gap-1 rounded-xl border border-line bg-surface p-1 text-sm"
            >
              <button
                v-for="t in [
                  ['all', 'All'],
                  ['work', 'Work'],
                  ['education', 'Education'],
                ]"
                :key="t[0]"
                type="button"
                class="rounded-lg px-3 py-1.5 transition"
                :class="
                  tab === t[0]
                    ? 'bg-tint/10 font-semibold text-fg'
                    : 'text-muted hover:text-fg'
                "
                @click="tab = t[0]"
              >
                {{ t[1] }}
              </button>
            </div>
          </div>

          <p v-if="!timeline.length" class="mt-6 text-muted">
            Nothing here yet.
          </p>

          <ol v-else class="relative mt-6 space-y-4 border-l border-line pl-6">
            <li v-for="item in timeline" :key="item.key" class="relative">
              <span
                class="absolute left[31px] top-6 h-3 w-3 rounded-full border-2 border-ink"
                :class="item.current ? 'bg-accent' : 'bg-zinc-600'"
              ></span>
              <div
                class="rounded-2xl border bg-surface p-5"
                :class="item.current ? 'border-accent/40' : 'border-line'"
              >
                <div class="flex flex-wrap items-start justify-between gap-2">
                  <h3 class="font-semibold">{{ item.title }}</h3>
                  <span
                    class="rounded-full px-3 py-1 text-xs"
                    :class="
                      item.current
                        ? 'bg-accent/15 text-accent'
                        : 'bg-tint/5 text-muted'
                    "
                  >
                    {{ formatMonthYear(item.start) }} —
                    {{ item.current ? "Present" : formatMonthYear(item.end) }}
                  </span>
                </div>
                <p class="mt-1 text-sm text-muted">{{ item.sub }}</p>
                <p
                  v-if="item.desc"
                  class="mt-3 whitespace-pre-line text-sm text-gray-300"
                >
                  {{ item.desc }}
                </p>
              </div>
            </li>
          </ol>
        </div>
      </div>

      <!-- Skills -->
      <div class="mt-16">
        <p class="text-xs font-semibold uppercase tracking-widest text-accent">
          Toolbox
        </p>
        <h2 class="mt-1 text-3xl font-extrabold tracking-tight">
          Skills &amp; tools
        </h2>

        <p v-if="!Object.keys(skills).length" class="mt-6 text-muted">
          No skills added yet.
        </p>
        <div v-else class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="(list, category) in skills"
            :key="category"
            class="rounded-2xl border border-line bg-surface p-6"
          >
            <h3 class="text-lg font-semibold capitalize">{{ category }}</h3>
            <ul class="mt-4 flex flex-wrap gap-2">
              <li
                v-for="skill in list"
                :key="skill.id"
                class="rounded-lg bg-tint/5 px-3 py-1.5 text-sm text-gray-200"
                :title="skill.level + '%'"
              >
                {{ skill.name }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </template>
  </section>
</template>
