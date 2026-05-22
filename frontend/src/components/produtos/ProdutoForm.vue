<script setup>
import BaseInput from '../ui/BaseInput.vue'
import BaseButton from '../ui/BaseButton.vue'

const props = defineProps({
  modelValue: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['submit'])

const submit = () => emit('submit', { ...props.modelValue })
</script>

<template>
  <form class="space-y-4" @submit.prevent="submit">
    <BaseInput
      :model-value="modelValue.nome"
      label="Nome"
      placeholder="Nome do produto"
      :error="errors.nome?.[0]"
      @update:model-value="modelValue.nome = $event"
    />
    <BaseInput
      :model-value="modelValue.preco_venda"
      label="Preço de venda sugerido (R$)"
      type="number"
      step="0.01"
      min="0.01"
      placeholder="0,00"
      :error="errors.preco_venda?.[0]"
      @update:model-value="modelValue.preco_venda = $event"
    />
    <div class="flex justify-end gap-2 pt-2">
      <BaseButton type="submit" :loading="loading">Salvar</BaseButton>
    </div>
  </form>
</template>
