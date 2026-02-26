<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, onMounted, watch } from 'vue'
import { menu, guardarRegistro } from '@/routes/publico'
import api from '@/routes/publico/api'

// ---------------------------------------------------------------------------
// Props desde Inertia (registros del día cargados en el servidor al entrar)
// ---------------------------------------------------------------------------
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

// ---------------------------------------------------------------------------
// Estado reactivo
// ---------------------------------------------------------------------------
const inputTag         = ref('')
const inputNombre      = ref('')
const empleadoActual   = ref<Empleado | null>(null)
const estadoBusquedaTag = ref<'idle' | 'buscando' | 'encontrado' | 'no_encontrado'>('idle')
const sugerenciasNombre = ref<Empleado[]>([])
const mensajeError      = ref('')
const guardando         = ref(false)
const mensajeExito      = ref('')
const tabla             = ref<RegistroHoy[]>([...props.registrosHoy])

// Debounce para búsqueda por nombre
let timerNombre: ReturnType<typeof setTimeout> | null = null

// ---------------------------------------------------------------------------
// Funciones AJAX
// ---------------------------------------------------------------------------

/** Busca por tag al presionar Enter o al escribir (debounce 400ms) */
async function buscarPorTag() {
  const tag = inputTag.value.trim()
  if (!tag) return

  estadoBusquedaTag.value = 'buscando'
  empleadoActual.value = null
  mensajeError.value = ''

  try {
    const res = await fetch(`${api.buscarPorTag().url}&tag=${encodeURIComponent(tag)}`)
    if (res.status === 404) {
      estadoBusquedaTag.value = 'no_encontrado'
      mensajeError.value = 'No se encontró ningún empleado con ese número de tag.'
      return
    }
    const data: Empleado = await res.json()
    empleadoActual.value = data
    estadoBusquedaTag.value = 'encontrado'
  } catch {
    estadoBusquedaTag.value = 'no_encontrado'
    mensajeError.value = 'Error de conexión. Intenta de nuevo.'
  }
}

/** Búsqueda por nombre con debounce de 400ms */
watch(inputNombre, (valor) => {
  sugerenciasNombre.value = []
  if (timerNombre) clearTimeout(timerNombre)
  if (valor.trim().length < 2) return

  timerNombre = setTimeout(async () => {
    try {
      const res = await fetch(`${api.buscarPorNombre().url}&nombre=${encodeURIComponent(valor.trim())}`)
      sugerenciasNombre.value = await res.json()
    } catch { /* silencioso */ }
  }, 400)
})

/** Al seleccionar una sugerencia, rellena el formulario */
function seleccionarSugerencia(emp: Empleado) {
  empleadoActual.value = emp
  inputTag.value = emp.numero_tag
  inputNombre.value = ''
  sugerenciasNombre.value = []
  estadoBusquedaTag.value = 'encontrado'
  mensajeError.value = ''
}

/** Limpiar el formulario */
function limpiarFormulario() {
  inputTag.value = ''
  inputNombre.value = ''
  empleadoActual.value = null
  estadoBusquedaTag.value = 'idle'
  sugerenciasNombre.value = []
  mensajeError.value = ''
  mensajeExito.value = ''
}

/** Guardar el registro de asistencia */
async function guardarAsistencia() {
  if (!empleadoActual.value) return

  guardando.value = true
  mensajeExito.value = ''
  mensajeError.value = ''

  try {
    // Obtener CSRF token de la cookie de Laravel
    const csrfToken = (document.cookie.match(/XSRF-TOKEN=([^;]+)/) ?? [])[1]
    const token = csrfToken ? decodeURIComponent(csrfToken) : ''

    const res = await fetch(guardarRegistro().url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': token,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        empleado_temporal_id: empleadoActual.value.id,
        tipo: 'asistencia',
      }),
    })

    const data = await res.json()

    if (data.exito) {
      mensajeExito.value = `✓ Asistencia registrada para ${data.nombre_completo} a las ${data.fecha_hora}`
      // Añadir a la tabla del día sin recargar
      tabla.value.unshift({
        id: data.id,
        numero_tag: data.numero_tag,
        nombre_completo: data.nombre_completo,
        fecha_hora: data.fecha_hora,
        tipo: data.tipo,
      })
      // Limpiar el formulario después de 2 seg
      setTimeout(limpiarFormulario, 2000)
    } else {
      mensajeError.value = 'No se pudo guardar el registro.'
    }
  } catch {
    mensajeError.value = 'Error de conexión al guardar.'
  } finally {
    guardando.value = false
  }
}
</script>

