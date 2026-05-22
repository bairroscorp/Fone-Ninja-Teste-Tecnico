<script setup>
import { ref, reactive, onMounted } from 'vue'
import AppLayout from '../components/layout/AppLayout.vue'
import PageHeader from '../components/layout/PageHeader.vue'
import BaseTable from '../components/ui/BaseTable.vue'
import BaseModal from '../components/ui/BaseModal.vue'
import BaseButton from '../components/ui/BaseButton.vue'
import IconButton from '../components/ui/IconButton.vue'
import VendaForm from '../components/vendas/VendaForm.vue'
import { useCrudModal } from '../composables/useCrudModal'
import { useSwal } from '../composables/useSwal'
import { useAsync } from '../composables/useAsync'
import { useProdutosStore } from '../stores/produtos'
import * as vendasApi from '../api/vendas'

const vendas = ref([])
const produtosStore = useProdutosStore()
const form = reactive({
  cliente: '',
  produtos: [{ id: '', quantidade: 1, preco_unitario: '' }],
})
const formErrors = ref({})
const detailVenda = ref(null)
const detailOpen = ref(false)
const { isOpen, openCreate, close } = useCrudModal()
const { loading, execute } = useAsync()
const { success, error, confirmAction } = useSwal()

const columns = [
  { key: 'created_at', label: 'Data', format: (v) => formatDate(v) },
  { key: 'cliente', label: 'Cliente' },
  { key: 'total', label: 'Total', format: (v) => formatMoney(v) },
  { key: 'lucro', label: 'Lucro', format: (v) => formatMoney(v) },
  { key: 'status', label: 'Status' },
]

const formatMoney = (value) =>
  Number(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })

const formatDate = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleString('pt-BR')
}

const load = async () => {
  const { data } = await vendasApi.listar()
  vendas.value = data.data ?? data
}

const resetForm = () => {
  form.cliente = ''
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
        cliente: data.cliente,
        produtos: data.produtos.map((p) => ({
          id: Number(p.id),
          quantidade: Number(p.quantidade),
          preco_unitario: Number(p.preco_unitario),
        })),
      }
      const { data: response } = await vendasApi.criar(payload)
      const venda = response.data ?? response
      await success(
        'Venda registrada!',
        `Total: ${formatMoney(venda.total)} | Lucro: ${formatMoney(venda.lucro)}`
      )
      close()
      resetForm()
      await load()
      await produtosStore.fetchProdutos()
    })
  } catch (e) {
    if (e.response?.status === 422) {
      formErrors.value = e.response.data.errors || {}
    }
    await error('Erro ao registrar venda', e.friendlyMessage)
  }
}

const handleView = async (row) => {
  const { data } = await vendasApi.buscar(row.id)
  detailVenda.value = data.data ?? data
  detailOpen.value = true
}

const handleCancel = async (row) => {
  const result = await confirmAction(
    'Cancelar venda?',
    'O estoque será revertido para todos os itens desta venda.',
    'Sim, cancelar'
  )
  if (!result.isConfirmed) return

  try {
    await execute(() => vendasApi.cancelar(row.id))
    await success('Venda cancelada!', 'Estoque revertido com sucesso.')
    await load()
    await produtosStore.fetchProdutos()
  } catch (e) {
    await error('Erro ao cancelar', e.friendlyMessage)
  }
}

onMounted(load)
</script>

<template>
  <AppLayout>
    <PageHeader title="Vendas">
      <template #actions>
        <BaseButton @click="handleOpenCreate">+ Nova Venda</BaseButton>
      </template>
    </PageHeader>

    <BaseTable :columns="columns" :rows="vendas" :loading="loading">
      <template #cell-status="{ value }">
        <span
          class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium"
          :class="value === 'ativa' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'"
        >
          {{ value === 'ativa' ? 'Ativa' : 'Cancelada' }}
        </span>
      </template>
      <template #actions="{ row }">
        <div class="flex justify-end gap-1">
          <IconButton variant="view" title="Ver detalhes" @click="handleView(row)" />
          <IconButton
            v-if="row.status === 'ativa'"
            variant="cancel"
            title="Cancelar venda"
            @click="handleCancel(row)"
          />
        </div>
      </template>
    </BaseTable>

    <BaseModal :open="isOpen" title="Nova Venda" size="lg" @close="close">
      <VendaForm
        :model-value="form"
        :produtos="produtosStore.produtos"
        :errors="formErrors"
        :loading="loading"
        @submit="handleSubmit"
      />
    </BaseModal>

    <BaseModal :open="detailOpen" title="Detalhes da Venda" size="lg" @close="detailOpen = false">
      <div v-if="detailVenda" class="space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><span class="text-slate-500">Cliente:</span> {{ detailVenda.cliente }}</div>
          <div><span class="text-slate-500">Status:</span> {{ detailVenda.status }}</div>
          <div><span class="text-slate-500">Total:</span> {{ formatMoney(detailVenda.total) }}</div>
          <div><span class="text-slate-500">Lucro:</span> {{ formatMoney(detailVenda.lucro) }}</div>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-3 py-2 text-left">Produto</th>
              <th class="px-3 py-2 text-right">Qtd</th>
              <th class="px-3 py-2 text-right">Preço</th>
              <th class="px-3 py-2 text-right">Custo</th>
              <th class="px-3 py-2 text-right">Lucro</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in detailVenda.itens" :key="item.id" class="border-t">
              <td class="px-3 py-2">{{ item.produto_nome }}</td>
              <td class="px-3 py-2 text-right">{{ item.quantidade }}</td>
              <td class="px-3 py-2 text-right">{{ formatMoney(item.preco_unitario) }}</td>
              <td class="px-3 py-2 text-right">{{ formatMoney(item.custo_unitario) }}</td>
              <td class="px-3 py-2 text-right">{{ formatMoney(item.lucro) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </BaseModal>
  </AppLayout>
</template>
