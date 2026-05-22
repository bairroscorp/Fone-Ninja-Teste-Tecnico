<script setup>
defineProps({
  total: { type: Number, default: 0 },
  lucro: { type: Number, default: 0 },
})

const formatMoney = (value) =>
  Number(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
</script>

<template>
  <div class="grid grid-cols-2 gap-4 p-4 bg-indigo-50 rounded-lg border border-indigo-100">
    <div>
      <p class="text-xs text-slate-500 uppercase tracking-wide">Total da venda</p>
      <p class="text-xl font-semibold text-slate-800">{{ formatMoney(total) }}</p>
    </div>
    <div>
      <p class="text-xs text-slate-500 uppercase tracking-wide">Lucro estimado</p>
      <p class="text-xl font-semibold" :class="lucro >= 0 ? 'text-emerald-600' : 'text-red-600'">
        {{ formatMoney(lucro) }}
      </p>
    </div>
  </div>
</template>
