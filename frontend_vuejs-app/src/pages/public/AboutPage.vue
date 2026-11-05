<script setup>
import { ref, onMounted } from "vue";
import { publicApi } from "@/api";
import { formatMounthYear } from "@/utils/format";

const profile = ref(null);
const skills = ref({});
const experiences = ref([]);
const educations = ref([]);
const loading = ref(true);
const error = ref("");

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
    error.value = "Could not load the Page. ";
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <section>
    <h1 class="text-2xl font-bold">About</h1>

    <p v-if="loading" class="mt-4 text-gray-500">Loading...</p>
    <p v-else-if="error" class="mt-4 text-red-600">{{ error }}</p>

    <template v-else>
      <p v-if="profile?.bio" class="mt-4 whitespace-pre-line leading-relaxed">
        {{ profile.bio }}
      </p>
      <p v-if="profile?.location" class="mt-2 text-sm text-gray-500">
        📍 {{ profile.location }}
      </p>

      <!-- Skills -->
      <h2 class="mt-10 mb-4 text-xl font-semibold">Skills</h2>
      <p v-if="!Object.keys(skills).length" class="text-gray-500">
        No skills added yet.
      </p>
      <div v-else class="grid gap-6 sm:grid-cols-2">
        <div
          v-for="(list, category) in skills"
          :key="category"
          class="rounded-lg bg-white p-4 shadow"
        >
          <h3 class="mb-3 font-medium capitalize">{{ category }}</h3>
          <ul class="space-y-3">
            <li v-for="skill in list" :key="skill.id">
              <div class="flex justify-between text-sm">
                <span>{{ skill.name }}</span>
                <span class="text-gray-500">{{ skill.level }}%</span>
              </div>
              <div class="mt-1 h-2 rounded bg-gray-200">
                <div
                  class="h-2 rounded bg-gray-900"
                  :style="{ width: skill.level + '%' }"
                ></div>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <!-- Experience -->
      <h2 class="mt-10 mb-4 text-xl font-semibold">Experience</h2>
      <p v-if="!experiences.length" class="text-gray-500">
        No experience added yet.
      </p>
      <ol v-else class="space-y-5 border-l-2 border-gray-200 pl-5">
        <li v-for="e in experiences" :key="e.id">
          <h3 class="font-medium">{{ e.position }} · {{ e.company }}</h3>
          <p class="text-sm text-gray-500">
            {{ formatMonthYear(e.start_date) }} -
            {{ e.is_current ? "Present" : formatMonthYear(e.end_date) }}
          </p>
          <p
            v-if="e.description"
            class="mt-1 whitespace-pre-line text-sm text-gray-700"
          >
            {{ e.description }}
          </p>
        </li>
      </ol>

      <!-- Education -->
      <h2 class="mt-10 mb-4 text-xl font-semibold">Education</h2>
      <p v-if="!educations.length" class="text-gray-500">
        No education added yet.
      </p>
      <ol v-else class="space-y-5 border-l-2 border-gray-200 pl-5">
        <li v-for="ed in educations" :key="ed.id">
          <h3 class="font-medium">
            {{ ed.degree }}<span v-if="ed.field"> in {{ ed.field }}</span>
          </h3>
          <p class="text-sm text-gray-700">{{ ed.school }}</p>
          <p class="text-sm text-gray-500">
            {{ formatMonthYear(ed.start_date) }} -
            {{ ed.end_date ? formatMonthYear(ed.end_date) : "Present" }}
          </p>
        </li>
      </ol>
    </template>
  </section> 
</template>
