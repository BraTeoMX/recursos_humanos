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

// Datos simulados de laboratorio textil
const summary = {
    productionToday: 12850, // metros de tela
    productionTarget: 12000,
    qualityIndex: 97.4, // %
    machinesRunning: 42,
    machinesTotal: 50,
    ordersInProgress: 18,
};

const kpis = [
    {
        label: 'Eficiencia de producción',
        value: 107,
        unit: '% del objetivo',
        trend: '+3.2 vs ayer',
    },
    {
        label: 'Tasa de defectos',
        value: 1.8,
        unit: '%',
        trend: '-0.4 vs promedio semanal',
    },
    {
        label: 'OEE promedio',
        value: 86.5,
        unit: '%',
        trend: '+1.1 vs mes pasado',
    },
    {
        label: 'Entrega a tiempo',
        value: 93.0,
        unit: '%',
        trend: '+0.8 vs trimestre anterior',
    },
] as const;

const trends = [
    { label: 'Lun', value: 11200 },
    { label: 'Mar', value: 11850 },
    { label: 'Mié', value: 12100 },
    { label: 'Jue', value: 11950 },
    { label: 'Vie', value: 12500 },
    { label: 'Sáb', value: 9800 },
] as const;

const maxTrendValue = computed(() => {
    if (!trends.length) return 0;
    return Math.max(...trends.map((t) => t.value));
});

const productionProgress = computed(() => {
    if (!summary.productionTarget) return 0;

    return Math.min(
        120,
        (summary.productionToday / summary.productionTarget) * 100,
    );
});

const machinesUsage = computed(() => {
    if (!summary.machinesTotal) return 0;

    return (summary.machinesRunning / summary.machinesTotal) * 100;
});
</script>

