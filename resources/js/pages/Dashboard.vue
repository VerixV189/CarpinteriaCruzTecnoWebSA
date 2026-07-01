<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Users, Package, ClipboardList, Banknote, TrendingUp, Coins, BarChart3, RefreshCw, File, FileText, Table, ChevronDown } from 'lucide-vue-next';
import { computed } from 'vue';

interface Stats {
    clientes: number;
    productos: number;
    pedidos: number;
    ingresos: number;
}

interface RecursoPopular {
    modelo_tipo: string;
    total: number;
}

interface VentaPorTipo {
    tipo: string;
    total: number;
}

interface PagoPorEstado {
    estado: string;
    total: number;
}

interface VentaReciente {
    id: number;
    codigo: string;
    total_costo: string | number;
    fecha_entregado: string;
    tipo: string;
    pedido?: {
        id: number;
        cliente?: {
            id: number;
            usuario?: {
                id: number;
                nombre: string;
                apellido: string;
            }
        }
    }
}

interface Reportes {
    ventas_por_tipo: VentaPorTipo[];
    pagos_por_estado: PagoPorEstado[];
    ventas_recientes: VentaReciente[];
}

const props = defineProps<{
    stats: Stats;
    recursos_populares: RecursoPopular[];
    reportes: Reportes;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
    },
];

// Helper to calculate percentages
const maxVentaTipo = computed(() => {
    if (!props.reportes?.ventas_por_tipo?.length) return 1;
    return Math.max(...props.reportes.ventas_por_tipo.map(v => v.total));
});

const maxPagoEstado = computed(() => {
    if (!props.reportes?.pagos_por_estado?.length) return 1;
    return Math.max(...props.reportes.pagos_por_estado.map(p => p.total));
});

import { router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const isRefreshing = ref(false);
const refreshPage = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['stats', 'recursos_populares', 'reportes'],
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

const isReportOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const toggleReportDropdown = () => {
    isReportOpen.value = !isReportOpen.value;
};

const closeReportDropdown = (e: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        isReportOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeReportDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeReportDropdown);
});

