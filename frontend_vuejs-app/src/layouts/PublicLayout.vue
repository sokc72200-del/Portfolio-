<script setup>
import { ref, onMounted } from "vue";
import NavBar from "@/components/NavBar.vue";
import SocialIcon from "@/components/SocialIcon.vue";
import { publicApi } from "@/api";

const socialLinks = ref([]);
const profile = ref(null);

onMounted(async () => {
  try {
    const { data } = await publicApi.profile();
    socialLinks.value = data.social_links ?? [];
    profile.value = data.profile ?? null;
  } catch {
    // footer links are optional, inore errors
  }
});
</script>

<template>
  <div class="public-theme flex min-h-screen flex-col">
    <NavBar :profile="profile" />

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-10">
      <RouterView />
    </main>

    <footer class="border-t border-line py-8">
      <div
        class="mx-auto flex max-w-6xl flex-col items-center gap-4 px-4 text-sm text-muted"
      >
        <ul
          v-if="socialLinks.length"
          class="flex flex-wrap justify-center gap-3"
        >
          <li v-for="link in socialLinks" :key="link.id">
            <a
              :href="link.url"
              target="_blank"
              rel="noopener noreferrer"
              :aria-label="link.platform"
              class="flex h-10 w-10 items-center justify-center rounded-xl border border-line bg-surface text-muted transition hover:border-accent hover:text-accent"
            >
              <SocialIcon :platform="link.platform" />
            </a>
          </li>
        </ul>
        <p>
          © {{ new Date().getFullYear() }}
          {{ profile?.full_name ?? "Portfolio" }}
        </p>
      </div>
    </footer>
  </div>
</template>
