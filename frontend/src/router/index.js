import { createRouter, createWebHistory } from 'vue-router'
import ProdutosPage from '../pages/ProdutosPage.vue'
import ComprasPage from '../pages/ComprasPage.vue'
import VendasPage from '../pages/VendasPage.vue'

const routes = [
  { path: '/', redirect: '/produtos' },
  { path: '/produtos', name: 'produtos', component: ProdutosPage },
  { path: '/compras', name: 'compras', component: ComprasPage },
  { path: '/vendas', name: 'vendas', component: VendasPage },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})
