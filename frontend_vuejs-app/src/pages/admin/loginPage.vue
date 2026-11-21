<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { Lock, Mail, LogIn } from "lucide-vue-next";
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
  <div class="admin-theme flex min-h-screen items-center justify-center px-4">
    <form
      class="w-full max-w-sm space-y-5 rounded-3xl border border-line bg-surface p-8 shadow-xl"
      @submit.prevent="submit"
    >
      <div class="text-center">
        <span
          class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-brand text-white"
        >
          <Lock class="h-6 w-6" aria-hidden="true" />
        </span>
        <h1 class="mt-4 text-2xl font-bold">Admin login</h1>
        <p class="text-sm text-muted">Sign in to manage your portfolio</p>
      </div>

      <p
        v-if="error"
        role="alert"
        class="rounded-xl bg-danger/15 p-3 text-sm text-danger"
      >
        {{ error }}
      </p>

      <div>
        <label for="email" class="mb-1.5 block text-sm text-muted">Email</label>
        <div class="relative">
          <Mail
            class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-muted"
            aria-hidden="true"
          />
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            autocomplete="username"
            class="w-full rounded-xl border px-3 py-3 pl-10"
          />
        </div>
      </div>

      <div>
        <label for="password" class="mb-1.5 block text-sm text-muted"
          >Password</label
        >
        <div class="relative">
          <Lock
            class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-muted"
            aria-hidden="true"
          />
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            autocomplete="current-password"
            class="w-full rounded-xl border px-3 py-3 pl-10"
          />
        </div>
      </div>

      <button
        :disabled="loading"
        class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand py-3 font-semibold text-white hover:opacity-90 disabled:opacity-50"
      >
        <LogIn class="h-5 w-5" aria-hidden="true" />
        {{ loading ? "Signing in..." : "Sign in" }}
      </button>
    </form>
  </div>
</template>
