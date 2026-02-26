<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast, Toaster } from 'vue-sonner';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

// ─────────────────────────────────────────────────────────────────────────────
// Sistema de notificaciones toast global
// Escucha los mensajes flash de Laravel en cada navegación de Inertia
// ─────────────────────────────────────────────────────────────────────────────
const page = usePage();

watch(
    () => page.props.flash as { success?: string; error?: string; warning?: string },
    (flash) => {
        if (flash?.success) toast.success(flash.success, { duration: 4000 });
        if (flash?.error)   toast.error(flash.error,   { duration: 5000 });
        if (flash?.warning) toast.warning(flash.warning, { duration: 4500 });
    },
    { deep: true },
);
</script>

<template>
    <AppShell variant="sidebar">
        <!-- Toaster global: aparece en todas las páginas del layout autenticado -->
        <Toaster
            position="top-right"
            :rich-colors="true"
            :close-button="true"
            :expand="true"
        />
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>

