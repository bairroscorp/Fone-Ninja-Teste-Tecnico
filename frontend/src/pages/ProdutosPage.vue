<script setup>
import { ref, reactive, onMounted } from 'vue'
import AppLayout from '../components/layout/AppLayout.vue'
import PageHeader from '../components/layout/PageHeader.vue'
import BaseTable from '../components/ui/BaseTable.vue'
import BaseModal from '../components/ui/BaseModal.vue'
import BaseButton from '../components/ui/BaseButton.vue'
import IconButton from '../components/ui/IconButton.vue'
import ProdutoForm from '../components/produtos/ProdutoForm.vue'
import { useCrudModal } from '../composables/useCrudModal'
import { useSwal } from '../composables/useSwal'
import { useAsync } from '../composables/useAsync'
import * as produtosApi from '../api/produtos'

const produtos = ref([])
const form = reactive({ nome: '', preco_venda: '' })
const formErrors = ref({})
const { isOpen, mode, selectedItem, openCreate, openEdit, close } = useCrudModal()
const { loading, execute } = useAsync()
const { success, error, confirmDelete } = useSwal()

const columns = [
  { key: 'nome', label: 'Nome' },
  { key: 'preco_venda', label: 'Preço Venda', format: (v) => formatMoney(v) },
  { key: 'custo_medio', label: 'Custo Médio', format: (v) => formatMoney(v) },
  { key: 'estoque', label: 'Estoque' },
]

const formatMoney = (value) =>
  Number(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })

const load = async () => {
  const { data } = await produtosApi.listar()
  produtos.value = data.data ?? data
}

const resetForm = () => {
  form.nome = ''
  form.preco_venda = ''
  formErrors.value = {}
}

const handleOpenCreate = () => {
  resetForm()
  openCreate()
}

const handleOpenEdit = (item) => {
  form.nome = item.nome
  form.preco_venda = item.preco_venda
  formErrors.value = {}
  openEdit(item)
}

const handleSubmit = async (data) => {
  formErrors.value = {}
  try {
    await execute(async () => {
      const payload = {
        nome: data.nome,
        preco_venda: Number(data.preco_venda),
      }
      if (mode.value === 'create') {
        await produtosApi.criar(payload)
        await success('Produto cadastrado!')
      } else {
        await produtosApi.atualizar(selectedItem.value.id, payload)
        await success('Produto atualizado!')
      }
      close()
      resetForm()
      await load()
    })
  } catch (e) {
    if (e.response?.status === 422) {
      formErrors.value = e.response.data.errors || {}
      await error('Erro de validação', e.friendlyMessage)
    } else {
      await error('Erro', e.friendlyMessage)
    }
  }
}

const handleDelete = async (item) => {
  const result = await confirmDelete(`Excluir "${item.nome}"?`)
  if (!result.isConfirmed) return

  try {
    await execute(() => produtosApi.excluir(item.id))
    await success('Produto excluído!')
    await load()
  } catch (e) {
    await error('Não foi possível excluir', e.friendlyMessage)
  }
}

onMounted(load)
</script>

<template>
  <AppLayout>
    <PageHeader title="Produtos">
      <template #actions>
        <BaseButton @click="handleOpenCreate">+ Novo</BaseButton>
      </template>
    </PageHeader>

    <BaseTable :columns="columns" :rows="produtos" :loading="loading">
      <template #actions="{ row }">
        <div class="flex justify-end gap-1">
          <IconButton variant="edit" title="Editar" @click="handleOpenEdit(row)" />
          <IconButton variant="delete" title="Excluir" @click="handleDelete(row)" />
        </div>
      </template>
    </BaseTable>

    <BaseModal
      :open="isOpen"
      :title="mode === 'create' ? 'Novo Produto' : 'Editar Produto'"
      @close="close"
    >
      <ProdutoForm
        :model-value="form"
        :errors="formErrors"
        :loading="loading"
        @submit="handleSubmit"
      />
    </BaseModal>
  </AppLayout>
</template>
