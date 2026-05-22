import { ref } from 'vue'

export function useCrudModal() {
  const isOpen = ref(false)
  const mode = ref('create')
  const selectedItem = ref(null)

  const openCreate = () => {
    mode.value = 'create'
    selectedItem.value = null
    isOpen.value = true
  }

  const openEdit = (item) => {
    mode.value = 'edit'
    selectedItem.value = { ...item }
    isOpen.value = true
  }

  const close = () => {
    isOpen.value = false
    selectedItem.value = null
  }

  return { isOpen, mode, selectedItem, openCreate, openEdit, close }
}
