<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { guardarRegistro, menu } from '@/routes/publico'
import api from '@/routes/publico/api'

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

const inputTag = ref('')
const inputNombre = ref('')
const empleadoActual = ref<Empleado | null>(null)
const estadoBusquedaTag = ref<'idle' | 'buscando' | 'encontrado' | 'no_encontrado'>('idle')
const sugerenciasNombre = ref<Empleado[]>([])
const mensajeError = ref('')
const guardando = ref(false)
const mensajeExito = ref('')
const tabla = ref<RegistroHoy[]>([...props.registrosHoy])
const buscandoNombre = ref(false)

let timerNombre: ReturnType<typeof setTimeout> | null = null

async function buscarPorTag() {
  const tag = inputTag.value.trim().toUpperCase()
  if (!tag) return

  inputTag.value = tag
  estadoBusquedaTag.value = 'buscando'
  empleadoActual.value = null
  mensajeError.value = ''

  try {
    const url = api.buscarPorTag.url({ query: { tag } })
    const res = await fetch(url, {
      headers: { Accept: 'application/json' },
    })

    if (res.status === 404) {
      estadoBusquedaTag.value = 'no_encontrado'
      mensajeError.value = 'No se encontro ningun empleado con ese numero de tag.'
      return
    }

    if (!res.ok) {
      estadoBusquedaTag.value = 'no_encontrado'
      mensajeError.value = 'No fue posible completar la busqueda por tag.'
      return
    }

    const data: Empleado = await res.json()
    empleadoActual.value = data
    inputTag.value = data.numero_tag
    estadoBusquedaTag.value = 'encontrado'
  } catch {
    estadoBusquedaTag.value = 'no_encontrado'
    mensajeError.value = 'Error de conexion. Intenta de nuevo.'
  }
}

watch(inputNombre, (valor) => {
  sugerenciasNombre.value = []
  if (timerNombre) clearTimeout(timerNombre)

  const termino = valor.trim()
  if (termino.length < 2) {
    buscandoNombre.value = false
    return
  }

  timerNombre = setTimeout(async () => {
    buscandoNombre.value = true
    try {
      const url = api.buscarPorNombre.url({ query: { nombre: termino } })
      const res = await fetch(url, {
        headers: { Accept: 'application/json' },
      })

      if (!res.ok) {
        sugerenciasNombre.value = []
        return
      }

      sugerenciasNombre.value = await res.json()
    } catch {
      sugerenciasNombre.value = []
    } finally {
      buscandoNombre.value = false
    }
  }, 300)
})

function seleccionarSugerencia(emp: Empleado) {
  empleadoActual.value = emp
  inputTag.value = emp.numero_tag
  inputNombre.value = ''
  sugerenciasNombre.value = []
  estadoBusquedaTag.value = 'encontrado'
  mensajeError.value = ''
}

function limpiarFormulario() {
  inputTag.value = ''
  inputNombre.value = ''
  empleadoActual.value = null
  estadoBusquedaTag.value = 'idle'
  sugerenciasNombre.value = []
  mensajeError.value = ''
  mensajeExito.value = ''
}

async function guardarAsistencia() {
  if (!empleadoActual.value) return

  guardando.value = true
  mensajeExito.value = ''
  mensajeError.value = ''

  try {
    const csrfToken = (document.cookie.match(/XSRF-TOKEN=([^;]+)/) ?? [])[1]
    const token = csrfToken ? decodeURIComponent(csrfToken) : ''

    const res = await fetch(guardarRegistro().url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': token,
        Accept: 'application/json',
      },
      body: JSON.stringify({
        empleado_temporal_id: empleadoActual.value.id,
        tipo: 'asistencia',
      }),
    })

    const data = await res.json()

    if (data.exito) {
      mensajeExito.value = `Asistencia registrada para ${data.nombre_completo} a las ${data.fecha_hora}`
      tabla.value.unshift({
        id: data.id,
        numero_tag: data.numero_tag,
        nombre_completo: data.nombre_completo,
        fecha_hora: data.fecha_hora,
        tipo: data.tipo,
      })
      setTimeout(limpiarFormulario, 2000)
    } else {
      mensajeError.value = 'No se pudo guardar el registro.'
    }
  } catch {
    mensajeError.value = 'Error de conexion al guardar.'
  } finally {
    guardando.value = false
  }
}
</script>

