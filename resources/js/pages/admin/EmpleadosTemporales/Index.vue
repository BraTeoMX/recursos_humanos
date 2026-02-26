<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { toast } from 'vue-sonner'
// ✅ Wayfinder — tipado y generado automáticamente desde las rutas de Laravel
import { store, destroy } from '@/routes/admin/empleados-temporales'

interface Empleado {
  id: number
  numero_tag: string
  nombre: string
  apellido_paterno: string
  apellido_materno: string | null
}

const props = defineProps<{
  empleados: Empleado[]
}>()

const form = useForm({
  numero_tag: '',
  nombre: '',
  apellido_paterno: '',
  apellido_materno: '',
})

function guardar() {
  // store().url → POST /admin/empleados-temporales
  form.post(store().url, {
    onSuccess: () => form.reset(),
  })
}

function eliminar(emp: Empleado) {
  toast.warning(`¿Eliminar a ${emp.nombre} ${emp.apellido_paterno}?`, {
    description: `Tag: ${emp.numero_tag} — Esta acción no se puede deshacer.`,
    duration: Infinity,
    action: {
      label: 'Sí, eliminar',
      // destroy(emp).url → DELETE /admin/empleados-temporales/{id}
      onClick: () => router.delete(destroy(emp).url),
    },
    cancel: {
      label: 'Cancelar',
      onClick: () => {},
    },
  })
}
</script>


<template>
  <Head title="Empleados Temporales" />
  <AppLayout>
    <div class="mx-auto max-w-5xl p-6 space-y-8">

      <!-- Encabezado -->
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Registro de Empleados Temporales</h1>
        <p class="text-sm text-gray-500 mt-1">Administra el catálogo de empleados con acceso por tag.</p>
      </div>


      <!-- Formulario de registro -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Nuevo Empleado</h2>
        <form @submit.prevent="guardar" class="grid grid-cols-1 md:grid-cols-2 gap-4">

          <!-- Número de Tag -->
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Número de Tag *</label>
            <input
              v-model="form.numero_tag"
              type="text"
              placeholder="Ej: TAG-0001"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
              :class="{ 'border-red-400': form.errors.numero_tag }"
            />
            <p v-if="form.errors.numero_tag" class="mt-1 text-xs text-red-500">{{ form.errors.numero_tag }}</p>
          </div>

          <!-- Nombre -->
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Nombre *</label>
            <input
              v-model="form.nombre"
              type="text"
              placeholder="Nombre(s)"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
              :class="{ 'border-red-400': form.errors.nombre }"
            />
            <p v-if="form.errors.nombre" class="mt-1 text-xs text-red-500">{{ form.errors.nombre }}</p>
          </div>

          <!-- Apellido Paterno -->
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Apellido Paterno *</label>
            <input
              v-model="form.apellido_paterno"
              type="text"
              placeholder="Apellido paterno"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
              :class="{ 'border-red-400': form.errors.apellido_paterno }"
            />
            <p v-if="form.errors.apellido_paterno" class="mt-1 text-xs text-red-500">{{ form.errors.apellido_paterno }}</p>
          </div>

          <!-- Apellido Materno -->
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Apellido Materno</label>
            <input
              v-model="form.apellido_materno"
              type="text"
              placeholder="Apellido materno (opcional)"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
            />
          </div>

          <!-- Botón -->
          <div class="md:col-span-2 flex justify-end">
            <button
              type="submit"
              :disabled="form.processing"
              class="bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition disabled:opacity-50"
            >
              {{ form.processing ? 'Guardando...' : 'Registrar Empleado' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Tabla de empleados registrados -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="text-lg font-semibold text-gray-700">Empleados Registrados
            <span class="ml-2 text-sm font-normal text-gray-400">({{ empleados.length }})</span>
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="text-left px-4 py-3 text-gray-600 font-medium">Tag</th>
                <th class="text-left px-4 py-3 text-gray-600 font-medium">Nombre</th>
                <th class="text-left px-4 py-3 text-gray-600 font-medium">Apellido Paterno</th>
                <th class="text-left px-4 py-3 text-gray-600 font-medium">Apellido Materno</th>
                <th class="text-center px-4 py-3 text-gray-600 font-medium">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="empleados.length === 0">
                <td colspan="5" class="text-center py-8 text-gray-400">No hay empleados registrados aún.</td>
              </tr>
              <tr
                v-for="emp in empleados"
                :key="emp.id"
                class="border-b border-gray-50 hover:bg-gray-50 transition"
              >
                <td class="px-4 py-3">
                  <span class="inline-flex items-center rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-medium text-teal-700 ring-1 ring-teal-200">
                    {{ emp.numero_tag }}
                  </span>
                </td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ emp.nombre }}</td>
                <td class="px-4 py-3 text-gray-700">{{ emp.apellido_paterno }}</td>
                <td class="px-4 py-3 text-gray-500">{{ emp.apellido_materno ?? '—' }}</td>
                <td class="px-4 py-3 text-center">
                  <button
                    @click="eliminar(emp)"
                    class="text-red-500 hover:text-red-700 text-xs font-medium transition"
                  >
                    Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
