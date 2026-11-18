<script setup>
import { ref, watch } from "vue";
import { useRoute } from "vue-router";
import { mediaUrl } from "@/utils/media";
import { useTheme } from "@/composables/useTheme";

defineProps({
  brand: { type: String, default: "Portfolio" },
  profile: { type: Object, default: null },
});

const route = useRoute();
const open = ref(false);
const { theme, toggle } = useTheme();

const links = [
  { to: "/", label: "Home" },
  { to: "/about", label: "About" },
  { to: "/projects", label: "Projects" },
  { to: "/contact", label: "Contact" },
];

// close the menu after navigating
watch(
  () => route.fullPath,
  () => (open.value = false),
);
</script>

<template>
  <header
    class="sticky top-0 z-20 border-b border-line bg-ink/80 backdrop-blur"
  >
    <nav
      class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3"
    >
      <!-- Brand -->
      <RouterLink to="/" class="flex min-w-0 items-center gap-3">
        <span class="relative shrink-0">
          <img
            v-if="profile?.avatar_url"
            :src="mediaUrl(profile.avatar_url)"
            :alt="profile.full_name"
            class="h-10 w-10 rounded-full object-cover ring-2 ring-line"
          />
          <span
            v-else
            class="flex h-10 w-10 items-center justify-center rounded-full bg-surface text-sm font-bold ring-2 ring-line"
          >
            {{ profile?.full_name?.[0] ?? "P" }}
          </span>
          <span
            class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-ink bg-emerald-500"
          ></span>
        </span>
        <span class="min-w-0 leading-tight">
          <span class="block truncate font-semibold">
            {{ profile?.full_name ?? "Portfolio"
            }}<span class="text-accent">.</span>
          </span>
          <span
            v-if="profile?.title"
            class="block truncate text-xs text-muted"
            >{{ profile.title }}</span
          >
        </span>
      </RouterLink>

      <!-- Desktop pill links -->
      <ul
        class="hidden gap-1 rounded-full border border-line bg-surface p-1 text-sm md:flex"
      >
        <li v-for="l in links" :key="l.to">
          <RouterLink
            :to="l.to"
            class="block rounded-full px-4 py-1.5 text-muted transition hover:text-fg"
            exact-active-class="!bg-tint/10 !text-fg"
          >
            {{ l.label }}
          </RouterLink>
        </li>
      </ul>

      <div class="flex items-center gap-2">
        <!-- Theme toggle -->
        <button
          type="button"
          class="flex h-10 w-10 items-center justify-center rounded-xl border border-line bg-surface text-accent hover:border-accent"
          :aria-label="
            theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'
          "
          :title="theme === 'dark' ? 'Light mode' : 'Dark mode'"
          @click="toggle"
        >
          <!-- sun (shown in dark mode) -->
          <svg
            v-if="theme === 'dark'"
            class="h-5 w-5"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
            aria-hidden="true"
          >
            <circle cx="12" cy="12" r="4" />
            <path
              stroke-linecap="round"
              d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"
            />
          </svg>
          <!-- moon (shown in light mode) -->
          <svg
            v-else
            class="h-5 w-5"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
            aria-hidden="true"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"
            />
          </svg>
        </button>
        <RouterLink
          to="/contact"
          class="hidden rounded-full bg-fg px-4 py-2 text-sm font-semibold text-ink hover:bg-gray-200 sm:block"
        >
          Hire me
        </RouterLink>

        <button
          type="button"
          class="rounded-lg border border-line p-2 md:hidden"
          :aria-expanded="open"
          aria-label="Toggle menu"
          @click="open = !open"
        >
          <svg
            class="h-5 w-5"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path
              v-if="!open"
              stroke-linecap="round"
              d="M4 6h16M4 12h16M4 18h16"
            />
            <path v-else stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
          </svg>
        </button>
      </div>
    </nav>

    <ul
      v-if="open"
      class="space-y-1 border-t border-line bg-ink px-4 py-3 md:hidden"
    >
      <li v-for="l in links" :key="l.to">
        <RouterLink
          :to="l.to"
          class="block rounded-lg px-3 py-3 text-muted"
          exact-active-class="!bg-tint/10 !text-fg"
        >
          {{ l.label }}
        </RouterLink>
      </li>
      <li>
        <RouterLink
          to="/contact"
          class="mt-2 block rounded-lg bg-fg px-3 py-3 text-center font-semibold text-ink"
        >
          Hire me
        </RouterLink>
      </li>
    </ul>
  </header>
</template>
