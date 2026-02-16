<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const periodLabel = 'Enero 2026';

const hrMetrics = [
    {
        key: 'nuevos-ingresos',
        label: 'Nuevos ingresos',
        value: 24,
        helper: 'Personas incorporadas este mes',
        tone: 'text-emerald-600',
    },
    {
        key: 'retencion',
        label: 'Se mantienen',
        value: 20,
        helper: 'Colaboradores que siguen activos',
        tone: 'text-sky-600',
    },
    {
        key: 'capacitacion',
        label: 'Capacitacion finalizada',
        value: 15,
        helper: 'Finalizaron induccion y plan inicial',
        tone: 'text-indigo-600',
    },
    {
        key: 'rezagados',
        label: 'Rezagados',
        value: 5,
        helper: 'Pendientes por cerrar proceso',
        tone: 'text-amber-600',
    },
] as const;

const totalIngresos = computed(() => hrMetrics[0].value);

const funnel = computed(() => {
    const [newHires, retained, trained, delayed] = hrMetrics;
    const base = newHires.value || 1;

    return [
        {
            label: newHires.label,
            value: newHires.value,
            width: 100,
            color: 'bg-emerald-500/90',
        },
        {
            label: retained.label,
            value: retained.value,
            width: (retained.value / base) * 100,
            color: 'bg-sky-500/90',
        },
        {
            label: trained.label,
            value: trained.value,
            width: (trained.value / base) * 100,
            color: 'bg-indigo-500/90',
        },
        {
            label: delayed.label,
            value: delayed.value,
            width: (delayed.value / base) * 100,
            color: 'bg-amber-500/90',
        },
    ];
});
</script>

<template>
    <Head title="Dashboard - Atraccion de Talento" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <header
                class="rounded-xl border border-border bg-card p-5 shadow-sm"
            >
                <h1 class="text-xl font-semibold tracking-tight">
                    Recursos Humanos - Atraccion de Talento
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    KPI de referencia para seguimiento de reclutamiento e
                    incorporacion. Datos de maqueta para demostracion.
                </p>
                <div class="mt-3 text-xs text-muted-foreground">
                    Periodo: {{ periodLabel }}
                </div>
            </header>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article
                    v-for="metric in hrMetrics"
                    :key="metric.key"
                    class="rounded-xl border border-border bg-card p-4 shadow-sm"
                >
                    <p
                        class="text-[11px] font-medium uppercase tracking-wide text-muted-foreground"
                    >
                        {{ metric.label }}
                    </p>
                    <p class="mt-2 text-3xl font-semibold" :class="metric.tone">
                        {{ metric.value }}
                    </p>
                    <p class="mt-2 text-xs text-muted-foreground">
                        {{ metric.helper }}
                    </p>
                </article>
            </section>

            <section class="grid gap-4 lg:grid-cols-3">
                <article
                    class="rounded-xl border border-border bg-card p-5 shadow-sm lg:col-span-2"
                >
                    <h2
                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                    >
                        Avance del proceso
                    </h2>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="step in funnel"
                            :key="step.label"
                            class="space-y-1"
                        >
                            <div
                                class="flex items-center justify-between text-xs text-muted-foreground"
                            >
                                <span>{{ step.label }}</span>
                                <span>{{ step.value }}</span>
                            </div>
                            <div class="h-2.5 rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full transition-all"
                                    :class="step.color"
                                    :style="{
                                        width: `${Math.max(
                                            8,
                                            Math.min(100, step.width),
                                        )}%`,
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                </article>

                <article class="rounded-xl border border-border bg-card p-5 shadow-sm">
                    <h2
                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                    >
                        Resumen rapido
                    </h2>
                    <p class="mt-3 text-sm text-muted-foreground">
                        De {{ totalIngresos }} ingresos, {{ hrMetrics[1].value }}
                        se mantienen activos y {{ hrMetrics[2].value }}
                        completaron capacitacion.
                    </p>
                    <p class="mt-3 text-sm text-muted-foreground">
                        {{ hrMetrics[3].value }} personas permanecen como
                        rezagadas para seguimiento.
                    </p>
                </article>
            </section>
        </div>
    </AppLayout>
</template>
