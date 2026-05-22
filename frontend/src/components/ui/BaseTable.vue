<script setup>
defineProps({
  columns: { type: Array, required: true },
  rows: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
})
</script>

<template>
  <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
    <div v-if="loading" class="flex justify-center py-16">
      <div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin" />
    </div>
    <div v-else-if="!rows.length" class="py-16 text-center text-slate-500">
      <slot name="empty">Nenhum registro encontrado.</slot>
    </div>
    <div v-else class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-3 text-left font-medium text-slate-600"
              :class="col.class"
            >
              {{ col.label }}
            </th>
            <th v-if="$slots.actions" class="px-4 py-3 text-right font-medium text-slate-600 w-28">
              Ações
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="(row, idx) in rows" :key="row.id ?? idx" class="hover:bg-slate-50/50">
            <td
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-3 text-slate-700"
              :class="col.class"
            >
              <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                {{ col.format ? col.format(row[col.key], row) : row[col.key] }}
              </slot>
            </td>
            <td v-if="$slots.actions" class="px-4 py-3 text-right">
              <slot name="actions" :row="row" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
