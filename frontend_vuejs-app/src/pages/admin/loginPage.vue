<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  email: "",
  password: "",
});
const error = ref("");
const loading = ref(false);

async function submit() {
  error.value = "";
  loading.value = true;
  try {
    await authStore.login(form);
    await router.push({ name: "admin-dashboard" });
  } catch (e) {
    error.value =
      e.response?.data?.errors?.email?.[0] ??
      e.response?.data?.message ??
      "An error occurred";
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
    <form
      class="w-full max-w-sm space-y-4 rounded-lg bg-white p-6 shadow"
      @submit.prevent="submit"
    >
      <h1 class="text-xl font-bold">Admin Login</h1>

      <p v-if="error" class="rounded bg-red-50 p-2 text-sm text-red-700">
        {{ error }}
      </p>

      <div>
        <label class="mb-1 block text-sm" for="email">Email</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          class="w-full rounded border border-gray-300 px-3 py-2"
        />
      </div>

      <div>
        <label class="mb-1 block text-sm" for="password">Password</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          required
          class="w-full rounded border border-gray-300 px-3 py-2"
        />
      </div>

      <button
        :disabled="loading"
        class="w-full rounded bg-gray-900 py-2 text-white disabled:opacity-50"
      >
        {{ loading ? "Signing in..." : "Sign in" }}
      </button>
    </form>
  </div>
</template>
