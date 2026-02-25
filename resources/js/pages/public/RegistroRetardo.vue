<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

interface RegistroHoy {
  id: number
  numero_tag: string
  nombre_completo: string
  fecha_hora: string
  tipo: string
}

interface Empleado {
  id: number
  numero_tag: string
  nombre: string
  apellido_paterno: string
  apellido_materno: string | null
}

const props = defineProps<{
  registrosHoy: RegistroHoy[]
}>()

const inputTag          = ref('')
const inputNombre       = ref('')
const empleadoActual    = ref<Empleado | null>(null)
const estadoBusqueda    = ref<'idle' | 'buscando' | 'encontrado' | 'no_encontrado'>('idle')
const sugerencias       = ref<Empleado[]>([])
const mensajeError      = ref('')
const guardando         = ref(false)
const mensajeExito      = ref('')
const tabla             = ref<RegistroHoy[]>([...props.registrosHoy])
let timerNombre: ReturnType<typeof setTimeout> | null = null

async function buscarPorTag() {
  const tag = inputTag.value.trim()
  if (!tag) return
  estadoBusqueda.value = 'buscando'
  empleadoActual.value = null
  mensajeError.value = ''
  try {
    const res = await fetch(`/publico/api/buscar-por-tag?tag=${encodeURIComponent(tag)}`)
    if (res.status === 404) { estadoBusqueda.value = 'no_encontrado'; mensajeError.value = 'Tag no encontrado.'; return }
    empleadoActual.value = await res.json()
    estadoBusqueda.value = 'encontrado'
  } catch { estadoBusqueda.value = 'no_encontrado'; mensajeError.value = 'Error de conexión.' }
}

watch(inputNombre, (valor) => {
  sugerencias.value = []
  if (timerNombre) clearTimeout(timerNombre)
  if (valor.trim().length < 2) return
  timerNombre = setTimeout(async () => {
    try {
      const res = await fetch(`/publico/api/buscar-por-nombre?nombre=${encodeURIComponent(valor.trim())}`)
      sugerencias.value = await res.json()
    } catch { /* silencioso */ }
  }, 400)
})

function seleccionarSugerencia(emp: Empleado) {
  empleadoActual.value = emp
  inputTag.value = emp.numero_tag
  inputNombre.value = ''
  sugerencias.value = []
  estadoBusqueda.value = 'encontrado'
}

function limpiar() {
  inputTag.value = ''; inputNombre.value = ''; empleadoActual.value = null
  estadoBusqueda.value = 'idle'; sugerencias.value = []; mensajeError.value = ''; mensajeExito.value = ''
}

async function guardar() {
  if (!empleadoActual.value) return
  guardando.value = true; mensajeExito.value = ''; mensajeError.value = ''
  try {
    const csrfToken = (document.cookie.match(/XSRF-TOKEN=([^;]+)/) ?? [])[1]
    const token = csrfToken ? decodeURIComponent(csrfToken) : ''
    const res = await fetch('/publico/guardar-registro', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-XSRF-TOKEN': token, 'Accept': 'application/json' },
      body: JSON.stringify({ empleado_temporal_id: empleadoActual.value.id, tipo: 'retardo' }),
    })
    const data = await res.json()
    if (data.exito) {
      mensajeExito.value = `✓ Retardo registrado para ${data.nombre_completo} a las ${data.fecha_hora}`
      tabla.value.unshift({ id: data.id, numero_tag: data.numero_tag, nombre_completo: data.nombre_completo, fecha_hora: data.fecha_hora, tipo: data.tipo })
      setTimeout(limpiar, 2000)
    } else { mensajeError.value = 'No se pudo guardar.' }
  } catch { mensajeError.value = 'Error de conexión.' }
  finally { guardando.value = false }
}
</script>

