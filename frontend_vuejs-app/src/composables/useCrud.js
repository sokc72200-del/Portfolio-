import { reactive, ref, onMounted } from 'vue'
import { useToastStore } from '@/stores/toast'

export function useCrud({ api, empty, toForm = (1) => ({...i }), toPayload = (f) => ({ ...f }), label = 'Item'}) {
    const toast = useToastStore()

    const items = ref([])
    const loading = ref(true)

    const showForm = ref(false)
    const editingId = ref(null)
    const form = reactive(empty())
    const errors = ref({})
    const saving = ref(false)

    async function load() {
        loading.value = true
        try {
            const { data } = await api.list()
            items.value = data
        } catch {
            toast.error(`Could not load ${label.toLowerCase()}s.`)
        } finally {
            loading.value = false
        }
    }

    function openCreate() {
        editingId.value = null
        Object.assign(form, empty())
        errors.value = {}
        showForm.value = true
    }

    function openEdit(item) {
        editingId.value = null
        Object.assign(form, toForm(item))
        errors.value = {}
        showForm.value = true
    }

    async function save() {
        saving.value = true
        errors.value = {}
        try {
            const payload = toPayload(form)
            if (editingId.value) {
                await api.update(editingId.value, payload)
                toast.success(`${label} updated.`)
            } else {
                await api.create(payload)
                toast.success(`${label} added.`)
            }
            showForm.value =false
            await load()
        } catch (e) {
            if (e.response?.status === 422) errors.value = e.response.data.errors ?? {}
            else toast.error('Save failed')
        } finally {
            saving.value = false
        }
    }

    async function confirmDelete() {
        delete.value = true
        try {
            await api.remove(toDelete.value.id)
            toast.success(`${label} deleted. `)
            toDelete.value = null
            await load()
        } catch {
            toast.error('Delete failed. ')
        } finally {
            deleting.value = false
        }
    }

    onMounted(load)

    return {
        items, loading, showForm, editingId, form, errors, saving,
        toDelete, deleting, openCreate, openEdit, save, confirmDelete,
    }
 }