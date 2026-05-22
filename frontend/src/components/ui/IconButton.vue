<script setup>
import { Pencil, Trash2, Ban, Eye } from 'lucide-vue-next'

defineProps({
  variant: { type: String, default: 'edit' },
  title: { type: String, default: '' },
})

const icons = { edit: Pencil, delete: Trash2, cancel: Ban, view: Eye }
const colors = {
  edit: 'text-indigo-600 hover:bg-indigo-50',
  delete: 'text-red-600 hover:bg-red-50',
  cancel: 'text-amber-600 hover:bg-amber-50',
  view: 'text-slate-600 hover:bg-slate-50',
}
</script>

<template>
  <button
    type="button"
    :title="title"
    class="p-2 rounded-lg transition-colors"
    :class="colors[variant]"
    v-bind="$attrs"
  >
    <component :is="icons[variant]" class="w-4 h-4" />
  </button>
</template>

<script>
export default { inheritAttrs: false }
</script>
