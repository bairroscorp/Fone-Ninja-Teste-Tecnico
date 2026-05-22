<script setup>
defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  error: { type: String, default: '' },
  min: { type: [String, Number], default: undefined },
  step: { type: [String, Number], default: undefined },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-slate-700 mb-1">{{ label }}</label>
    <input
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :min="min"
      :step="step"
      class="w-full rounded-lg border px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
      :class="error ? 'border-red-400' : 'border-slate-300'"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
  </div>
</template>
