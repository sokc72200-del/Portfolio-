<script setup>
import { onMounted, reactive, ref } from "vue";
import { adminApi } from "@/api";
import ConfirmModal from "@/components/ConfirmModal.vue";
import { useToastStore } from "@/stores/toast";

const toast = useToastStore();
const skills = ref([]);
const loading = ref(true);
const saving = ref(false);
const editingId = ref(null);
const deleteTarget = ref(null);
const deleting = ref(false);
const errors = ref({});

const emptyForm = () => ({
  name: "",
  category: "frontend",
  level: 75,
  icon: "",
});
const form = reactive(emptyForm());

function resetForm() {
  Object.assign(form, emptyForm());
  editingId.value = null;
  errors.value = {};
}

function editSkill(skill) {
  Object.assign(form, {
    name: skill.name,
    category: skill.category,
    level: skill.level,
    icon: skill.icon ?? "",
  });
  editingId.value = skill.id;
  errors.value = {};
}

async function loadSkills() {
  loading.value = true;
  try {
    const { data } = await adminApi.skills();
    skills.value = data;
  } catch {
    toast.error("Could not load skills.");
  } finally {
    loading.value = false;
  }
}

async function saveSkill() {
  saving.value = true;
  errors.value = {};
  try {
    if (editingId.value) {
      await adminApi.updateSkill(editingId.value, { ...form });
      toast.success("Skill updated.");
    } else {
      await adminApi.createSkill({ ...form });
      toast.success("Skill created.");
    }
    resetForm();
    await loadSkills();
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors ?? {};
      toast.error("Please check the highlighted fields.");
    } else {
      toast.error("Could not save the skill.");
    }
  } finally {
    saving.value = false;
  }
}

async function deleteSkill() {
  deleting.value = true;
  try {
    await adminApi.deleteSkill(deleteTarget.value.id);
    toast.success("Skill deleted.");
    deleteTarget.value = null;
    await loadSkills();
  } catch {
    toast.error("Could not delete the skill.");
  } finally {
    deleting.value = false;
  }
}

onMounted(loadSkills);
</script>

<template>
  <section class="max-w-4xl">
    <h1 class="text-2xl font-bold">Skills</h1>

    <form class="mt-5 grid gap-4 rounded-lg bg-white p-5 shadow sm:grid-cols-2" @submit.prevent="saveSkill">
      <label class="text-sm">
        Name *
        <input v-model="form.name" required maxlength="255" class="mt-1 w-full rounded border px-3 py-2" />
        <span v-if="errors.name" class="mt-1 block text-red-600">{{ errors.name[0] }}</span>
      </label>
      <label class="text-sm">
        Category *
        <select v-model="form.category" class="mt-1 w-full rounded border px-3 py-2">
          <option value="frontend">Frontend</option>
          <option value="backend">Backend</option>
          <option value="database">Database</option>
          <option value="devops">DevOps</option>
          <option value="other">Other</option>
        </select>
        <span v-if="errors.category" class="mt-1 block text-red-600">{{ errors.category[0] }}</span>
      </label>
      <label class="text-sm">
        Proficiency (1–100) *
        <input v-model.number="form.level" type="number" min="1" max="100" required class="mt-1 w-full rounded border px-3 py-2" />
        <span v-if="errors.level" class="mt-1 block text-red-600">{{ errors.level[0] }}</span>
      </label>
      <label class="text-sm">
        Icon (optional)
        <input v-model="form.icon" maxlength="255" class="mt-1 w-full rounded border px-3 py-2" />
        <span v-if="errors.icon" class="mt-1 block text-red-600">{{ errors.icon[0] }}</span>
      </label>
      <div class="flex gap-2 sm:col-span-2">
        <button :disabled="saving" class="rounded bg-gray-900 px-4 py-2 text-white disabled:opacity-50">
          {{ saving ? "Saving..." : editingId ? "Save changes" : "Add skill" }}
        </button>
        <button v-if="editingId" type="button" class="rounded border px-4 py-2" @click="resetForm">Cancel edit</button>
      </div>
    </form>

    <p v-if="loading" class="mt-5 text-gray-500">Loading skills...</p>
    <div v-else-if="skills.length" class="mt-5 overflow-x-auto rounded-lg bg-white shadow">
      <table class="w-full text-left text-sm">
        <thead class="border-b bg-gray-50 text-gray-600"><tr><th class="p-3">Skill</th><th class="p-3">Category</th><th class="p-3">Level</th><th class="p-3 text-right">Actions</th></tr></thead>
        <tbody>
          <tr v-for="skill in skills" :key="skill.id" class="border-b last:border-0">
            <td class="p-3 font-medium">{{ skill.name }}</td>
            <td class="p-3 capitalize">{{ skill.category }}</td>
            <td class="p-3">{{ skill.level }}%</td>
            <td class="p-3 text-right">
              <button class="mr-3 text-blue-700 hover:underline" @click="editSkill(skill)">Edit</button>
              <button class="text-red-700 hover:underline" @click="deleteTarget = skill">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-else class="mt-5 text-gray-500">No skills added yet.</p>

    <ConfirmModal :open="!!deleteTarget" title="Delete skill?" :message="`Delete ${deleteTarget?.name}?`" :loading="deleting" @confirm="deleteSkill" @cancel="deleteTarget = null" />
  </section>
</template>
