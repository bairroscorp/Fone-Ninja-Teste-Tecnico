import { defineStore } from 'pinia'
import { ref } from 'vue'
import * as produtosApi from '../api/produtos'

export const useProdutosStore = defineStore('produtos', () => {
  const produtos = ref([])
  const loading = ref(false)

  const fetchProdutos = async () => {
    loading.value = true
    try {
      const { data } = await produtosApi.listar()
      produtos.value = data.data ?? data
    } finally {
      loading.value = false
    }
  }

  return { produtos, loading, fetchProdutos }
})
