<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { asistencia, retardo, visitas } from '@/routes/publico'
</script>

<template>
  <Head title="Portal Público - Menú Principal" />

  <div class="min-h-screen bg-gradient-to-br from-teal-50 to-cyan-100 flex flex-col items-center justify-center p-6">

    <!-- Logo / Encabezado -->
    <div class="text-center mb-10">
      <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-teal-600 shadow-lg">
        <svg class="h-9 w-9 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.5a4 4 0 100 8 4 4 0 000-8zM4.25 12A7.75 7.75 0 0120 12M4 16.5A10 10 0 0120 16.5" />
        </svg>
      </div>
      <h1 class="text-3xl font-bold text-gray-800">Portal de Acceso</h1>
      <p class="mt-2 text-gray-500 text-sm">Selecciona la opción correspondiente</p>
    </div>

    <!-- Botones de opciones -->
    <div class="grid grid-cols-1 gap-5 w-full max-w-sm">

      <!-- Opción 1: Asistencia -->
      <button
        @click="router.visit(asistencia().url)"
        class="group flex items-center gap-4 rounded-2xl bg-white px-6 py-5 shadow-md border border-gray-100 hover:border-teal-300 hover:shadow-lg transition-all duration-200 text-left"
      >
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition-all duration-200">
          <!-- Ícono reloj -->
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </span>
        <div>
          <p class="font-semibold text-gray-800">Registro de Asistencia</p>
          <p class="text-xs text-gray-400 mt-0.5">Registra tu entrada del día</p>
        </div>
      </button>

      <!-- Opción 2: Retardo -->
      <button
        @click="router.visit(retardo().url)"
        class="group flex items-center gap-4 rounded-2xl bg-white px-6 py-5 shadow-md border border-gray-100 hover:border-amber-300 hover:shadow-lg transition-all duration-200 text-left"
      >
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 group-hover:bg-amber-500 group-hover:text-white transition-all duration-200">
          <!-- Ícono alerta reloj -->
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
          </svg>
        </span>
        <div>
          <p class="font-semibold text-gray-800">Registro de Retardo</p>
          <p class="text-xs text-gray-400 mt-0.5">Registra tu llegada tardía</p>
        </div>
      </button>

      <!-- Opción 3: Visitas -->
      <button
        @click="router.visit(visitas().url)"
        class="group flex items-center gap-4 rounded-2xl bg-white px-6 py-5 shadow-md border border-gray-100 hover:border-indigo-300 hover:shadow-lg transition-all duration-200 text-left"
      >
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-200">
          <!-- Ícono persona -->
          <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </span>
        <div>
          <p class="font-semibold text-gray-800">Registro de Visitas</p>
          <p class="text-xs text-gray-400 mt-0.5">Registra la visita de un externo</p>
        </div>
      </button>

    </div>

    <!-- Footer -->
    <p class="mt-10 text-xs text-gray-400">Recursos Humanos · Sistema de Asistencias</p>
  </div>
</template>