const exportDashboardTxt = () => {
    let content = `REPORTE EJECUTIVO DE CONTROL - MUEBLERÍA CRUZ\n`;
    content += `Fecha de Generación: ${new Date().toLocaleString()}\n`;
    content += `${'='.repeat(60)}\n\n`;

    content += `RESUMEN GENERAL:\n`;
    content += `- Clientes Registrados: ${props.stats.clientes}\n`;
    content += `- Productos en Catálogo: ${props.stats.productos}\n`;
    content += `- Pedidos Activos: ${props.stats.pedidos}\n`;
    content += `- Ingresos Totales: Bs. ${props.stats.ingresos.toFixed(2)}\n\n`;

    content += `VENTAS POR TIPO DE PAGO:\n`;
    props.reportes.ventas_por_tipo.forEach(item => {
        content += `- ${item.tipo}: ${item.total} ventas\n`;
    });
    content += `\n`;

    content += `ESTADO DE PAGOS:\n`;
    props.reportes.pagos_por_estado.forEach(item => {
        content += `- ${item.estado}: ${item.total} pagos\n`;
    });
    content += `\n`;

    content += `RECURSOS MÁS ACCEDIDOS (TOP 5):\n`;
    props.recursos_populares.forEach((recurso, index) => {
        content += `- #${index + 1} ${recurso.modelo_tipo}: ${recurso.total} visitas\n`;
    });
    content += `\n`;

    content += `ÚLTIMAS VENTAS REALIZADAS:\n`;
    props.reportes.ventas_recientes.forEach(venta => {
        const cliente = venta.pedido?.cliente?.usuario ? `${venta.pedido.cliente.usuario.nombre} ${venta.pedido.cliente.usuario.apellido}` : 'Cliente Registrado';
        content += `- #${venta.codigo} | ${cliente} | ${venta.fecha_entregado} | ${venta.tipo} | Bs. ${parseFloat(String(venta.total_costo)).toFixed(2)}\n`;
    });

    const blob = new Blob([content], { type: 'text/plain;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `reporte-dashboard-${Date.now()}.txt`;
    link.click();
    isReportOpen.value = false;
};

const exportDashboardExcel = () => {
    let csvContent = '\uFEFF';
    csvContent += 'sep=;\n';

    csvContent += '"SECCIÓN";"MÉTRICA";"VALOR"\n';
    csvContent += `"Resumen General";"Clientes Registrados";"${props.stats.clientes}"\n`;
    csvContent += `"Resumen General";"Productos en Catálogo";"${props.stats.productos}"\n`;
    csvContent += `"Resumen General";"Pedidos Activos";"${props.stats.pedidos}"\n`;
    csvContent += `"Resumen General";"Ingresos Totales";"Bs. ${props.stats.ingresos.toFixed(2)}"\n\n`;

    csvContent += '"SECCIÓN";"TIPO/ESTADO";"CANTIDAD"\n';
    props.reportes.ventas_por_tipo.forEach(item => {
        csvContent += `"Ventas por Tipo";"${item.tipo}";"${item.total} ventas"\n`;
    });
    props.reportes.pagos_por_estado.forEach(item => {
        csvContent += `"Estado de Pagos";"${item.estado}";"${item.total} pagos"\n`;
    });
    props.recursos_populares.forEach((recurso, index) => {
        csvContent += `"Recursos Populares";"#${index + 1} ${recurso.modelo_tipo}";"${recurso.total} visitas"\n`;
    });
    csvContent += '\n';

    csvContent += '"ÚLTIMAS VENTAS";"CÓDIGO";"CLIENTE";"FECHA";"TIPO";"TOTAL"\n';
    props.reportes.ventas_recientes.forEach(venta => {
        const cliente = venta.pedido?.cliente?.usuario ? `${venta.pedido.cliente.usuario.nombre} ${venta.pedido.cliente.usuario.apellido}` : 'Cliente Registrado';
        csvContent += `"Venta";"#${venta.codigo}";"${cliente}";"${venta.fecha_entregado}";"${venta.tipo}";"Bs. ${parseFloat(String(venta.total_costo)).toFixed(2)}"\n`;
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `reporte-dashboard-${Date.now()}.csv`;
    link.click();
    isReportOpen.value = false;
};

const exportDashboardPdf = () => {
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    const html = `
    <html>
    <head>
        <title>Reporte Ejecutivo de Control</title>
        <style>
            body { font-family: 'Segoe UI', Roboto, sans-serif; color: #333; margin: 40px; }
            .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #d97706; padding-bottom: 15px; margin-bottom: 30px; }
            .header h1 { margin: 0; font-size: 24px; color: #1e293b; }
            .header p { margin: 5px 0 0 0; font-size: 12px; color: #64748b; }
            .logo { font-weight: bold; color: #d97706; font-size: 18px; }
            
            .stats-grid { display: grid; grid-template-cols: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
            .stat-card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; background: #f8fafc; }
            .stat-title { font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 5px; }
            .stat-value { font-size: 18px; font-weight: bold; color: #0f172a; }

            .section-title { font-size: 16px; font-weight: bold; color: #1e293b; margin-top: 30px; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; }
            
            .flex-sections { display: flex; gap: 30px; margin-bottom: 30px; }
            .flex-section { flex: 1; }
            .list-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
            .list-item span:first-child { text-transform: capitalize; color: #475569; }
            .list-item span:last-child { font-weight: bold; color: #0f172a; }

            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th { background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; text-align: left; padding: 10px; font-size: 11px; text-transform: uppercase; }
            td { border-bottom: 1px solid #f1f5f9; padding: 10px; font-size: 13px; color: #334155; }
            
            .footer { margin-top: 50px; font-size: 11px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 15px; }
        </style>
    </head>
    <body>
        <div class="header">
            <div>
                <h1>Reporte Ejecutivo de Control</h1>
                <p>Generado el: ${new Date().toLocaleString()}</p>
            </div>
            <div class="logo">Mueblería Cruz</div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Clientes Registrados</div>
                <div class="stat-value">${props.stats.clientes}</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Productos en Catálogo</div>
                <div class="stat-value">${props.stats.productos}</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Pedidos Activos</div>
                <div class="stat-value">${props.stats.pedidos}</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Ingresos Totales</div>
                <div class="stat-value">Bs. ${props.stats.ingresos.toFixed(2)}</div>
            </div>
        </div>

        <div class="flex-sections">
            <div class="flex-section">
                <div class="section-title">Ventas por Tipo de Pago</div>
                \${props.reportes.ventas_por_tipo.map(item => \`
                    <div class="list-item">
                        <span>\${item.tipo}</span>
                        <span>\${item.total} ventas</span>
                    </div>
                \`).join('')}
            </div>
            <div class="flex-section">
                <div class="section-title">Estado de Pagos</div>
                \${props.reportes.pagos_por_estado.map(item => \`
                    <div class="list-item">
                        <span>\${item.estado}</span>
                        <span>\${item.total} pagos</span>
                    </div>
                \`).join('')}
            </div>
            <div class="flex-section">
                <div class="section-title">Recursos Más Accedidos (Top 5)</div>
                \${props.recursos_populares.map((recurso, index) => \`
                    <div class="list-item">
                        <span>#\${index + 1} \${recurso.modelo_tipo}</span>
                        <span>\${recurso.total} visitas</span>
                    </div>
                \`).join('')}
            </div>
        </div>

        <div class="section-title">Últimas Ventas Realizadas</div>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                \${props.reportes.ventas_recientes.map(venta => \`
                    <tr>
                        <td style="font-weight: 600; color: #d97706;">#\${venta.codigo}</td>
                        <td>\${venta.pedido?.cliente?.usuario ? \`\${venta.pedido.cliente.usuario.nombre} \${venta.pedido.cliente.usuario.apellido}\` : 'Cliente Registrado'}</td>
                        <td>\${venta.fecha_entregado}</td>
                        <td style="text-transform: capitalize;">\${venta.tipo}</td>
                        <td style="text-align: right; font-weight: bold;">Bs. \${parseFloat(String(venta.total_costo)).toFixed(2)}</td>
                    </tr>
                \`).join('')}
            </tbody>
        </table>

        <div class="footer">
            Este reporte consolidado fue generado de forma automática por el Sistema de Gestión de Mueblería Cruz S.A.
        </div>

        <script>
            window.onload = function() {
                window.print();
                setTimeout(function() { window.close(); }, 500);
            };
        <\/script>
    </body>
    </html>
    `;

    printWindow.document.write(html);
    printWindow.document.close();
    isReportOpen.value = false;
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <!-- Header Section -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-stone-900 dark:text-white">Panel de Control</h1>
                    <p class="text-sm text-stone-500 dark:text-stone-400">Resumen y estado actual de Mueblería Cruz.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="refreshPage"
                        :disabled="isRefreshing"
                        class="inline-flex items-center justify-center gap-1.5 rounded-md border border-input bg-background px-3 h-9 text-xs font-medium text-stone-600 dark:text-stone-300 shadow-sm transition-colors hover:bg-stone-50 hover:text-stone-900 dark:hover:bg-stone-850 dark:hover:text-white focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:opacity-50"
                        title="Refrescar datos"
                    >
                        <RefreshCw class="h-3.5 w-3.5" :class="{ 'animate-spin': isRefreshing }" />
                        <span>Refrescar</span>
                    </button>

                    <div class="relative inline-block text-left" ref="dropdownRef">
                        <button
                            @click="toggleReportDropdown"
                            class="inline-flex items-center justify-center gap-1.5 rounded-md bg-amber-600 dark:bg-amber-500 px-3 h-9 text-xs font-semibold text-white shadow-sm transition-all hover:bg-amber-700 dark:hover:bg-amber-600 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            type="button"
                        >
                            <File class="h-3.5 w-3.5" />
                            <span>Reporte</span>
                            <ChevronDown class="h-3 w-3" />
                        </button>

                        <div
                            v-if="isReportOpen"
                            class="absolute right-0 mt-2 w-48 origin-top-right rounded-md border border-sidebar-border bg-popover text-popover-foreground shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50 animate-in fade-in slide-in-from-top-1 duration-100"
                        >
                            <div class="py-1">
                                <button
                                    @click="exportDashboardTxt"
                                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm hover:bg-accent hover:text-accent-foreground transition-colors"
                                >
                                    <FileText class="h-4 w-4 text-stone-500" />
                                    <span>Reporte TXT</span>
                                </button>
                                <button
                                    @click="exportDashboardExcel"
                                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm hover:bg-accent hover:text-accent-foreground transition-colors"
                                >
                                    <Table class="h-4 w-4 text-emerald-600 dark:text-emerald-500" />
                                    <span>Reporte Excel (CSV)</span>
                                </button>
                                <button
                                    @click="exportDashboardPdf"
                                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm hover:bg-accent hover:text-accent-foreground transition-colors"
                                >
                                    <FileText class="h-4 w-4 text-red-500" />
                                    <span>Reporte PDF</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Clientes Card -->
                <div class="rounded-xl border border-sidebar-border bg-card p-6 shadow-sm text-card-foreground">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <h3 class="text-sm font-medium text-muted-foreground">Clientes</h3>
                        <Users class="h-4 w-4 text-amber-600 dark:text-amber-500" />
                    </div>
                    <div class="flex items-baseline justify-between">
                        <div class="text-2xl font-bold">{{ stats.clientes }}</div>
                        <span class="text-xs text-muted-foreground">Registrados</span>
                    </div>
                </div>

                <!-- Productos Card -->
                <div class="rounded-xl border border-sidebar-border bg-card p-6 shadow-sm text-card-foreground">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <h3 class="text-sm font-medium text-muted-foreground">Productos</h3>
                        <Package class="h-4 w-4 text-amber-600 dark:text-amber-500" />
                    </div>
                    <div class="flex items-baseline justify-between">
                        <div class="text-2xl font-bold">{{ stats.productos }}</div>
                        <span class="text-xs text-muted-foreground">En catálogo</span>
                    </div>
                </div>

                <!-- Pedidos Card -->
                <div class="rounded-xl border border-sidebar-border bg-card p-6 shadow-sm text-card-foreground">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <h3 class="text-sm font-medium text-muted-foreground">Pedidos Activos</h3>
                        <ClipboardList class="h-4 w-4 text-amber-600 dark:text-amber-500" />
                    </div>
                    <div class="flex items-baseline justify-between">
                        <div class="text-2xl font-bold">{{ stats.pedidos }}</div>
                        <span class="text-xs text-muted-foreground">En producción</span>
                    </div>
                </div>

                <!-- Ingresos Card -->
                <div class="rounded-xl border border-sidebar-border bg-card p-6 shadow-sm text-card-foreground">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <h3 class="text-sm font-medium text-muted-foreground">Ingresos Totales</h3>
                        <Banknote class="h-4 w-4 text-emerald-600 dark:text-emerald-500" />
                    </div>
                    <div class="flex items-baseline justify-between">
                        <div class="text-2xl font-bold">Bs. {{ stats.ingresos.toFixed(2) }}</div>
                        <span class="text-xs text-muted-foreground">Por ventas</span>
                    </div>
                </div>
            </div>

            <!-- Section: Reportes y Estadísticas -->
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-stone-900 dark:text-white">Estadísticas</h2>
                    <p class="text-sm text-stone-500 dark:text-stone-400">Información detallada de facturación, ventas y pagos registrados en el sistema.</p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Ventas por Tipo -->
                    <div class="rounded-2xl border border-sidebar-border bg-card p-6 shadow-sm text-card-foreground">
                        <div class="flex items-center gap-2 mb-4">
                            <TrendingUp class="h-5 w-5 text-amber-600 dark:text-amber-500" />
                            <h3 class="font-bold text-stone-900 dark:text-white text-base">Ventas por Tipo de Pago</h3>
                        </div>
                        <div class="space-y-4">
                            <div v-for="item in reportes.ventas_por_tipo" :key="item.tipo" class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-stone-700 dark:text-stone-300 capitalize">{{ item.tipo }}</span>
                                    <span class="font-bold text-stone-900 dark:text-white">{{ item.total }} {{ item.total === 1 ? 'venta' : 'ventas' }}</span>
                                </div>
                                <div class="h-2 w-full bg-stone-100 dark:bg-stone-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-amber-500 rounded-full transition-all duration-500" :style="{ width: `${(item.total / maxVentaTipo) * 100}%` }"></div>
                                </div>
                            </div>
                            <div v-if="!reportes.ventas_por_tipo || reportes.ventas_por_tipo.length === 0" class="text-sm text-stone-500 text-center py-6">
                                Sin registros de ventas por tipo.
                            </div>
                        </div>
                    </div>

                    <!-- Estado de Pagos -->
                    <div class="rounded-2xl border border-sidebar-border bg-card p-6 shadow-sm text-card-foreground">
                        <div class="flex items-center gap-2 mb-4">
                            <Coins class="h-5 w-5 text-amber-600 dark:text-amber-500" />
                            <h3 class="font-bold text-stone-900 dark:text-white text-base">Estado de Pagos</h3>
                        </div>
                        <div class="space-y-4">
                            <div v-for="item in reportes.pagos_por_estado" :key="item.estado" class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-stone-700 dark:text-stone-300 capitalize">{{ item.estado }}</span>
                                    <span class="font-bold text-stone-900 dark:text-white">{{ item.total }} {{ item.total === 1 ? 'pago' : 'pagos' }}</span>
                                </div>
                                <div class="h-2 w-full bg-stone-100 dark:bg-stone-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" :style="{ width: `${(item.total / maxPagoEstado) * 100}%` }"></div>
                                </div>
                            </div>
                            <div v-if="!reportes.pagos_por_estado || reportes.pagos_por_estado.length === 0" class="text-sm text-stone-500 text-center py-6">
                                Sin registros de pagos.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimas Ventas Realizadas Table -->
                <div class="rounded-2xl border border-sidebar-border bg-card p-6 shadow-sm text-card-foreground">
                    <div class="flex items-center gap-2 mb-4">
                        <BarChart3 class="h-5 w-5 text-amber-600 dark:text-amber-500" />
                        <h3 class="font-bold text-stone-900 dark:text-white text-base">Últimas Ventas Realizadas</h3>
                    </div>
                    <div class="relative w-full overflow-auto">
                        <table class="w-full caption-bottom text-sm">
                            <thead>
                                <tr class="text-left font-medium text-stone-500 dark:text-stone-400 border-b border-sidebar-border/85 pb-2">
                                    <th class="p-3 pl-0">Código</th>
                                    <th class="p-3">Cliente</th>
                                    <th class="p-3">Fecha Entregado</th>
                                    <th class="p-3">Tipo de Pago</th>
                                    <th class="p-3 text-right pr-0">Total Cobrado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-sidebar-border/50">
                                <tr v-for="venta in reportes.ventas_recientes" :key="venta.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="p-3 pl-0 font-medium text-amber-600 dark:text-amber-500">#{{ venta.codigo }}</td>
                                    <td class="p-3">
                                        {{ venta.pedido?.cliente?.usuario ? `${venta.pedido.cliente.usuario.nombre} ${venta.pedido.cliente.usuario.apellido}` : 'Cliente Registrado' }}
                                    </td>
                                    <td class="p-3 text-stone-500 dark:text-stone-400">{{ venta.fecha_entregado }}</td>
                                    <td class="p-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-stone-100 text-stone-850 dark:bg-stone-850 dark:text-stone-300 capitalize">
                                            {{ venta.tipo }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right pr-0 font-semibold text-stone-900 dark:text-white">
                                        Bs. {{ parseFloat(String(venta.total_costo)).toFixed(2) }}
                                    </td>
                                </tr>
                                <tr v-if="!reportes.ventas_recientes || reportes.ventas_recientes.length === 0">
                                    <td colspan="5" class="p-4 text-center text-stone-500 py-8">
                                        No se han registrado ventas en el sistema todavía.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recursos Más Populares -->
            <div class="rounded-2xl border border-sidebar-border bg-card p-6 shadow-sm text-card-foreground">
                <h3 class="font-bold text-stone-900 dark:text-white mb-4">Recursos Más Accedidos (Top 5)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="(recurso, index) in recursos_populares" :key="index" class="flex items-center justify-between p-4 rounded-xl border border-sidebar-border hover:bg-muted/30 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-400 font-bold text-sm">
                                #{{ index + 1 }}
                            </div>
                            <span class="text-sm font-semibold">{{ recurso.modelo_tipo }}</span>
                        </div>
                        <span class="text-sm font-medium text-muted-foreground">{{ recurso.total }} visitas</span>
                    </div>
                    <div v-if="!recursos_populares || recursos_populares.length === 0" class="col-span-full text-sm text-muted-foreground text-center py-6 border border-dashed rounded-xl">
                        Aún no hay suficientes registros en la bitácora.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