<template>
  <Head title="Registro de Asistencia" />

  <div class="min-h-screen bg-gray-50">
    <header class="flex items-center justify-between bg-teal-600 px-6 py-4 text-white shadow">
      <div class="flex items-center gap-3">
        <button
          class="rounded-lg bg-teal-700 px-3 py-1.5 text-xs font-medium transition hover:bg-teal-800"
          @click="router.visit(menu().url)"
        >
          Volver
        </button>
        <h1 class="text-lg font-semibold">Registro de Asistencia</h1>
      </div>
      <span class="text-sm text-teal-200">
        {{ new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
      </span>
    </header>

    <div class="mx-auto max-w-4xl space-y-6 p-6">
      <div
        v-if="mensajeExito"
        class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800"
      >
        {{ mensajeExito }}
      </div>
      <div
        v-if="mensajeError"
        class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
      >
        {{ mensajeError }}
      </div>

      <div class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-700">Identificacion del Empleado</h2>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-600">Numero de Tag</label>
            <div class="flex gap-2">
              <input
                v-model="inputTag"
                type="text"
                placeholder="Escribe el tag y presiona Enter"
                class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none"
                @input="inputTag = inputTag.toUpperCase()"
                @keyup.enter="buscarPorTag"
              />
              <button
                class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700"
                @click="buscarPorTag"
              >
                Buscar
              </button>
            </div>
            <p v-if="estadoBusquedaTag === 'buscando'" class="mt-1 animate-pulse text-xs text-gray-400">Buscando...</p>
            <p v-if="estadoBusquedaTag === 'no_encontrado'" class="mt-1 text-xs text-red-500">Tag no encontrado</p>
          </div>

          <div class="relative">
            <label class="mb-1 block text-sm font-medium text-gray-600">No sabes tu tag? Busca por nombre</label>
            <input
              v-model="inputNombre"
              type="text"
              placeholder="Escribe tu nombre o apellido..."
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none"
            />
            <div
              v-if="sugerenciasNombre.length > 0"
              class="absolute z-20 mt-1 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg"
            >
              <button
                v-for="emp in sugerenciasNombre"
                :key="emp.id"
                class="w-full border-b border-gray-50 px-4 py-2.5 text-left text-sm transition last:border-0 hover:bg-teal-50"
                @click="seleccionarSugerencia(emp)"
              >
                <span class="font-medium text-gray-800">{{ emp.nombre }} {{ emp.apellido_paterno }} {{ emp.apellido_materno }}</span>
                <span class="ml-2 text-xs text-gray-400">Tag: {{ emp.numero_tag }}</span>
              </button>
            </div>
            <p v-if="buscandoNombre" class="mt-1 text-xs text-gray-400">Buscando coincidencias...</p>
            <p
              v-else-if="inputNombre.trim().length >= 2 && sugerenciasNombre.length === 0"
              class="mt-1 text-xs text-gray-400"
            >
              Sin coincidencias...
            </p>
          </div>
        </div>

        <transition name="fade">
          <div v-if="empleadoActual" class="rounded-xl border border-teal-200 bg-teal-50 p-4">
            <p class="mb-2 text-xs font-medium tracking-wide text-teal-600 uppercase">Empleado identificado</p>
            <div class="grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
              <div>
                <p class="text-xs text-gray-400">Tag</p>
                <p class="font-semibold text-gray-800">{{ empleadoActual.numero_tag }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Nombre</p>
                <p class="font-semibold text-gray-800">{{ empleadoActual.nombre }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Ap. Paterno</p>
                <p class="font-semibold text-gray-800">{{ empleadoActual.apellido_paterno }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Ap. Materno</p>
                <p class="font-semibold text-gray-800">{{ empleadoActual.apellido_materno ?? '-' }}</p>
              </div>
            </div>
            <div class="mt-4 flex gap-2">
              <button
                class="rounded-lg bg-teal-600 px-6 py-2 text-sm font-medium text-white transition hover:bg-teal-700 disabled:opacity-50"
                :disabled="guardando"
                @click="guardarAsistencia"
              >
                {{ guardando ? 'Registrando...' : 'Registrar Asistencia' }}
              </button>
              <button
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-100"
                @click="limpiarFormulario"
              >
                Cancelar
              </button>
            </div>
          </div>
        </transition>
      </div>

      <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
          <h2 class="text-base font-semibold text-gray-700">
            Asistencias de Hoy
            <span class="ml-2 inline-flex items-center rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-medium text-teal-700">
              {{ tabla.length }}
            </span>
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="border-b border-gray-100 bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-500">#</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Tag</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Nombre Completo</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500">Hora</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="tabla.length === 0">
                <td colspan="4" class="py-10 text-center text-sm text-gray-400">Aun no hay registros de asistencia hoy.</td>
              </tr>
              <tr
                v-for="(reg, i) in tabla"
                :key="reg.id"
                class="border-b border-gray-50 transition hover:bg-gray-50"
              >
                <td class="px-4 py-3 text-xs text-gray-400">{{ i + 1 }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    {{ reg.numero_tag }}
                  </span>
                </td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ reg.nombre_completo }}</td>
                <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ reg.fecha_hora }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s, transform 0.25s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
