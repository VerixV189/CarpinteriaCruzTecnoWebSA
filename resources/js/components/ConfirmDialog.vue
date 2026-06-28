<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { AlertCircle, Trash2, AlertTriangle } from 'lucide-vue-next';

interface Props {
    open: boolean;
    title?: string;
    message?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'danger' | 'warning' | 'info';
}

withDefaults(defineProps<Props>(), {
    title: '¿Estás seguro?',
    message: 'Esta acción no se puede deshacer. ¿Deseas continuar?',
    confirmLabel: 'Eliminar',
    cancelLabel: 'Cancelar',
    variant: 'danger'
});

const emit = defineEmits(['update:open', 'confirm', 'cancel']);

const onConfirm = () => {
    emit('confirm');
    emit('update:open', false);
};

const onCancel = () => {
    emit('cancel');
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-md bg-white dark:bg-stone-900 border-stone-200 dark:border-stone-800">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-stone-900 dark:text-white">
                    <Trash2 v-if="variant === 'danger'" class="h-5 w-5 text-red-500" />
                    <AlertTriangle v-else-if="variant === 'warning'" class="h-5 w-5 text-amber-500" />
                    <AlertCircle v-else class="h-5 w-5 text-blue-500" />
                    {{ title }}
                </DialogTitle>
                <DialogDescription class="text-stone-500 dark:text-stone-400 mt-2">
                    {{ message }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="sm:justify-end mt-4">
                <Button 
                    type="button" 
                    variant="outline" 
                    @click="onCancel"
                    class="border-stone-200 dark:border-stone-700 hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300"
                >
                    {{ cancelLabel }}
                </Button>
                <Button 
                    type="button" 
                    :class="{
                        'bg-red-600 hover:bg-red-700 text-white': variant === 'danger',
                        'bg-amber-600 hover:bg-amber-700 text-white': variant === 'warning',
                        'bg-blue-600 hover:bg-blue-700 text-white': variant === 'info'
                    }"
                    @click="onConfirm"
                >
                    {{ confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
