<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { ChevronDown, FileText, Table, File } from 'lucide-vue-next';

interface Props {
    data: any[];
    headers: string[];
    // keys can be strings or custom functions that take the item and return a string/number
    keys: (string | ((item: any) => any))[];
    filename?: string;
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    filename: 'reporte',
    title: 'Reporte de Sistema'
});

const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = (e: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});

// Helper to extract value from item
const getValue = (item: any, key: string | ((item: any) => any)) => {
    if (typeof key === 'function') {
        return key(item);
    }
    // Deep access for dot notation (e.g. 'usuario.nombre')
    if (key.includes('.')) {
        return key.split('.').reduce((acc, part) => acc && acc[part], item) ?? '';
    }
    return item[key] ?? '';
};

// EXPORT TO TXT
const exportTxt = () => {
    let content = `${props.title.toUpperCase()}\n`;
    content += `Fecha de Generación: ${new Date().toLocaleString()}\n`;
    content += `${'='.repeat(60)}\n\n`;

    // Calculate column widths
    const colWidths = props.headers.map((h, index) => {
        let maxLen = h.length;
        props.data.forEach(item => {
            const val = String(getValue(item, props.keys[index]));
            if (val.length > maxLen) maxLen = val.length;
        });
        return maxLen + 3; // padding
    });

    // Write Headers
    props.headers.forEach((h, i) => {
        content += h.padEnd(colWidths[i]);
    });
    content += '\n';
    content += props.headers.map((_, i) => '-'.repeat(colWidths[i] - 3).padEnd(colWidths[i])).join('') + '\n';

    // Write Data
    props.data.forEach(item => {
        props.keys.forEach((key, i) => {
            const val = String(getValue(item, key));
            content += val.padEnd(colWidths[i]);
        });
        content += '\n';
    });

    content += `\n${'='.repeat(60)}\n`;
    content += `Total de registros: ${props.data.length}\n`;

    const blob = new Blob([content], { type: 'text/plain;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${props.filename}-${Date.now()}.txt`;
    link.click();
    isOpen.value = false;
};

// EXPORT TO EXCEL (CSV)
const exportExcel = () => {
    let csvContent = '\uFEFF'; // UTF-8 BOM for Excel to recognize accents/special symbols correctly
    csvContent += 'sep=;\n'; // Tell Excel we are using semicolon as delimiter

    // Headers
    const headersRow = props.headers.map(h => `"${h.replace(/"/g, '""')}"`).join(';');
    csvContent += headersRow + '\n';

    // Data rows
    props.data.forEach(item => {
        const row = props.keys.map(key => {
            const val = String(getValue(item, key));
            return `"${val.replace(/"/g, '""')}"`;
        }).join(';');
        csvContent += row + '\n';
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${props.filename}-${Date.now()}.csv`;
    link.click();
    isOpen.value = false;
};

// EXPORT TO PDF (HTML Print View)
const exportPdf = () => {
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    // Build modern styled HTML table for printing
    const html = `
    <html>
    <head>
        <title>${props.title}</title>
        <style>
            body { font-family: 'Segoe UI', Roboto, sans-serif; color: #333; margin: 40px; }
            .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #d97706; padding-bottom: 15px; margin-bottom: 30px; }
            .header h1 { margin: 0; font-size: 24px; color: #1e293b; }
            .header p { margin: 5px 0 0 0; font-size: 12px; color: #64748b; }
            .logo { font-weight: bold; color: #d97706; font-size: 18px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th { background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; text-align: left; padding: 12px 10px; font-size: 12px; text-transform: uppercase; }
            td { border-bottom: 1px solid #f1f5f9; padding: 12px 10px; font-size: 13px; color: #334155; }
            tr:hover { background-color: #f8fafc; }
            .footer { margin-top: 40px; font-size: 11px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 15px; }
            .summary { margin-top: 20px; font-size: 13px; font-weight: 600; text-align: right; color: #1e293b; }
        </style>
    </head>
    <body>
        <div class="header">
            <div>
                <h1>${props.title}</h1>
                <p>Generado el: ${new Date().toLocaleString()}</p>
            </div>
            <div class="logo">Mueblería Cruz</div>
        </div>

        <table>
            <thead>
                <tr>
                    ${props.headers.map(h => `<th>${h}</th>`).join('')}
                </tr>
            </thead>
            <tbody>
                ${props.data.map(item => `
                    <tr>
                        ${props.keys.map(key => `<td>${getValue(item, key)}</td>`).join('')}
                    </tr>
                `).join('')}
            </tbody>
        </table>

        <div class="summary">
            Total Registros: ${props.data.length}
        </div>

        <div class="footer">
            Este reporte fue generado de forma automatizada por el Sistema de Gestión de Mueblería Cruz S.A.
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
    isOpen.value = false;
};
</script>

<template>
    <div class="relative inline-block text-left" ref="dropdownRef">
        <button
            @click="toggleDropdown"
            class="inline-flex items-center justify-center gap-1.5 rounded-md bg-amber-600 dark:bg-amber-500 px-3 h-9 text-xs font-semibold text-white shadow-sm transition-all hover:bg-amber-700 dark:hover:bg-amber-600 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
            type="button"
        >
            <File class="h-3.5 w-3.5" />
            <span>Reporte</span>
            <ChevronDown class="h-3 w-3" />
        </button>

        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-48 origin-top-right rounded-md border border-sidebar-border bg-popover text-popover-foreground shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50 animate-in fade-in slide-in-from-top-1 duration-100"
        >
            <div class="py-1">
                <button
                    @click="exportTxt"
                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm hover:bg-accent hover:text-accent-foreground transition-colors"
                >
                    <FileText class="h-4 w-4 text-stone-500" />
                    <span>Reporte TXT</span>
                </button>
                <button
                    @click="exportExcel"
                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm hover:bg-accent hover:text-accent-foreground transition-colors"
                >
                    <Table class="h-4 w-4 text-emerald-600 dark:text-emerald-500" />
                    <span>Reporte Excel (CSV)</span>
                </button>
                <button
                    @click="exportPdf"
                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm hover:bg-accent hover:text-accent-foreground transition-colors"
                >
                    <FileText class="h-4 w-4 text-red-500" />
                    <span>Reporte PDF</span>
                </button>
            </div>
        </div>
    </div>
</template>
