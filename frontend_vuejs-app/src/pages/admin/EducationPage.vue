<script setup>
import { onMounted, reactive, ref } from "vue";
import { adminApi } from "@/api";
import ConfirmModal from "@/components/ConfirmModal.vue";
import { useToastStore } from "@/stores/toast";

const toast = useToastStore();
const educations = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingId = ref(null);
const deleteTarget = ref(null);
const deleting = ref(false);
const errors = ref({});

const emptyForm = () => ({
  institution_name: "",
  degree: "",
  field_of_study: "",
  start_date: "",
  end_date: "",
  is_current: false,
  location: "",
  description: "",
});
const form = reactive(emptyForm());

function dateInput(value) {
  return value ? value.slice(0, 10) : "";
}

function resetForm() {
  Object.assign(form, emptyForm());
  editingId.value = null;
  errors.value = {};
}

function editEducation(item) {
  Object.assign(form, {
    institution_name: item.institution_name,
    degree: item.degree,
    field_of_study: item.field_of_study,
    start_date: dateInput(item.start_date),
    end_date: dateInput(item.end_date),
    is_current: !!item.is_current,
    location: item.location ?? "",
    description: item.description ?? "",
  });
  editingId.value = item.id;
  errors.value = {};
}

async function loadEducations() {
  loading.value = true;
  try {
    const { data } = await adminApi.educations();
    educations.value = data;
  } catch {
    toast.error("Could not load education.");
  } finally {
    loading.value = false;
  }
}

async function saveEducation() {
  saving.value = true;
  errors.value = {};
  const payload = {
    ...form,
    end_date: form.is_current ? null : form.end_date || null,
  };
  try {
    if (editingId.value) {
      await adminApi.updateEducation(editingId.value, payload);
      toast.success("Education updated.");
    } else {
      await adminApi.createEducation(payload);
      toast.success("Education added.");
    }
    resetForm();
    await loadEducations();
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors ?? {};
      toast.error("Please check the highlighted fields.");
    } else {
      toast.error("Could not save this education.");
    }
  } finally {
    saving.value = false;
  }
}

async function deleteEducation() {
  deleting.value = true;
  try {
    await adminApi.deleteEducation(deleteTarget.value.id);
    toast.success("Education deleted.");
    deleteTarget.value = null;
    await loadEducations();
  } catch {
    toast.error("Could not delete this education.");
  } finally {
    deleting.value = false;
  }
}

onMounted(loadEducations);
</script>

<template>
  <section class="max-w-4xl">
    <h1 class="text-2xl font-bold">Education</h1>
    <form class="mt-5 grid gap-4 rounded-lg bg-white p-5 shadow sm:grid-cols-2" @submit.prevent="saveEducation">
      <label class="text-sm">
        Institution *
        <input v-model="form.institution_name" required maxlength="255" class="mt-1 w-full rounded border px-3 py-2" />
        <span v-if="errors.institution_name" class="mt-1 block text-red-600">{{ errors.institution_name[0] }}</span>
      </label>
      <label class="text-sm">
        Degree *
        <input v-model="form.degree" required maxlength="255" class="mt-1 w-full rounded border px-3 py-2" />
        <span v-if="errors.degree" class="mt-1 block text-red-600">{{ errors.degree[0] }}</span>
      </label>
      <label class="text-sm">
        Field of study *
        <input v-model="form.field_of_study" required maxlength="255" class="mt-1 w-full rounded border px-3 py-2" />
        <span v-if="errors.field_of_study" class="mt-1 block text-red-600">{{ errors.field_of_study[0] }}</span>
      </label>
      <label class="text-sm">
        Location
        <input v-model="form.location" maxlength="255" class="mt-1 w-full rounded border px-3 py-2" />
        <span v-if="errors.location" class="mt-1 block text-red-600">{{ errors.location[0] }}</span>
      </label>
      <label class="text-sm">
        Start date *
        <input v-model="form.start_date" type="date" required class="mt-1 w-full rounded border px-3 py-2" />
        <span v-if="errors.start_date" class="mt-1 block text-red-600">{{ errors.start_date[0] }}</span>
      </label>
      <label class="text-sm">
        End date
        <input v-model="form.end_date" type="date" :disabled="form.is_current" class="mt-1 w-full rounded border px-3 py-2 disabled:bg-gray-100" />
        <span v-if="errors.end_date" class="mt-1 block text-red-600">{{ errors.end_date[0] }}</span>
      </label>
      <label class="flex items-center gap-2 text-sm sm:col-span-2">
        <input v-model="form.is_current" type="checkbox" class="h-4 w-4" />
        I am currently studying here
      </label>
      <label class="text-sm sm:col-span-2">
        Description
        <textarea v-model="form.description" rows="4" class="mt-1 w-full rounded border px-3 py-2"></textarea>
        <span v-if="errors.description" class="mt-1 block text-red-600">{{ errors.description[0] }}</span>
      </label>
      <div class="flex gap-2 sm:col-span-2">
        <button :disabled="saving" class="rounded bg-gray-900 px-4 py-2 text-white disabled:opacity-50">
          {{ saving ? "Saving..." : editingId ? "Save changes" : "Add education" }}
        </button>
        <button v-if="editingId" type="button" class="rounded border px-4 py-2" @click="resetForm">Cancel edit</button>
      </div>
    </form>

    <p v-if="loading" class="mt-5 text-gray-500">Loading education...</p>
    <div v-else-if="educations.length" class="mt-5 space-y-3">
      <article v-for="item in educations" :key="item.id" class="rounded-lg bg-white p-5 shadow">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h2 class="font-semibold">{{ item.degree }} in {{ item.field_of_study }}</h2>
            <p>{{ item.institution_name }}</p>
            <p class="text-sm text-gray-600">{{ item.location || "Location not specified" }}</p>
            <p class="text-sm text-gray-500">{{ dateInput(item.start_date) }} – {{ item.is_current ? "Present" : dateInput(item.end_date) || "End date not set" }}</p>
            <p v-if="item.description" class="mt-2 whitespace-pre-line text-sm">{{ item.description }}</p>
          </div>
          <div class="flex gap-3 text-sm">
            <button class="text-blue-700 hover:underline" @click="editEducation(item)">Edit</button>
            <button class="text-red-700 hover:underline" @click="deleteTarget = item">Delete</button>
          </div>
        </div>
      </article>
    </div>
    <p v-else class="mt-5 text-gray-500">No education added yet.</p>

    <ConfirmModal :open="!!deleteTarget" title="Delete education?" :message="`Delete ${deleteTarget?.degree} at ${deleteTarget?.institution_name}?`" :loading="deleting" @confirm="deleteEducation" @cancel="deleteTarget = null" />
  </section>
</template>
