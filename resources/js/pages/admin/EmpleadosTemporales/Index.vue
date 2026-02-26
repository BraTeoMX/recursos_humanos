<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { toast } from 'vue-sonner'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import AppLayout from '@/layouts/AppLayout.vue'
import { destroy, store } from '@/routes/admin/empleados-temporales'

interface Empleado {
  id: number
  numero_tag: string
  nombre: string
  apellido_paterno: string
  apellido_materno: string | null
}

defineProps<{
  empleados: Empleado[]
}>()

const form = useForm({
  numero_tag: '',
  nombre: '',
  apellido_paterno: '',
  apellido_materno: '',
})

const confirmacionAbierta = ref(false)
const eliminando = ref(false)
const empleadoPorEliminar = ref<Empleado | null>(null)

function guardar() {
  form.post(store().url, {
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: () => {
      toast.error('No se pudo registrar el empleado.', {
        description: 'Revisa los campos marcados e intenta nuevamente.',
      })
    },
  })
}

function abrirConfirmacion(emp: Empleado) {
  empleadoPorEliminar.value = emp
  confirmacionAbierta.value = true
}

function cerrarConfirmacion() {
  confirmacionAbierta.value = false
  if (!eliminando.value) empleadoPorEliminar.value = null
}

function confirmarEliminacion() {
  if (!empleadoPorEliminar.value || eliminando.value) return

  eliminando.value = true
  router.delete(destroy(empleadoPorEliminar.value).url, {
    preserveScroll: true,
    onError: () => {
      toast.error('No se pudo eliminar el empleado.', {
        description: 'Ocurrio un error inesperado. Intenta de nuevo.',
      })
    },
    onFinish: () => {
      eliminando.value = false
      confirmacionAbierta.value = false
      empleadoPorEliminar.value = null
    },
  })
}
</script>

<template>
  <Head title="Empleados Temporales" />

  <AppLayout>
    <div class="mx-auto max-w-5xl space-y-8 p-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Registro de Empleados Temporales</h1>
        <p class="mt-1 text-sm text-gray-500">Administra el catalogo de empleados con acceso por tag.</p>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-700">Nuevo Empleado</h2>
        <form class="grid grid-cols-1 gap-4 md:grid-cols-2" @submit.prevent="guardar">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-600">Numero de Tag *</label>
            <input
              v-model="form.numero_tag"
              type="text"
              placeholder="Ej: TAG-0001"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none"
              :class="{ 'border-red-400': form.errors.numero_tag }"
            />
            <p v-if="form.errors.numero_tag" class="mt-1 text-xs text-red-500">{{ form.errors.numero_tag }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-gray-600">Nombre *</label>
            <input
              v-model="form.nombre"
              type="text"
              placeholder="Nombre(s)"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none"
              :class="{ 'border-red-400': form.errors.nombre }"
            />
            <p v-if="form.errors.nombre" class="mt-1 text-xs text-red-500">{{ form.errors.nombre }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-gray-600">Apellido Paterno *</label>
            <input
              v-model="form.apellido_paterno"
              type="text"
              placeholder="Apellido paterno"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none"
              :class="{ 'border-red-400': form.errors.apellido_paterno }"
            />
            <p v-if="form.errors.apellido_paterno" class="mt-1 text-xs text-red-500">{{ form.errors.apellido_paterno }}</p>
          </div>

          <div>
            <label class="mb-1 block text-sm font-medium text-gray-600">Apellido Materno</label>
            <input
              v-model="form.apellido_materno"
              type="text"
              placeholder="Apellido materno (opcional)"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none"
            />
          </div>

          <div class="flex justify-end md:col-span-2">
            <button
              type="submit"
              :disabled="form.processing"
              class="rounded-lg bg-teal-600 px-6 py-2 text-sm font-medium text-white transition hover:bg-teal-700 disabled:opacity-50"
            >
              {{ form.processing ? 'Guardando...' : 'Registrar Empleado' }}
            </button>
          </div>
        </form>
      </div>

      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-4">
          <h2 class="text-lg font-semibold text-gray-700">
            Empleados Registrados
            <span class="ml-2 text-sm font-normal text-gray-400">({{ empleados.length }})</span>
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="border-b border-gray-100 bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Tag</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Nombre</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Apellido Paterno</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Apellido Materno</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="empleados.length === 0">
                <td colspan="5" class="py-8 text-center text-gray-400">No hay empleados registrados aun.</td>
              </tr>
              <tr
                v-for="emp in empleados"
                :key="emp.id"
                class="border-b border-gray-50 transition hover:bg-gray-50"
              >
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200"
                  >
                    {{ emp.numero_tag }}
                  </span>
                </td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ emp.nombre }}</td>
                <td class="px-4 py-3 text-gray-700">{{ emp.apellido_paterno }}</td>
                <td class="px-4 py-3 text-gray-500">{{ emp.apellido_materno ?? '-' }}</td>
                <td class="px-4 py-3 text-center">
                  <button
                    class="text-xs font-medium text-red-500 transition hover:text-red-700"
                    @click="abrirConfirmacion(emp)"
                  >
                    Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <Dialog :open="confirmacionAbierta" @update:open="(open) => (!open ? cerrarConfirmacion() : null)">
        <DialogContent class="sm:max-w-md">
          <DialogHeader class="space-y-2">
            <DialogTitle>Eliminar empleado</DialogTitle>
            <DialogDescription>
              Se eliminara a
              <strong>{{ empleadoPorEliminar?.nombre }} {{ empleadoPorEliminar?.apellido_paterno }}</strong>
              (tag: {{ empleadoPorEliminar?.numero_tag }}). Esta accion no se puede deshacer.
            </DialogDescription>
          </DialogHeader>

          <DialogFooter class="gap-2">
            <Button variant="secondary" :disabled="eliminando" @click="cerrarConfirmacion">
              Cancelar
            </Button>
            <Button variant="destructive" :disabled="eliminando" @click="confirmarEliminacion">
              {{ eliminando ? 'Eliminando...' : 'Si, eliminar' }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
