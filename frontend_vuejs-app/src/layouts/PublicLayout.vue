<script setup>
import { ref, onMounted } from "vue";
import NavBar from "@/components/NavBar.vue";
import { publicApi } from "@/api";

const socialLinks = ref([]);
const profileName = ref("");

onMounted(async () => {
  try {
    const { data } = await publicApi.profile();
    socialLinks.value = data.social_links;
    profileName.value = data.profile?.full_name ?? "";
  } catch {
    // footer links are optional, inore errors
  }
});
</script>

<template>
  <div class="flex min-h-screen flex-col bg-gray-50 text-gray-900">
    <NavBar />
    <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-8">
      <RouterView />
    </main>
    <footer
      class="border-t border-gray-200 py-6 text-center text-sm text-gray-500"
    >
      <ul v-if="socialLinks.length" class="mb-2 flex justify-center gap-4">
        <li v-for="link in socialLinks" :key="link.id">
          <a
            :href="link.url"
            target="_blank"
            rel="noopener"
            class="hover:text-black"
          >
            {{ link.platform }}
          </a>
        </li>
      </ul>
      © {{ new Date().getFullYear() }} {{ profileName || "Portfolio" }}
    </footer>
  </div>
</template>
