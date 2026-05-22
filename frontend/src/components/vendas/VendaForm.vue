<script setup>
import { computed } from 'vue'
import { Plus, Trash2 } from 'lucide-vue-next'
import BaseInput from '../ui/BaseInput.vue'
import BaseButton from '../ui/BaseButton.vue'
import VendaResumo from './VendaResumo.vue'

const props = defineProps({
  modelValue: { type: Object, required: true },
  produtos: { type: Array, default: () => [] },
  errors: { type: Object, default: () => ({}) },
  loading: { type: Boolean, default: false },
})

const emit = defineEmits(['submit'])

const getProduto = (id) => props.produtos.find((p) => p.id === Number(id))

const total = computed(() =>
  props.modelValue.produtos.reduce(
    (sum, item) => sum + Number(item.quantidade || 0) * Number(item.preco_unitario || 0),
    0
  )
)

const lucroEstimado = computed(() =>
  props.modelValue.produtos.reduce((sum, item) => {
    const produto = getProduto(item.id)
    if (!produto) return sum
    const lucro =
      (Number(item.preco_unitario || 0) - Number(produto.custo_medio || 0)) *
      Number(item.quantidade || 0)
    return sum + lucro
  }, 0)
)

const addItem = () => {
  props.modelValue.produtos.push({ id: '', quantidade: 1, preco_unitario: '' })
}

const removeItem = (index) => {
  if (props.modelValue.produtos.length > 1) {
    props.modelValue.produtos.splice(index, 1)
  }
}

const onProdutoChange = (item) => {
  const produto = getProduto(item.id)
  if (produto) {
    item.preco_unitario = produto.preco_venda
  }
}

const submit = () => emit('submit', { ...props.modelValue })
</script>

<template>
  <form class="space-y-4" @submit.prevent="submit">
    <BaseInput
      :model-value="modelValue.cliente"
      label="Cliente"
      placeholder="Nome do cliente"
      :error="errors.cliente?.[0]"
      @update:model-value="modelValue.cliente = $event"
    />

    <div>
      <div class="flex items-center justify-between mb-2">
        <label class="text-sm font-medium text-slate-700">Produtos</label>
        <button
          type="button"
          class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-700"
          @click="addItem"
        >
          <Plus class="w-4 h-4" /> Adicionar linha
        </button>
      </div>
      <p v-if="errors.produtos?.[0]" class="text-xs text-red-500 mb-2">{{ errors.produtos[0] }}</p>

      <div class="space-y-3">
        <div
          v-for="(item, index) in modelValue.produtos"
          :key="index"
          class="grid grid-cols-12 gap-2 items-end p-3 bg-slate-50 rounded-lg"
        >
          <div class="col-span-5">
            <label class="block text-xs text-slate-500 mb-1">Produto</label>
            <select
              v-model="item.id"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
              @change="onProdutoChange(item)"
            >
              <option value="">Selecione...</option>
              <option v-for="p in produtos" :key="p.id" :value="p.id">
                {{ p.nome }} (est: {{ p.estoque }})
              </option>
            </select>
          </div>
          <div class="col-span-2">
            <BaseInput v-model="item.quantidade" label="Qtd" type="number" min="1" />
          </div>
          <div class="col-span-3">
            <BaseInput
              v-model="item.preco_unitario"
              label="Preço unit."
              type="number"
              step="0.01"
              min="0.01"
            />
          </div>
          <div class="col-span-1 flex justify-center pb-2">
            <button
              type="button"
              class="p-2 text-red-500 hover:bg-red-50 rounded-lg"
              :disabled="modelValue.produtos.length === 1"
              @click="removeItem(index)"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <VendaResumo :total="total" :lucro="lucroEstimado" />

    <div class="flex justify-end">
      <BaseButton type="submit" :loading="loading">Registrar venda</BaseButton>
    </div>
  </form>
</template>
