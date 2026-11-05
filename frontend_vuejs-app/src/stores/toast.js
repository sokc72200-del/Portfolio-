import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useToastStore = defineStore('toast', ( ) => {
    const items = ref([])
    let nextId = 1

    function show (text, type = 'success', ms = 3000) {
        const id = nextId++
        items.value.push({ id, text, type })
        setTimeout(() => remove(id), ms)
    }

    function remove(id){
        items.value = items.value.filter((t) => t.id !== id)
    }

    return {
        items,
        show,
        remove,
        success: (t) => show(t, 'success'),
        error: (t) => show(t, 'error', 5000),
    }
})