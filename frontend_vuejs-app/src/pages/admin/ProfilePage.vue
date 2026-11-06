<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from "vue";
import { adminApi } from "@/api";
import { useToastStore } from "@/stores/toast";
import { mediaUrl } from "@/utils/media";

const toast = useToastStore();
const profile = ref(null);
const loading = ref(true);
const saving = ref(false);
const errors = ref({});
const avatarFile = ref(null);
const resumeFile = ref(null);
const avatarPreview = ref(null);

const AVATAR_MAX_BYTES = 2 * 1024 * 1024;
const RESUME_MAX_BYTES = 5 * 1024 * 1024;

const form = reactive({
  full_name: "",
  title: "",
  bio: "",
  email: "",
  phone_number: "",
  address: "",
});

function setAvatarPreview(url) {
  if (avatarPreview.value?.startsWith("blob:")) {
    URL.revokeObjectURL(avatarPreview.value);
  }
  avatarPreview.value = url;
}

function onAvatarChange(event) {
  const file = event.target.files?.[0];
  if (!file) return;

  if (file.size > AVATAR_MAX_BYTES) {
    avatarFile.value = null;
    setAvatarPreview(profile.value?.avatar_url ? mediaUrl(profile.value.avatar_url) : null);
    errors.value = { ...errors.value, avatar: ["Avatar must be 2 MB or smaller."] };
    event.target.value = "";
    return;
  }

  delete errors.value.avatar;
  avatarFile.value = file;
  setAvatarPreview(URL.createObjectURL(file));
}

function onResumeChange(event) {
  const file = event.target.files?.[0] ?? null;
  if (!file) {
    resumeFile.value = null;
    return;
  }

  if (file.size > RESUME_MAX_BYTES) {
    resumeFile.value = null;
    errors.value = { ...errors.value, resume: ["Resume must be 5 MB or smaller."] };
    event.target.value = "";
    return;
  }

  delete errors.value.resume;
  resumeFile.value = file;
}

async function loadProfile() {
  loading.value = true;
  try {
    const { data } = await adminApi.profile();
    profile.value = data;
    Object.assign(form, {
      full_name: data?.full_name ?? "",
      title: data?.title ?? "",
      bio: data?.bio ?? "",
      email: data?.email ?? "",
      phone_number: data?.phone_number ?? "",
      address: data?.address ?? "",
    });
    avatarFile.value = null;
    resumeFile.value = null;
    setAvatarPreview(data?.avatar_url ? mediaUrl(data.avatar_url) : null);
  } catch {
    toast.error("Could not load your profile.");
  } finally {
    loading.value = false;
  }
}

async function saveProfile() {
  saving.value = true;
  errors.value = {};
  const payload = new FormData();
  for (const [key, value] of Object.entries(form)) {
    payload.append(key, value);
  }
  if (avatarFile.value) payload.append("avatar", avatarFile.value);
  if (resumeFile.value) payload.append("resume", resumeFile.value);

  try {
    await adminApi.updateProfile(payload);
    toast.success("Profile saved.");
    await loadProfile();
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors ?? {};
      toast.error("Please check the highlighted fields.");
    } else {
      toast.error("Could not save your profile.");
    }
  } finally {
    saving.value = false;
  }
}

onMounted(loadProfile);
onBeforeUnmount(() => {
  if (avatarPreview.value?.startsWith("blob:")) {
    URL.revokeObjectURL(avatarPreview.value);
  }
});
</script>

<template>
  <section class="max-w-3xl">
    <h1 class="text-2xl font-bold">Profile</h1>
    <p v-if="loading" class="mt-5 text-gray-500">Loading profile...</p>
    <form v-else class="mt-5 space-y-4 rounded-lg bg-white p-5 shadow" @submit.prevent="saveProfile">
      <div>
        <label for="profile-name" class="mb-1 block text-sm">Full name *</label>
        <input id="profile-name" v-model="form.full_name" required maxlength="255" class="w-full rounded border px-3 py-2" />
        <p v-if="errors.full_name" class="mt-1 text-sm text-red-600">{{ errors.full_name[0] }}</p>
      </div>
      <div>
        <label for="profile-title" class="mb-1 block text-sm">Professional title *</label>
        <input id="profile-title" v-model="form.title" required maxlength="255" class="w-full rounded border px-3 py-2" />
        <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title[0] }}</p>
      </div>
      <div>
        <label for="profile-email" class="mb-1 block text-sm">Public email *</label>
        <input id="profile-email" v-model="form.email" type="email" required maxlength="255" class="w-full rounded border px-3 py-2" />
        <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
      </div>
      <div class="grid gap-4 sm:grid-cols-2">
        <label class="text-sm">
          Phone
          <input v-model="form.phone_number" maxlength="255" class="mt-1 w-full rounded border px-3 py-2" />
          <span v-if="errors.phone_number" class="mt-1 block text-red-600">{{ errors.phone_number[0] }}</span>
        </label>
        <label class="text-sm">
          Address
          <input v-model="form.address" maxlength="255" class="mt-1 w-full rounded border px-3 py-2" />
          <span v-if="errors.address" class="mt-1 block text-red-600">{{ errors.address[0] }}</span>
        </label>
      </div>
      <div>
        <label for="profile-bio" class="mb-1 block text-sm">Bio *</label>
        <textarea id="profile-bio" v-model="form.bio" required rows="6" class="w-full rounded border px-3 py-2"></textarea>
        <p v-if="errors.bio" class="mt-1 text-sm text-red-600">{{ errors.bio[0] }}</p>
      </div>
      <div>
        <label for="profile-avatar" class="mb-1 block text-sm">Avatar (max 2 MB)</label>
        <img v-if="avatarPreview" :src="avatarPreview" alt="Profile avatar preview" class="mb-2 h-24 w-24 rounded-full object-cover" />
        <input id="profile-avatar" type="file" accept="image/*" class="block" @change="onAvatarChange" />
        <p v-if="errors.avatar" class="mt-1 text-sm text-red-600">{{ errors.avatar[0] }}</p>
      </div>
      <div>
        <label for="profile-resume" class="mb-1 block text-sm">Resume (PDF, max 5 MB)</label>
        <p v-if="profile?.resume_url" class="mb-2 text-sm"><a :href="mediaUrl(profile.resume_url)" target="_blank" rel="noopener noreferrer" class="text-blue-700 hover:underline">View current resume</a></p>
        <input id="profile-resume" type="file" accept="application/pdf,.pdf" class="block" @change="onResumeChange" />
        <p v-if="errors.resume" class="mt-1 text-sm text-red-600">{{ errors.resume[0] }}</p>
      </div>
      <button :disabled="saving" class="rounded bg-gray-900 px-5 py-2 text-white disabled:opacity-50">
        {{ saving ? "Saving..." : "Save profile" }}
      </button>
    </form>
  </section>
</template>
