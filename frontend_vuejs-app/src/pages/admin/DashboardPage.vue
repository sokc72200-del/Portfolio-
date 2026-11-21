<script setup>
import { ref, onMounted } from "vue";
import {
  FolderKanban,
  Sparkles,
  Briefcase,
  Mail,
  LayoutDashboard,
  Plus,
  UserRound,
  ExternalLink,
  Inbox,
} from "lucide-vue-next";
import { adminApi } from "@/api";
import PageHeader from "@/components/PageHeader.vue";

const stats = ref({ projects: 0, skills: 0, experiences: 0, unread: 0 });
const loading = ref(true);
const error = ref("");
const latest = ref([]);

function formatDate(d) {
  return new Date(d).toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
  });
}

onMounted(async () => {
  const [projects, skills, experiences, messages] = await Promise.allSettled([
    adminApi.projects({ page: 1 }),
    adminApi.skills(),
    adminApi.experiences(),
    adminApi.messages({ page: 1 }),
  ]);
  if (projects.status === "fulfilled") {
    stats.value.projects = projects.value.data.total ?? 0;
  }
  if (skills.status === "fulfilled") {
    stats.value.skills = skills.value.data.length;
  }
  if (experiences.status === "fulfilled") {
    stats.value.experiences = experiences.value.data.length;
  }
  if (messages.status === "fulfilled") {
    stats.value.unread = messages.value.data.unread_count ?? 0;
    latest.value = (messages.value.data.messages?.data ?? []).slice(0, 5);
  }
  if ([projects, skills, experiences, messages].some((result) => result.status === "rejected")) {
    error.value = "Some dashboard data could not be loaded.";
  }
  loading.value = false;
});

const cards = [
  {
    key: "projects",
    label: "Projects",
    icon: FolderKanban,
    to: "/admin/projects",
    color: "bg-indigo-500/15 text-indigo-400",
  },
  {
    key: "skills",
    label: "Skills",
    icon: Sparkles,
    to: "/admin/skills",
    color: "bg-amber-500/15 text-amber-500",
  },
  {
    key: "experiences",
    label: "Experience",
    icon: Briefcase,
    to: "/admin/experience",
    color: "bg-emerald-500/15 text-emerald-500",
  },
  {
    key: "unread",
    label: "Unread messages",
    icon: Mail,
    to: "/admin/messages",
    color: "bg-rose-500/15 text-rose-500",
  },
];

const actions = [
  { label: "New project", icon: Plus, to: "/admin/projects/new" },
  { label: "Add skill", icon: Sparkles, to: "/admin/skills" },
  { label: "Edit profile", icon: UserRound, to: "/admin/profile" },
  { label: "View site", icon: ExternalLink, to: "/", external: true },
];
</script>
<template>
  <PageHeader
    title="Dashboard"
    subtitle="Overview of your portfolio"
    :icon="LayoutDashboard"
  />

  <p v-if="loading" class="text-muted">Loading...</p>
  <p v-if="error" role="alert" class="mb-4 text-sm text-danger">{{ error }}</p>

  <template v-if="!loading">
    <!-- Stats -->
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <RouterLink
        v-for="c in cards"
        :key="c.key"
        :to="c.to"
        class="group rounded-2xl border border-line bg-surface p-5 transition hover:-translate-y-0.5 hover:border-brand/50"
      >
        <span
          class="flex h-11 w-11 items-center justify-center rounded-xl"
          :class="c.color"
        >
          <component :is="c.icon" class="h-5 w-5" aria-hidden="true" />
        </span>
        <p class="mt-4 text-3xl font-bold">{{ stats[c.key] }}</p>
        <p class="text-sm text-muted">{{ c.label }}</p>
      </RouterLink>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
      <!-- Latest messages -->
      <section class="rounded-2xl border border-line bg-surface lg:col-span-2">
        <div class="flex items-center justify-between border-b border-line p-5">
          <h2 class="flex items-center gap-2 font-semibold">
            <Inbox class="h-5 w-5 text-brand" aria-hidden="true" /> Latest
            messages
          </h2>
          <RouterLink
            to="/admin/messages"
            class="text-sm text-accent hover:underline"
            >View all</RouterLink
          >
        </div>

        <p v-if="!latest.length" class="p-5 text-sm text-muted">
          No messages yet.
        </p>
        <ul v-else class="divide-y divide-line">
          <li v-for="m in latest" :key="m.id">
            <RouterLink
              to="/admin/messages"
              class="flex items-start gap-3 p-4 hover:bg-tint/5"
            >
              <span
                class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full"
                :class="m.is_read ? 'bg-transparent' : 'bg-brand'"
                :aria-label="m.is_read ? 'Read' : 'Unread'"
              ></span>
              <span class="min-w-0 flex-1">
                <span class="flex items-center justify-between gap-2">
                  <span
                    class="truncate"
                    :class="m.is_read ? 'text-muted' : 'font-semibold'"
                    >{{ m.name }}</span
                  >
                  <span class="shrink-0 text-xs text-muted">{{
                    formatDate(m.created_at)
                  }}</span>
                </span>
                <span class="block truncate text-sm text-muted">{{
                  m.subject || m.body
                }}</span>
              </span>
            </RouterLink>
          </li>
        </ul>
      </section>

      <!-- Quick actions -->
      <section class="rounded-2xl border border-line bg-surface p-5">
        <h2 class="font-semibold">Quick actions</h2>
        <div class="mt-4 grid gap-2">
          <component
            :is="a.external ? 'a' : 'RouterLink'"
            v-for="a in actions"
            :key="a.label"
            :to="a.external ? undefined : a.to"
            :href="a.external ? a.to : undefined"
            :target="a.external ? '_blank' : undefined"
            class="flex items-center gap-3 rounded-xl border border-line px-4 py-3 text-sm transition hover:border-brand hover:bg-brand/10"
          >
            <component
              :is="a.icon"
              class="h-5 w-5 text-brand"
              aria-hidden="true"
            />
            {{ a.label }}
          </component>
        </div>
      </section>
    </div>
  </template>
</template>