<template>
  <Head title="Registro de Retardo" />
  <div class="min-h-screen bg-gray-50">
    <header class="bg-amber-500 text-white px-6 py-4 flex items-center justify-between shadow">
      <div class="flex items-center gap-3">
        <button @click="router.visit('/publico')" class="rounded-lg bg-amber-600 hover:bg-amber-700 px-3 py-1.5 text-xs font-medium transition">← Volver</button>
        <h1 class="text-lg font-semibold">Registro de Retardo</h1>
      </div>
      <span class="text-amber-100 text-sm">{{ new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
    </header>

    <div class="mx-auto max-w-4xl p-6 space-y-6">
      <div v-if="mensajeExito" class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm font-medium">{{ mensajeExito }}</div>
      <div v-if="mensajeError" class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">⚠ {{ mensajeError }}</div>

      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-5">
        <h2 class="text-base font-semibold text-gray-700">Identificación del Empleado</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Número de Tag</label>
            <div class="flex gap-2">
              <input v-model="inputTag" type="text" placeholder="Tag y presiona Enter" @keyup.enter="buscarPorTag" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
              <button @click="buscarPorTag" class="rounded-lg bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 text-sm font-medium transition">Buscar</button>
            </div>
            <p v-if="estadoBusqueda === 'buscando'" class="mt-1 text-xs text-gray-400 animate-pulse">Buscando...</p>
            <p v-if="estadoBusqueda === 'no_encontrado'" class="mt-1 text-xs text-red-500">Tag no encontrado</p>
          </div>
          <div class="relative">
            <label class="block text-sm font-medium text-gray-600 mb-1">¿No sabes tu tag? Busca por nombre</label>
            <input v-model="inputNombre" type="text" placeholder="Nombre o apellido..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
            <div v-if="sugerencias.length > 0" class="absolute z-20 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg overflow-hidden">
              <button v-for="emp in sugerencias" :key="emp.id" @click="seleccionarSugerencia(emp)" class="w-full text-left px-4 py-2.5 text-sm hover:bg-amber-50 transition border-b border-gray-50 last:border-0">
                <span class="font-medium text-gray-800">{{ emp.nombre }} {{ emp.apellido_paterno }} {{ emp.apellido_materno }}</span>
                <span class="ml-2 text-xs text-gray-400">Tag: {{ emp.numero_tag }}</span>
              </button>
            </div>
          </div>
        </div>

        <transition name="fade">
          <div v-if="empleadoActual" class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-xs text-amber-600 font-medium mb-2 uppercase tracking-wide">Empleado identificado</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
              <div><p class="text-gray-400 text-xs">Tag</p><p class="font-semibold text-gray-800">{{ empleadoActual.numero_tag }}</p></div>
              <div><p class="text-gray-400 text-xs">Nombre</p><p class="font-semibold text-gray-800">{{ empleadoActual.nombre }}</p></div>
              <div><p class="text-gray-400 text-xs">Ap. Paterno</p><p class="font-semibold text-gray-800">{{ empleadoActual.apellido_paterno }}</p></div>
              <div><p class="text-gray-400 text-xs">Ap. Materno</p><p class="font-semibold text-gray-800">{{ empleadoActual.apellido_materno ?? '—' }}</p></div>
            </div>
            <div class="mt-4 flex gap-2">
              <button @click="guardar" :disabled="guardando" class="rounded-lg bg-amber-500 hover:bg-amber-600 text-white px-6 py-2 text-sm font-medium transition disabled:opacity-50">
                {{ guardando ? 'Registrando...' : '⚠ Registrar Retardo' }}
              </button>
              <button @click="limpiar" class="rounded-lg border border-gray-300 hover:bg-gray-100 text-gray-600 px-4 py-2 text-sm transition">Cancelar</button>
            </div>
          </div>
        </transition>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="text-base font-semibold text-gray-700">Retardos de Hoy <span class="ml-2 inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">{{ tabla.length }}</span></h2>
        </div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">#</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Tag</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Nombre Completo</th>
              <th class="text-left px-4 py-3 text-gray-500 font-medium">Hora</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="tabla.length === 0"><td colspan="4" class="text-center py-10 text-gray-400 text-sm">Sin retardos registrados hoy.</td></tr>
            <tr v-for="(reg, i) in tabla" :key="reg.id" class="border-b border-gray-50 hover:bg-gray-50 transition">
              <td class="px-4 py-3 text-gray-400 text-xs">{{ i + 1 }}</td>
              <td class="px-4 py-3"><span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-200">{{ reg.numero_tag }}</span></td>
              <td class="px-4 py-3 font-medium text-gray-800">{{ reg.nombre_completo }}</td>
              <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ reg.fecha_hora }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s, transform 0.25s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