<template>
  <Head title="Registro de Asistencia" />

  <div class="min-h-screen bg-gray-50">

    <!-- Barra superior -->
    <header class="bg-teal-600 text-white px-6 py-4 flex items-center justify-between shadow">
      <div class="flex items-center gap-3">
        <button
          @click="router.visit(menu().url)"
          class="rounded-lg bg-teal-700 hover:bg-teal-800 px-3 py-1.5 text-xs font-medium transition"
        >
          ← Volver
        </button>
        <h1 class="text-lg font-semibold">Registro de Asistencia</h1>
      </div>
      <span class="text-teal-200 text-sm">{{ new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
    </header>

    <div class="mx-auto max-w-4xl p-6 space-y-6">

      <!-- Alertas -->
      <div v-if="mensajeExito" class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm font-medium">
        {{ mensajeExito }}
      </div>
      <div v-if="mensajeError" class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm">
        ⚠ {{ mensajeError }}
      </div>

      <!-- Tarjeta de búsqueda y formulario -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-5">
        <h2 class="text-base font-semibold text-gray-700">Identificación del Empleado</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

          <!-- Búsqueda por Tag -->
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Número de Tag</label>
            <div class="flex gap-2">
              <input
                v-model="inputTag"
                type="text"
                placeholder="Escribe el tag y presiona Enter"
                @keyup.enter="buscarPorTag"
                class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
              />
              <button
                @click="buscarPorTag"
                class="rounded-lg bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 text-sm font-medium transition"
              >
                Buscar
              </button>
            </div>
            <!-- Estado de búsqueda -->
            <p v-if="estadoBusquedaTag === 'buscando'" class="mt-1 text-xs text-gray-400 animate-pulse">Buscando...</p>
            <p v-if="estadoBusquedaTag === 'no_encontrado'" class="mt-1 text-xs text-red-500">Tag no encontrado</p>
          </div>

          <!-- Búsqueda por Nombre (alternativa) -->
          <div class="relative">
            <label class="block text-sm font-medium text-gray-600 mb-1">¿No sabes tu tag? Busca por nombre</label>
            <input
              v-model="inputNombre"
              type="text"
              placeholder="Escribe tu nombre o apellido..."
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
            />
            <!-- Dropdown de sugerencias -->
            <div
              v-if="sugerenciasNombre.length > 0"
              class="absolute z-20 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg overflow-hidden"
            >
              <button
                v-for="emp in sugerenciasNombre"
                :key="emp.id"
                @click="seleccionarSugerencia(emp)"
                class="w-full text-left px-4 py-2.5 text-sm hover:bg-teal-50 transition border-b border-gray-50 last:border-0"
              >
                <span class="font-medium text-gray-800">{{ emp.nombre }} {{ emp.apellido_paterno }} {{ emp.apellido_materno }}</span>
                <span class="ml-2 text-xs text-gray-400">Tag: {{ emp.numero_tag }}</span>
              </button>
            </div>
            <p v-if="inputNombre.length >= 2 && sugerenciasNombre.length === 0" class="mt-1 text-xs text-gray-400">Sin coincidencias...</p>
          </div>
        </div>

        <!-- Datos del empleado encontrado -->
        <transition name="fade">
          <div v-if="empleadoActual" class="rounded-xl border border-teal-200 bg-teal-50 p-4">
            <p class="text-xs text-teal-600 font-medium mb-2 uppercase tracking-wide">Empleado identificado</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
              <div>
                <p class="text-gray-400 text-xs">Tag</p>
                <p class="font-semibold text-gray-800">{{ empleadoActual.numero_tag }}</p>
              </div>
              <div>
                <p class="text-gray-400 text-xs">Nombre</p>
                <p class="font-semibold text-gray-800">{{ empleadoActual.nombre }}</p>
              </div>
              <div>
                <p class="text-gray-400 text-xs">Ap. Paterno</p>
                <p class="font-semibold text-gray-800">{{ empleadoActual.apellido_paterno }}</p>
              </div>
              <div>
                <p class="text-gray-400 text-xs">Ap. Materno</p>
                <p class="font-semibold text-gray-800">{{ empleadoActual.apellido_materno ?? '—' }}</p>
              </div>
            </div>
            <div class="mt-4 flex gap-2">
              <button
                @click="guardarAsistencia"
                :disabled="guardando"
                class="rounded-lg bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 text-sm font-medium transition disabled:opacity-50"
              >
                {{ guardando ? 'Registrando...' : '✓ Registrar Asistencia' }}
              </button>
              <button
                @click="limpiarFormulario"
                class="rounded-lg border border-gray-300 hover:bg-gray-100 text-gray-600 px-4 py-2 text-sm transition"
              >
                Cancelar
              </button>
            </div>
          </div>
        </transition>
      </div>

      <!-- Tabla de Registros del Día -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-700">
            Asistencias de Hoy
            <span class="ml-2 inline-flex items-center rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-medium text-teal-700">
              {{ tabla.length }}
            </span>
          </h2>
        </div>
        <div class="overflow-x-auto">
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
              <tr v-if="tabla.length === 0">
                <td colspan="4" class="text-center py-10 text-gray-400 text-sm">
                  Aún no hay registros de asistencia hoy.
                </td>
              </tr>
              <tr
                v-for="(reg, i) in tabla"
                :key="reg.id"
                class="border-b border-gray-50 hover:bg-gray-50 transition"
              >
                <td class="px-4 py-3 text-gray-400 text-xs">{{ i + 1 }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    {{ reg.numero_tag }}
                  </span>
                </td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ reg.nombre_completo }}</td>
                <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ reg.fecha_hora }}</td>
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
