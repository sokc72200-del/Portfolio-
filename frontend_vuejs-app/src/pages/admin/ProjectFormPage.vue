<script setup>
import { reactive, ref, computed, onMounted, onBeforeUnmount } from "vue";
import { useRoute, useRouter } from "vue-router";
import { adminApi } from "@/api";
import { useToastStore } from "@/stores/toast";
import { mediaUrl } from "@/utils/media";

const route = useRoute();
const router = useRouter();
const toast = useToastStore();

const slugParam = route.params.slug;
const isEdit = computed(() => !!slugParam);

const form = reactive({
  title: "",
  slug: "",
  summary: "",
  description: "",
  repo_url: "",
  status: "draft",
  featured: false,
  sort_order: 0,
  skill_ids: [],
})

const allSkills = ref([])
const thumbnailFile = ref(null)
const thumbnailPreview = ref(null)
const errors = ref({})
const saving = ref(false)
const loading = ref(true)

function onThumbnail(e) {
    const file = e.target.files[0]
    if (!file) return
    thumbnailFile.value = file
    if (thumbnailPreview.value?.startsWith('blob:')) URL.revokeObjectURL(thumbnailPreview.value)
    thumbnailPreview.value = URL.createObjectURL(file)
}

onBeforeUnmount(() => {
    if (thumbnailPreview.value?.startsWith('blob:')) URL.revokeObjectURL(thumbnailPreview.value)
})

onMounted(async() => {
    try {
        const skillsRes = await adminApi.skills()
        allSkills.value = skillsRes.data

        if (isEdit.value) {
            const { data } = await adminApi.project(slugParam)
            Object.assign(form, {
                title: data.title,
                slug: data.slug,
                summary: data.summary,
                description: data.description ?? '',
                repo_url: data.repo_url ?? '',
                featured: !!data.featured,
                status: data.status,
                sort_order: data.sort_order ?? 0,
                skill_ids: data.skills.map((s) => s.id),
            })
            thumbnailPreview.value = mediaUrl(data.thumbnail_url)
        }
    } catch {
        toast.error('Could not load data. ')
        router.push({ name: 'admin-projects'})
    } finally {
        loading.value = false
    }
})

async function submit() {
    saving.value = true
    errors.value = {}

    const fd = new FormData()
    fd.append('title', form.title)
    // Keep an empty slug out of edit requests so Laravel leaves the current slug unchanged.
    if (form.slug) fd.append('slug', form.slug)
    fd.append('summary', form.summary)
    fd.append('description', form.description)
    fd.append('repo_url', form.repo_url)
    fd.append('status', form.status)
    fd.append('featured', form.featured ? "1" : "0")
    fd.append('sort_order', String(form.sort_order || 0))
    fd.append('sync_skills', "1")
    form.skill_ids.forEach((id) => fd.append('skill_ids[]', id))
    if (thumbnailFile.value) fd.append('thumbnail', thumbnailFile.value)

    try {
        if (isEdit.value) {
            fd.append('_method', 'PUT')
            await adminApi.updateProject(slugParam, fd)
            toast.success('project update.')
        }else {
            await adminApi.createProject(fd)
            toast.success('Project created. ')
        }
        router.push({name: 'admin-projects'})
    } catch (e){
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors ?? {}
            toast.error('Please fix the highlighted fields.')
        } else {
            toast.error('Save failed. Check the server log. ')
        }
    } finally {
        saving.value = false
    }
}
</script>

