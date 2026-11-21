<script setup>
import { onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
  LayoutDashboard,
  FolderKanban,
  Sparkles,
  Briefcase,
  GraduationCap,
  UserRound,
  Link2,
  Mail,
  Sun,
  Moon,
  LogOut,
  Menu,
  X,
  ExternalLink,
} from "lucide-vue-next";
import { useAuthStore } from "@/stores/auth";
import ToastList from "@/components/ToastList.vue";
import { adminApi } from "@/api";
import { useTheme } from "@/composables/useTheme";

const auth = useAuthStore();
 const { theme, toggle } = useTheme();
const route = useRoute();
const router = useRouter();

const groups = [
  {
    title: "Overview",
    items: [
      { to: "/admin", label: "Dashboard", icon: LayoutDashboard, exact: true },
    ],
  },
  {
    title: "Content",
    items: [
      { to: "/admin/projects", label: "Projects", icon: FolderKanban },
      { to: "/admin/skills", label: "Skills", icon: Sparkles },
      { to: "/admin/experience", label: "Experience", icon: Briefcase },
      { to: "/admin/education", label: "Education", icon: GraduationCap },
    ],
  },
  {
    title: "Site",
    items: [
      { to: "/admin/profile", label: "Profile", icon: UserRound },
      { to: "/admin/social-links", label: "Social links", icon: Link2 },
      { to: "/admin/messages", label: "Messages", icon: Mail, badge: true },
    ],
  },
];
const menuOpen = ref(false);
const unread = ref(0);

async function loadUnread() {
  try {
    const { data } = await adminApi.messages({ unread: 1, page: 1 });
    unread.value = data.unread_count ?? 0;
  } catch {
    // badge is optional
  }
}

onMounted(loadUnread);
watch(
  () => route.fullPath,
  () => {
    menuOpen.value = false;
    loadUnread();
  },
);

async function handleLogout() {
  await auth.logout();
  router.push({ name: "admin-login" });
}
</script>

<template>
  <div class="admin-theme flex min-h-screen">
    <!-- Backdrop (mobile) -->
    <div
      v-if="menuOpen"
      class="fixed inset-0 z-30 bg-black/50 md:hidden"
      @click="menuOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-40 flex w-64 transform flex-col overflow-y-auto border-r border-line bg-surface p-4 transition-transform md:static md:translate-x-0"
      :class="menuOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="mb-6 flex items-center justify-between">
        <RouterLink
          to="/admin"
          class="flex items-center gap-2 text-lg font-bold"
        >
          <span
            class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand text-white"
          >
            <LayoutDashboard class="h-5 w-5" aria-hidden="true" />
          </span>
          Admin
        </RouterLink>
        <button
          type="button"
          class="rounded-lg p-1.5 hover:bg-tint/10 md:hidden"
          aria-label="Close menu"
          @click="menuOpen = false"
        >
          <X class="h-5 w-5" />
        </button>
      </div>

      <nav class="flex-1 space-y-5 text-sm">
        <div v-for="g in groups" :key="g.title">
          <p
            class="mb-1.5 px-3 text-xs font-semibold uppercase tracking-wider text-muted"
          >
            {{ g.title }}
          </p>
          <RouterLink
            v-for="l in g.items"
            :key="l.to"
            :to="l.to"
            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-muted transition hover:bg-tint/5 hover:text-fg"
            :exact-active-class="
              l.exact ? '!bg-brand/15 !text-fg font-semibold' : ''
            "
            :active-class="l.exact ? '' : '!bg-brand/15 !text-fg font-semibold'"
          >
            <component
              :is="l.icon"
              class="h-5 w-5 shrink-0"
              aria-hidden="true"
            />
            <span class="flex-1">{{ l.label }}</span>
            <span
              v-if="l.badge && unread"
              class="rounded-full bg-danger px-2 py-0.5 text-xs font-semibold text-white"
              :aria-label="`${unread} unread`"
            >
              {{ unread }}
            </span>
          </RouterLink>
        </div>
      </nav>

      <RouterLink
        to="/"
        target="_blank"
        class="mt-4 flex items-center gap-3 rounded-xl border border-line px-3 py-2.5 text-sm text-muted hover:text-fg"
      >
        <ExternalLink class="h-5 w-5" aria-hidden="true" />
        View site
      </RouterLink>
    </aside>

    <!-- Main -->
    <div class="flex min-w-0 flex-1 flex-col">
      <header
        class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-line bg-ink/80 px-4 py-3 backdrop-blur sm:px-6"
      >
        <div class="flex items-center gap-3">
          <button
            type="button"
            class="rounded-lg border border-line p-2 md:hidden"
            aria-label="Open menu"
            @click="menuOpen = true"
          >
            <Menu class="h-5 w-5" />
          </button>
          <span class="hidden items-center gap-2 text-sm text-muted sm:flex">
            <span
              class="flex h-8 w-8 items-center justify-center rounded-full bg-brand/15 text-sm font-bold text-brand"
            >
              {{ auth.user?.name?.[0] ?? "A" }}
            </span>
            {{ auth.user?.name }}
          </span>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-line bg-surface text-accent hover:border-accent"
            :aria-label="
              theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'
            "
            @click="toggle"
          >
            <Sun v-if="theme === 'dark'" class="h-5 w-5" />
            <Moon v-else class="h-5 w-5" />
          </button>
          <button
            type="button"
            class="flex items-center gap-2 rounded-xl bg-danger px-3 py-2 text-sm font-semibold text-white hover:opacity-90"
            @click="handleLogout"
          >
            <LogOut class="h-4 w-4" aria-hidden="true" />
            <span class="hidden sm:inline">Logout</span>
          </button>
        </div>
      </header>

      <main class="min-w-0 flex-1 p-4 sm:p-6"><RouterView /></main>
    </div>

    <ToastList />
  </div>
</template>
