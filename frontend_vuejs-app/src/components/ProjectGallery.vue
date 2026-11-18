<script setup>
import { ref } from 'vue'
import { adminApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import ConfirmModal from './ConfirmModal.vue';

const props = defineProps({
    slug: { type: String, required:true},
})

const images = defineModel('images', { type: Array, default: ()=>[]})

const toast = useToastStore()

const MAX_BYTES = 4 * 1024 * 1024 // matches max:4096 in Laravel
const caption = ref('')
const uploading = ref(false)
const progress = ref('')
const fileInput = ref(null)

const toDelete = ref(null)
const deleting = ref(false)

async function onFiles(event){
    const files = [...event.target.files]
    if (!files.length) return

    uploading.value = true
    let done = 0

    for (const file of files) {
        progress.value = `uploading ${done + 1} of ${files.length}...`

        if (file.size > MAX_BYTES){
            toast.error(`${file.name} is over 4 MB and was skipped.`)
            continue
        }

        const fb = new FormData()
        fb.append('image', file)
        if (caption.value) fb.append('caption', caption.value)
        fb.append('sort_order', String(images.value.length))

        try {
            const { data } = await adminApi.addProjectImage(props.slug, fb)
            images.value = [...images.value, data]
            done++
        } catch (e) {
            const msg = e.response?.data?.errors?.image?.[0] ?? `Could not upload ${file.name}.`
            toast.error(msg)
        }
    }
    if (done) toast.success(`${done} image${done > 1? 's' : ''} uploaded.`)
    caption.value = ''
    progress.value = ''
    uploading.value = false
    if (fileInput.value) fileInput.value.value = '' // allow picking the same file again
}
async function confirmDelete() {
    deleting.value = true
    try {
        await adminApi.deleteProjectImage(toDelete.value.id)
        images.value = images.value.filter((i) => i.id !== toDelete.value.id)
        toast.success('Image deleted. ')
        toDelete.value = null
    } catch {
        toast.error('Delete failed.')
    } finally {
        deleting.value = false 
    }
}
</script>
<template>
    <section class="mt-8 rounded-lg bg-white p-6 shadow">
    <h2 class="text-lg font-semibold">Gallery</h2>
    <p class="mt-1 text-sm text-gray-500">Screenshots shown on the project page (max 4 MB each).</p>

    <div class="mt-4 flex flex-wrap items-end gap-3">
      <div class="min-w-0 flex-1">
        <label for="gallery-caption" class="mb-1 block text-sm">Caption (optional, applies to this batch)</label>
        <input
          id="gallery-caption"
          v-model="caption"
          maxlength="150"
          :disabled="uploading"
          class="w-full rounded border px-3 py-2"
        />
      </div>
      <div>
        <input
          ref="fileInput"
          type="file"
          accept="image/*"
          multiple
          :disabled="uploading"
          @change="onFiles"
        />
      </div>
    </div>

    <p v-if="uploading" class="mt-3 text-sm text-gray-600" role="status">{{ progress }}</p>

    <p v-if="!images.length" class="mt-6 text-sm text-gray-500">No gallery images yet.</p>

    <ul v-else class="mt-6 grid gap-4 sm:grid-cols-2 md:grid-cols-3">
      <li v-for="img in images" :key="img.id" class="overflow-hidden rounded border">
        <img :src="img.image_url" :alt="img.caption || 'Gallery image'" loading="lazy"
             class="h-36 w-full object-cover" />
        <div class="flex items-center justify-between gap-2 p-2 text-sm">
          <span class="truncate text-gray-600">{{ img.caption || 'No caption' }}</span>
          <button type="button" class="shrink-0 text-red-600 hover:underline" @click="toDelete = img">
            Delete
          </button>
        </div>
      </li>
    </ul>

    <ConfirmModal
      :open="!!toDelete"
      title="Delete image?"
      message="This image will be permanently removed from the project."
      :loading="deleting"
      @confirm="confirmDelete"
      @cancel="toDelete = null"
    />
  </section>
</template>