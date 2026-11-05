<script setup>
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import ToastList from "@/components/ToastList.vue";

const router = useRouter();
const authStore = useAuthStore();
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

async function handleLogout() {
  await authStore.logout();
  await router.push({ name: "admin-login" });
}
</script>

<template>
  <div class="flex min-h-screen bg-gray-100">
    <aside class="hidden w-56 shrink-0 bg-gray-900 p-4 text-white md:block">
      <p class="mb-6 text-lg font-semibold">Admin</p>
      <nav class="space-y-1 text-sm">
        <RouterLink
          v-for="l in links"
          :key="l.to"
          :to="l.to"
          class="block rounded px-3 py-2 hover:bg-gray-800"
          :exact-active-class="l.exact ? 'bg-gray-800 font-semibold' : ''"
          :active-class="l.exact ? '' : 'bg-gray-800 font-semibold'"
        >
          {{ l.label }}
        </RouterLink>
        <RouterLink
          to="/"
          target="_blank"
          class="block rounded px-3 py-2 text-gray-400 hover:bg-gray-800"
        >
          View site ↗
        </RouterLink>
      </nav>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
      <header
        class="flex items-center justify-between bg-white px-6 py-3 shadow-sm"
      >
        <nav class="flex flex-wrap gap-3 text-sm md:hidden">
          <RouterLink v-for="l in links" :key="l.to" :to="l.to">{{
            l.label
          }}</RouterLink>
        </nav>
        <span class="hidden text-sm text-gray-500 md:block"
          >Signed in as {{ authStore.user?.name }}</span
        >
        <button
          class="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700"
          @click="handleLogout"
        >
          Logout
        </button>
      </header>
      <main class="flex-1 p-6"><RouterView /></main>
    </div>

    <ToastList />
  </div>
</template>
