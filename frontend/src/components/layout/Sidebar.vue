<script setup>
import { RouterLink, useRoute } from 'vue-router'
import { Package, ShoppingCart, TrendingUp } from 'lucide-vue-next'

const route = useRoute()

const links = [
  { to: '/produtos', label: 'Produtos', icon: Package },
  { to: '/compras', label: 'Compras', icon: ShoppingCart },
  { to: '/vendas', label: 'Vendas', icon: TrendingUp },
]
</script>

<template>
  <aside class="w-64 bg-white border-r border-slate-200 min-h-screen p-6">
    <div class="mb-8">
      <h2 class="text-lg font-bold text-indigo-600">ERP Estoque</h2>
      <p class="text-xs text-slate-500 mt-1">Fone Ninja</p>
    </div>
    <nav class="space-y-1">
      <RouterLink
        v-for="link in links"
        :key="link.to"
        :to="link.to"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
        :class="
          route.path === link.to
            ? 'bg-indigo-50 text-indigo-700'
            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
        "
      >
        <component :is="link.icon" class="w-5 h-5" />
        {{ link.label }}
      </RouterLink>
    </nav>
  </aside>
</template>
