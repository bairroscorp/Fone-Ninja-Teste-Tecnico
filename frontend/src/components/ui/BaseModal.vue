<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
  size: { type: String, default: 'md' },
})

defineEmits(['close'])

const sizes = {
  sm: 'max-w-md',
  md: 'max-w-lg',
  lg: 'max-w-2xl',
  xl: 'max-w-4xl',
}
</script>

<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/50" @click="$emit('close')" />
      <div
        class="relative bg-white rounded-xl shadow-xl w-full max-h-[90vh] overflow-y-auto"
        :class="sizes[size]"
      >
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
          <h3 class="text-lg font-semibold text-slate-800">{{ title }}</h3>
          <button
            type="button"
            class="text-slate-400 hover:text-slate-600 text-xl leading-none"
            @click="$emit('close')"
          >
            &times;
          </button>
        </div>
        <div class="px-6 py-4">
          <slot />
        </div>
        <div v-if="$slots.footer" class="px-6 py-4 border-t border-slate-200 flex justify-end gap-2">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </Teleport>
</template>
