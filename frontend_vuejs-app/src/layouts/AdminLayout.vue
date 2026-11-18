<script setup>
import { ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import ToastList from "@/components/ToastList.vue";

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();

const links = [
  { to: "/admin", label: "Dashboard", exact: true },
  { to: "/admin/projects", label: "Projects" },
  { to: "/admin/skills", label: "Skills" },
  { to: "/admin/experience", label: "Experience" },
  { to: "/admin/education", label: "Education" },
  { to: "/admin/profile", label: "Profile" },
  { to: "/admin/social-links", label: "Social links" },
  { to: "/admin/messages", label: "Messages" },
];

const menuOpen = ref(false);
watch(
  () => route.fullPath,
  () => (menuOpen.value = false),
);

async function handleLogout() {
  await auth.logout();
  router.push({ name: "admin-login" });
}
</script>

<template>
  <div class="flex min-h-screen bg-gray-100">
    <!-- Backdrop (mobile only) -->
    <div
      v-if="menuOpen"
      class="fixed inset-0 z-30 bg-black/40 md:hidden"
      @click="menuOpen = false"
    ></div>

    <!-- Sidebar: slides in on mobile, always visible from md up -->
    <aside
      class="fixed inset-y-0 left-0 z-40 w-60 transform overflow-y-auto bg-gray-900 p-4 text-white transition-transform md:static md:translate-x-0"
      :class="menuOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="mb-6 flex items-center justify-between">
        <p class="text-lg font-semibold">Admin</p>
        <button
          type="button"
          class="rounded p-1 hover:bg-gray-800 md:hidden"
          aria-label="Close menu"
          @click="menuOpen = false"
        >
          ✕
        </button>
      </div>

      <nav class="space-y-1 text-sm">
        <RouterLink
          v-for="l in links"
          :key="l.to"
          :to="l.to"
          class="block rounded px-3 py-2.5 hover:bg-gray-800"
          :exact-active-class="l.exact ? 'bg-gray-800 font-semibold' : ''"
          :active-class="l.exact ? '' : 'bg-gray-800 font-semibold'"
        >
          {{ l.label }}
        </RouterLink>
        <RouterLink
          to="/"
          target="_blank"
          class="block rounded px-3 py-2.5 text-gray-400 hover:bg-gray-800"
        >
          View site ↗
        </RouterLink>
      </nav>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
      <header
        class="flex items-center justify-between bg-white px-4 py-3 shadow-sm sm:px-6"
      >
        <div class="flex items-center gap-3">
          <button
            type="button"
            class="rounded p-2 hover:bg-gray-100 md:hidden"
            aria-label="Open menu"
            @click="menuOpen = true"
          >
            <svg
              class="h-6 w-6"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <span class="hidden text-sm text-gray-500 sm:block">
            Signed in as {{ auth.user?.name }}
          </span>
        </div>
        <button
          type="button"
          class="rounded bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-700"
          @click="handleLogout"
        >
          Logout
        </button>
      </header>

      <main class="min-w-0 flex-1 p-4 sm:p-6"><RouterView /></main>
    </div>

    <ToastList />
  </div>
</template>
