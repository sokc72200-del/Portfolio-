<script setup>
const links = [
  { to: "/", text: "Home" },
  { to: "/about", text: "About" },
  { to: "/projects", text: "Projects" },
  { to: "/contact", text: "Contact" },
];
</script>

<template>
  <header
    class="sticky top-0 z-10 border-b border-gray-200 bg-white/90 backdrop-blur"
  >
    <nav class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
      <RouterLink to="/" class="text-lg font-bold">Portfolio</RouterLink>
      <ul class="flex gap-5 text-sm">
        <li v-for="link in links" :key="link.to">
          <RouterLink
            :to="link.to"
            class="text-gray-600 hover:text-black"
            active-class="font-semibold text-black"
            exact-active-class="font-semibold text-black"
          >
            {{ link.text }}
          </RouterLink>
        </li>
      </ul>
    </nav>
  </header>
</template>
