<script setup>
import { onMounted, reactive, ref } from "vue";
import { publicApi } from "@/api";
import SocialIcon from "@/components/SocialIcon.vue";

const empty = () => ({
  name: "",
  email: "",
  subject: "",
  body: "",
  website: "",
});
const form = reactive(empty());

const profile = ref(null);
const socialLinks = ref([]);
const errors = ref({});
const status = ref("");
const message = ref("");
const loading = ref(false);
const copied = ref(false);
const input =
  "w-full rounded-xl border border-line bg-ink px-3 py-2.5 text-white outline-none transition placeholder:text-muted/70 focus:border-accent";

onMounted(async () => {
  try {
    const { data } = await publicApi.profile();
    profile.value = data.profile;
    socialLinks.value = data.social_links ?? [];
  } catch {
    // the form still works without profile data
  }
});

async function copyEmail() {
  try {
    await navigator.clipboard.writeText(profile.value.email);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
  } catch {
    // clipboard blocked: ignore
  }
}

async function submit() {
  loading.value = true;
  errors.value = {};
  status.value = "";

  try {
    await publicApi.contact(form);
    status.value = "success";
    message.value = "Thanks! You message has been sent.";
    Object.assign(form, empty());
  } catch (e) {
    status.value = "error";
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {};
      message.value = "Please fix the highlighted fields.";
    } else if (e.response?.status === 429) {
      message.value = "Too many attempts. Please wait a minute and try again.";
    } else {
      message.value = "Something went wrong. Please try again later.";
    }
  } finally {
    loading.value = false;
  }
}
</script>
<template>
  <section class="rounded-3xl border border-line bg-surface/80 p-6 sm:p-10">
    <div class="grid gap-10 lg:grid-cols-2">
      <!-- Left -->
      <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-accent">
          Let's collaborate
        </p>
        <h1
          class="mt-2 text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl"
        >
          Have an idea worth building?
        </h1>
        <p class="mt-4 text-muted">
          Tell me about your project or opportunity and I'll get back to you as
          soon as I can.
        </p>

        <div
          v-if="profile?.email"
          class="mt-8 flex items-center justify-between gap-3 rounded-2xl border border-line bg-ink p-3"
        >
          <span class="truncate pl-2">{{ profile.email }}</span>
          <button
            type="button"
            class="shrink-0 rounded-lg border border-line px-3 py-1.5 text-sm hover:border-white"
            @click="copyEmail"
          >
            {{ copied ? "Copied" : "Copy" }}
          </button>
        </div>

        <ul v-if="socialLinks.length" class="mt-5 flex flex-wrap gap-3">
          <li v-for="l in socialLinks" :key="l.id">
            <a
              :href="l.url"
              target="_blank"
              rel="noopener noreferrer"
              :aria-label="l.platform"
              class="flex h-12 w-12 items-center justify-center rounded-xl border border-line bg-ink text-muted transition hover:border-accent hover:text-accent"
            >
              <SocialIcon :platform="l.platform" />
            </a>
          </li>
        </ul>
      </div>

      <!-- Form -->
      <form novalidate @submit.prevent="submit">
        <p
          v-if="status"
          role="status"
          class="mb-4 rounded-xl p-3 text-sm"
          :class="
            status === 'success'
              ? 'bg-emerald-500/15 text-emerald-300'
              : 'bg-red-500/15 text-red-300'
          "
        >
          {{ message }}
        </p>

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label
              for="name"
              class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-muted"
              >Your name</label
            >
            <input
              id="name"
              v-model="form.name"
              maxlength="100"
              placeholder="Your name"
              :class="input"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-400">
              {{ errors.name[0] }}
            </p>
          </div>
          <div>
            <label
              for="email"
              class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-muted"
              >Email address</label
            >
            <input
              id="email"
              v-model="form.email"
              type="email"
              maxlength="150"
              placeholder="email"
              :class="input"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-400">
              {{ errors.email[0] }}
            </p>
          </div>
        </div>

        <div class="mt-4">
          <label
            for="subject"
            class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-muted"
            >Subject (optional)</label
          >
          <input
            id="subject"
            v-model="form.subject"
            maxlength="150"
            :class="input"
          />
          <p v-if="errors.subject" class="mt-1 text-sm text-red-400">
            {{ errors.subject[0] }}
          </p>
        </div>

        <div class="mt-4">
          <label
            for="body"
            class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-muted"
            >Project details</label
          >
          <textarea
            id="body"
            v-model="form.body"
            rows="6"
            maxlength="3000"
            placeholder="Tell me about your timeline, goals, and what we're building together..."
            :class="input"
          ></textarea>
          <p v-if="errors.body" class="mt-1 text-sm text-red-400">
            {{ errors.body[0] }}
          </p>
        </div>

        <!-- Honeypot: hidden from humans -->
        <div class="absolute left-[9999px]" aria-hidden="true">
          <label for="website">Leave this empty</label>
          <input
            id="website"
            v-model="form.website"
            tabindex="-1"
            autocomplete="off"
          />
        </div>

        <button
          :disabled="loading"
          class="mt-5 rounded-xl bg-brand px-6 py-3 font-semibold text-white shadow-lg shadow-brand/30 hover:bg-indigo-500 disabled:opacity-50"
        >
          {{ loading ? "Sending..." : "Send message" }}
        </button>
      </form>
    </div>
  </section>
</template>