<template>
    <Head title="Dashboard – KPIs laboratorio" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Encabezado -->
            <header
                class="flex flex-col gap-3 border-b border-border pb-4 md:flex-row md:items-end md:justify-between dark:border-sidebar-border"
            >
                <div>
                    <h1 class="text-lg font-semibold tracking-tight">
                        Panel de KPIs – Laboratorio Textil
                    </h1>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Resumen de producción, calidad y utilización de máquinas
                        (datos simulados).
                    </p>
                </div>
                <div class="flex flex-wrap gap-2 text-[11px] text-muted-foreground">
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-background px-3 py-1 ring-1 ring-border"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                        Datos de ejemplo
                    </span>
                    <span
                        class="rounded-full bg-background px-3 py-1 ring-1 ring-border"
                    >
                        Actualizado: hace unos segundos
                    </span>
                </div>
            </header>

            <!-- Tarjetas resumen -->
            <section class="grid gap-4 md:grid-cols-4">
                <div
                    class="rounded-xl border border-border bg-card p-4 shadow-sm dark:border-sidebar-border"
                >
                    <p
                        class="text-[11px] font-medium uppercase tracking-wide text-muted-foreground"
                    >
                        Producción de hoy
                    </p>
                    <p class="mt-2 text-2xl font-semibold">
                        {{ summary.productionToday.toLocaleString('es-PE') }} m
                    </p>
                    <p class="mt-1 text-[11px] text-muted-foreground">
                        Objetivo:
                        {{ summary.productionTarget.toLocaleString('es-PE') }} m
                    </p>
                    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-emerald-500"
                            :style="{ width: `${productionProgress}%` }"
                        />
                    </div>
                </div>

                <div
                    class="rounded-xl border border-border bg-card p-4 shadow-sm dark:border-sidebar-border"
                >
                    <p
                        class="text-[11px] font-medium uppercase tracking-wide text-muted-foreground"
                    >
                        Índice de calidad
                    </p>
                    <p class="mt-2 text-2xl font-semibold text-emerald-500">
                        {{ summary.qualityIndex.toFixed(1) }}%
                    </p>
                    <p class="mt-1 text-[11px] text-muted-foreground">
                        Porcentaje de rollos que pasan control de calidad.
                    </p>
                </div>

                <div
                    class="rounded-xl border border-border bg-card p-4 shadow-sm dark:border-sidebar-border"
                >
                    <p
                        class="text-[11px] font-medium uppercase tracking-wide text-muted-foreground"
                    >
                        Máquinas activas
                    </p>
                    <p class="mt-2 text-2xl font-semibold">
                        {{ summary.machinesRunning }} /
                        {{ summary.machinesTotal }}
                    </p>
                    <p class="mt-1 text-[11px] text-muted-foreground">
                        Utilización aproximada:
                        <span class="font-medium">
                            {{ machinesUsage.toFixed(0) }}%
                        </span>
                    </p>
                </div>

                <div
                    class="rounded-xl border border-border bg-card p-4 shadow-sm dark:border-sidebar-border"
                >
                    <p
                        class="text-[11px] font-medium uppercase tracking-wide text-muted-foreground"
                    >
                        Órdenes en proceso
                    </p>
                    <p class="mt-2 text-2xl font-semibold">
                        {{ summary.ordersInProgress }}
                    </p>
                    <p class="mt-1 text-[11px] text-muted-foreground">
                        Procesos activos en laboratorio.
                    </p>
                </div>
            </section>

            <!-- KPIs + gráfica -->
            <section class="grid flex-1 gap-4 lg:grid-cols-3">
                <!-- KPIs detallados -->
                <div class="space-y-3 lg:col-span-1">
                    <h2
                        class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                    >
                        Indicadores clave
                    </h2>

                    <div class="space-y-3">
                        <article
                            v-for="kpi in kpis"
                            :key="kpi.label"
                            class="flex items-center justify-between gap-3 rounded-xl border border-border bg-card p-4 text-sm shadow-sm dark:border-sidebar-border"
                        >
                            <div>
                                <p
                                    class="text-[11px] font-medium uppercase tracking-wide text-muted-foreground"
                                >
                                    {{ kpi.label }}
                                </p>
                                <p class="mt-1 text-lg font-semibold">
                                    {{ kpi.value }}
                                    <span
                                        class="text-[11px] font-normal text-muted-foreground"
                                    >
                                        {{ kpi.unit }}
                                    </span>
                                </p>
                                <p
                                    class="mt-1 text-[11px]"
                                    :class="
                                        kpi.trend.includes('-')
                                            ? 'text-emerald-500'
                                            : 'text-amber-500'
                                    "
                                >
                                    {{ kpi.trend }}
                                </p>
                            </div>
                            <div
                                class="ml-4 h-10 w-10 rounded-full bg-muted/60"
                            />
                        </article>
                    </div>
                </div>

                <!-- Gráfica de barras simple -->
                <div class="lg:col-span-2">
                    <h2
                        class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                    >
                        Producción diaria (m de tela)
                    </h2>
                    <div
                        class="flex h-full flex-col rounded-2xl border border-border bg-card p-4 shadow-sm dark:border-sidebar-border"
                    >
                        <div
                            class="flex flex-1 items-end justify-between gap-3 border-b border-border pb-4 text-[11px] dark:border-sidebar-border"
                        >
                            <div
                                v-for="item in trends"
                                :key="item.label"
                                class="flex flex-1 flex-col items-center gap-1"
                            >
                                <div
                                    class="flex w-full items-end justify-center rounded-t-md bg-gradient-to-t from-sky-500/10 to-sky-500/70"
                                    :style="{
                                        height: maxTrendValue
                                            ? `${Math.max(
                                                  8,
                                                  (item.value /
                                                      maxTrendValue) * 100,
                                              )}%`
                                            : '0%',
                                    }"
                                >
                                    <span
                                        class="mb-1 text-[10px] font-medium text-sky-950/80 dark:text-sky-100"
                                    >
                                        {{ item.value.toLocaleString('es-PE') }}
                                    </span>
                                </div>
                                <span
                                    class="text-[11px] font-medium text-muted-foreground"
                                >
                                    {{ item.label }}
                                </span>
                            </div>
                        </div>
                        <div
                            class="mt-3 flex items-center justify-between text-[11px] text-muted-foreground"
                        >
                            <span>Semana actual (simulada)</span>
                            <span>Laboratorio de pruebas textiles</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