<template>
  <div class="max-w-3xl">
    <RouterLink
      :to="{ name: 'admin-projects' }"
      class="text-sm text-gray-600 hover:underline"
    >
      &larr; Back to projects
    </RouterLink>
    <h1 class="mt-2 text-2xl font-bold">
      {{ isEdit ? "Edit project" : "New project" }}
    </h1>

    <p v-if="loading" class="mt-6 text-gray-500">Loading...</p>

    <form
      v-else
      class="mt-6 space-y-5 rounded-lg bg-white p-6 shadow"
      @submit.prevent="submit"
      novalidate
    >
      <!-- Title -->
      <div>
        <label for="title" class="mb-1 block text-sm font-medium"
          >Title *</label
        >
        <input
          id="title"
          v-model="form.title"
          type="text"
          class="w-full rounded border px-3 py-2"
        />
        <p v-if="errors.title" class="mt-1 text-sm text-red-600">
          {{ errors.title[0] }}
        </p>
      </div>

      <!-- Slug (edit only) -->
      <div v-if="isEdit">
        <label for="slug" class="mb-1 block text-sm font-medium"
          >Slug (URL)</label
        >
        <input
          id="slug"
          v-model="form.slug"
          type="text"
          class="w-full rounded border px-3 py-2"
        />
        <p v-if="errors.slug" class="mt-1 text-sm text-red-600">
          {{ errors.slug[0] }}
        </p>
      </div>

      <!-- Summary -->
      <div>
        <label for="summary" class="mb-1 block text-sm font-medium"
          >Summary * (shown on cards)</label
        >
        <input
          id="summary"
          v-model="form.summary"
          type="text"
          maxlength="255"
          class="w-full rounded border px-3 py-2"
        />
        <p v-if="errors.summary" class="mt-1 text-sm text-red-600">
          {{ errors.summary[0] }}
        </p>
      </div>

      <!-- Description -->
      <div>
        <label for="description" class="mb-1 block text-sm font-medium"
          >Description (case study)</label
        >
        <textarea
          id="description"
          v-model="form.description"
          rows="8"
          class="w-full rounded border px-3 py-2"
        ></textarea>
        <p v-if="errors.description" class="mt-1 text-sm text-red-600">
          {{ errors.description[0] }}
        </p>
      </div>

      <!-- Repository URL -->
      <div>
        <label for="repo_url" class="mb-1 block text-sm font-medium"
          >Repository URL</label
        >
        <input
          id="repo_url"
          v-model="form.repo_url"
          type="url"
          placeholder="https://github.com/..."
          class="w-full rounded border px-3 py-2"
        />
        <p v-if="errors.repo_url" class="mt-1 text-sm text-red-600">
          {{ errors.repo_url[0] }}
        </p>
      </div>

      <!-- Status / featured / order -->
      <div class="grid gap-4 sm:grid-cols-3">
        <div>
          <label for="status" class="mb-1 block text-sm font-medium"
            >Status *</label
          >
          <select
            id="status"
            v-model="form.status"
            class="w-full rounded border px-3 py-2"
          >
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="archived">Archived</option>
          </select>
          <p v-if="errors.status" class="mt-1 text-sm text-red-600">
            {{ errors.status[0] }}
          </p>
        </div>
        <div>
          <label for="sort_order" class="mb-1 block text-sm font-medium"
            >Sort order</label
          >
          <input
            id="sort_order"
            v-model.number="form.sort_order"
            type="number"
            min="0"
            class="w-full rounded border px-3 py-2"
          />
          <p v-if="errors.sort_order" class="mt-1 text-sm text-red-600">
            {{ errors.sort_order[0] }}
          </p>
        </div>
        <label class="flex items-center gap-2 self-end pb-2 text-sm">
          <input v-model="form.featured" type="checkbox" class="h-4 w-4" />
          Featured on Home
        </label>
      </div>

      <!-- Skills -->
      <fieldset>
        <legend class="mb-2 text-sm font-medium">Skills used</legend>
        <p v-if="!allSkills.length" class="text-sm text-gray-500">
          No skills yet. Add some first (Skills page comes next, or use
          Postman).
        </p>
        <div class="flex flex-wrap gap-2">
          <label
            v-for="s in allSkills"
            :key="s.id"
            class="flex cursor-pointer items-center gap-1.5 rounded-full border px-3 py-1 text-sm"
            :class="
              form.skill_ids.includes(s.id)
                ? 'border-gray-900 bg-gray-900 text-white'
                : 'bg-white'
            "
          >
            <input
              v-model="form.skill_ids"
              :value="s.id"
              type="checkbox"
              class="sr-only"
            />
            {{ s.name }}
          </label>
        </div>
        <p v-if="errors.skill_ids" class="mt-1 text-sm text-red-600">
          {{ errors.skill_ids[0] }}
        </p>
      </fieldset>

      <!-- Thumbnail -->
      <div>
        <label for="thumbnail" class="mb-1 block text-sm font-medium"
          >Thumbnail (max 2 MB)</label
        >
        <img
          v-if="thumbnailPreview"
          :src="thumbnailPreview"
          alt="Thumbnail preview"
          class="mb-2 h-40 rounded object-cover"
        />
        <input
          id="thumbnail"
          type="file"
          accept="image/*"
          @change="onThumbnail"
        />
        <p v-if="errors.thumbnail" class="mt-1 text-sm text-red-600">
          {{ errors.thumbnail[0] }}
        </p>
      </div>

      <div class="flex gap-3 pt-2">
        <button
          :disabled="saving"
          class="rounded bg-gray-900 px-5 py-2 text-white disabled:opacity-50"
        >
          {{
            saving ? "Saving..." : isEdit ? "Save changes" : "Create project"
          }}
        </button>
        <RouterLink
          :to="{ name: 'admin-projects' }"
          class="rounded border px-5 py-2"
          >Cancel</RouterLink
        >
      </div>
    </form>
  </div>
</template>
