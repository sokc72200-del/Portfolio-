<script setup>
import { reactive, ref } from "vue";
import { publicApi } from "@/api";

const empty = () => ({
  name: "",
  email: "",
  subject: "",
  body: "",
  website: "",
});
const form = reactive(empty());

const errors = ref({});
const status = ref("");
const message = ref("");
const loading = ref(false);

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
  <section class="max-w-xl">
    <h1 class="text-2xl font-bold">Contact</h1>
    <p class="mt-1 text-gray-600">
      Have a project or a question? Send me a message.
    </p>

    <p
      v-if="status"
      class="mt-4 rounded p-3 text-sm"
      :class="
        status === 'success'
          ? 'bg-green-50 text-green-700'
          : 'bg-red-50 text-red-700'
      "
      role="status"
    >
      {{ message }}
    </p>

    <form class="mt-6 space-y-4" @submit.prevent="submit" novalidate>
      <div>
        <label for="name" class="mb-1 block text-sm">Name</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          maxlength="100"
          class="w-full rounded border border-gray-300 px-3 py-2"
        />
        <p v-if="errors.name" class="mt-1 text-sm text-red-600">
          {{ errors.name[0] }}
        </p>
      </div>

      <div>
        <label for="email" class="mb-1 block text-sm">Email</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          maxlength="150"
          class="w-full rounded border border-gray-300 px-3 py-2"
        />
        <p v-if="errors.email" class="mt-1 text-sm text-red-600">
          {{ errors.email[0] }}
        </p>
      </div>

      <div>
        <label for="subject" class="mb-1 block text-sm"
          >Subject (optional)</label
        >
        <input
          id="subject"
          v-model="form.subject"
          type="text"
          maxlength="150"
          class="w-full rounded border border-gray-300 px-3 py-2"
        />
        <p v-if="errors.subject" class="mt-1 text-sm text-red-600">
          {{ errors.subject[0] }}
        </p>
      </div>

      <div>
        <label for="body" class="mb-1 block text-sm">Message</label>
        <textarea
          id="body"
          v-model="form.body"
          rows="6"
          maxlength="3000"
          class="w-full rounded border border-gray-300 px-3 py-2"
        ></textarea>
        <p v-if="errors.body" class="mt-1 text-sm text-red-600">
          {{ errors.body[0] }}
        </p>
      </div>

      <!-- Honeypot: hidden from humans, bots fill it in -->
      <div class="absolute-left-[9999px]" aria-hidden="true">
        <label for="website">Leave this empty</label>
        <input
          id="website"
          v-model="form.website"
          type="text"
          tabindex="-1"
          autocomplete="off"
        />
      </div>

      <button
        :disabled="loading"
        class="rounded bg-gray-900 px-5 py-2 text-white disabled:opacity-50"
      >
        {{ loading ? "Sending..." : "Send message" }}
      </button>
    </form>
  </section>
</template>
