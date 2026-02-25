<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
// RegistroVisitas es un placeholder modular.
// Puede extenderse en el futuro con lógica propia
// (campos: nombre visitante, empresa, a quién viene a ver, etc.)
</script>

<template>
  <Head title="Registro de Visitas" />
  <div class="min-h-screen bg-gray-50">
    <header class="bg-indigo-600 text-white px-6 py-4 flex items-center gap-3 shadow">
      <button
        @click="router.visit('/publico')"
        class="rounded-lg bg-indigo-700 hover:bg-indigo-800 px-3 py-1.5 text-xs font-medium transition"
      >
        ← Volver
      </button>
      <h1 class="text-lg font-semibold">Registro de Visitas</h1>
    </header>

    <div class="mx-auto max-w-2xl p-10 text-center">
      <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-2xl bg-indigo-100">
        <svg class="h-10 w-10 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
      </div>
      <h2 class="text-xl font-semibold text-gray-700 mb-2">Módulo en Construcción</h2>
      <p class="text-gray-400 text-sm">Esta sección está lista para ser desarrollada con la lógica de visitas externas.</p>
    </div>
  </div>
</template>
