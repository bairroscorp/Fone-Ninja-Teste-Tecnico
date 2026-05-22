<script setup>
import Sidebar from './Sidebar.vue'
</script>

<template>
  <div class="flex min-h-screen">
    <Sidebar />
    <main class="flex-1 p-8">
      <slot />
    </main>
  </div>
</template>
