<script setup>
import { ref, reactive, onMounted } from 'vue'
import AppLayout from '../components/layout/AppLayout.vue'
import PageHeader from '../components/layout/PageHeader.vue'
import BaseTable from '../components/ui/BaseTable.vue'
import BaseModal from '../components/ui/BaseModal.vue'
import BaseButton from '../components/ui/BaseButton.vue'
import IconButton from '../components/ui/IconButton.vue'
import CompraForm from '../components/compras/CompraForm.vue'
import { useCrudModal } from '../composables/useCrudModal'
import { useSwal } from '../composables/useSwal'
import { useAsync } from '../composables/useAsync'
import { useProdutosStore } from '../stores/produtos'
import * as comprasApi from '../api/compras'

const compras = ref([])
const produtosStore = useProdutosStore()
const form = reactive({
  fornecedor: '',
  produtos: [{ id: '', quantidade: 1, preco_unitario: '' }],
})
const formErrors = ref({})
const detailCompra = ref(null)
const detailOpen = ref(false)
const { isOpen, openCreate, close } = useCrudModal()
const { loading, execute } = useAsync()
const { success, error } = useSwal()

const columns = [
  { key: 'created_at', label: 'Data', format: (v) => formatDate(v) },
  { key: 'fornecedor', label: 'Fornecedor' },
  { key: 'itens_count', label: 'Itens' },
  { key: 'total', label: 'Total', format: (v) => formatMoney(v) },
]

const formatMoney = (value) =>
  Number(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })

const formatDate = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleString('pt-BR')
}

const load = async () => {
  const { data } = await comprasApi.listar()
  const items = data.data ?? data
  compras.value = items.map((c) => ({
    ...c,
    itens_count: c.itens?.length ?? 0,
  }))
}

const resetForm = () => {
  form.fornecedor = ''
  form.produtos = [{ id: '', quantidade: 1, preco_unitario: '' }]
  formErrors.value = {}
}

const handleOpenCreate = async () => {
  resetForm()
  await produtosStore.fetchProdutos()
  openCreate()
}

const handleSubmit = async (data) => {
  formErrors.value = {}
  try {
    await execute(async () => {
      const payload = {
        fornecedor: data.fornecedor,
        produtos: data.produtos.map((p) => ({
          id: Number(p.id),
          quantidade: Number(p.quantidade),
          preco_unitario: Number(p.preco_unitario),
        })),
      }
      await comprasApi.criar(payload)
      await success('Compra registrada!', 'Estoque e custo médio atualizados.')
      close()
      resetForm()
      await load()
      await produtosStore.fetchProdutos()
    })
  } catch (e) {
    if (e.response?.status === 422) {
      formErrors.value = e.response.data.errors || {}
    }
    await error('Erro ao registrar compra', e.friendlyMessage)
  }
}

const handleView = async (row) => {
  const { data } = await comprasApi.buscar(row.id)
  detailCompra.value = data.data ?? data
  detailOpen.value = true
}

onMounted(load)
</script>

<template>
  <AppLayout>
    <PageHeader title="Compras">
      <template #actions>
        <BaseButton @click="handleOpenCreate">+ Nova Compra</BaseButton>
      </template>
    </PageHeader>

    <BaseTable :columns="columns" :rows="compras" :loading="loading">
      <template #actions="{ row }">
        <IconButton variant="view" title="Ver detalhes" @click="handleView(row)" />
      </template>
    </BaseTable>

    <BaseModal :open="isOpen" title="Nova Compra" size="lg" @close="close">
      <CompraForm
        :model-value="form"
        :produtos="produtosStore.produtos"
        :errors="formErrors"
        :loading="loading"
        @submit="handleSubmit"
      />
    </BaseModal>

    <BaseModal
      :open="detailOpen"
      title="Detalhes da Compra"
      size="lg"
      @close="detailOpen = false"
    >
      <div v-if="detailCompra" class="space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><span class="text-slate-500">Fornecedor:</span> {{ detailCompra.fornecedor }}</div>
          <div><span class="text-slate-500">Total:</span> {{ formatMoney(detailCompra.total) }}</div>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-3 py-2 text-left">Produto</th>
              <th class="px-3 py-2 text-right">Qtd</th>
              <th class="px-3 py-2 text-right">Preço Unit.</th>
              <th class="px-3 py-2 text-right">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in detailCompra.itens" :key="item.id" class="border-t">
              <td class="px-3 py-2">{{ item.produto_nome }}</td>
              <td class="px-3 py-2 text-right">{{ item.quantidade }}</td>
              <td class="px-3 py-2 text-right">{{ formatMoney(item.preco_unitario) }}</td>
              <td class="px-3 py-2 text-right">{{ formatMoney(item.subtotal) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </BaseModal>
  </AppLayout>
</template>
