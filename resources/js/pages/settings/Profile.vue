<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    className?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mi Perfil',
        href: route('profile.edit'),
    },
];

const page = usePage<any>();
const user = page.props.auth.user as User;

const form = useForm({
    _method: 'patch',
    nombre: user.nombre || '',
    apellido: user.apellido || '',
    telefono: user.telefono || '',
    email: user.email || '',
    foto: null as File | null,
});

import { ref } from 'vue';

const fotoPreview = ref<string | null>(null);

const onFileChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.foto = file;
        fotoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Mi Perfil" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Información del perfil" description="Actualiza tu nombre, apellido, teléfono y correo electrónico." />

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Foto de Perfil -->
                    <div class="flex items-center gap-6 pb-6 border-b border-stone-200 dark:border-stone-850">
                        <div class="relative">
                            <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-amber-500 bg-stone-100 dark:bg-stone-800 flex items-center justify-center">
                                <img v-if="fotoPreview || user.foto" :src="fotoPreview || user.foto" class="w-full h-full object-cover" />
                                <span v-else class="text-stone-500 font-bold text-xl uppercase">{{ (user.nombre || '')[0] || '' }}{{ (user.apellido || '')[0] || '' }}</span>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="foto" class="cursor-pointer bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition-all inline-block shadow-sm">
                                Cambiar Foto
                            </Label>
                            <input 
                                id="foto" 
                                type="file" 
                                class="hidden" 
                                accept="image/*"
                                @change="onFileChange" 
                            />
                            <p class="text-[10px] text-muted-foreground">Formatos admitidos: JPG, PNG. Máximo 2MB.</p>
                            <InputError class="mt-1" :message="form.errors.foto" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="nombre">Nombre</Label>
                            <Input id="nombre" class="mt-1 block w-full" v-model="form.nombre" required autocomplete="given-name" placeholder="Nombre" />
                            <InputError class="mt-2" :message="form.errors.nombre" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="apellido">Apellido</Label>
                            <Input id="apellido" class="mt-1 block w-full" v-model="form.apellido" required autocomplete="family-name" placeholder="Apellido" />
                            <InputError class="mt-2" :message="form.errors.apellido" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="telefono">Teléfono</Label>
                        <Input id="telefono" class="mt-1 block w-full" v-model="form.telefono" required autocomplete="tel" placeholder="Teléfono" />
                        <InputError class="mt-2" :message="form.errors.telefono" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Dirección de correo electrónico</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full bg-stone-100 dark:bg-stone-850/50 cursor-not-allowed text-stone-500"
                            v-model="form.email"
                            required
                            disabled
                            autocomplete="username"
                            placeholder="Correo electrónico"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="mt-2 text-sm text-neutral-800">
                            Tu dirección de correo electrónico no está verificada.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="focus:outline-hidden rounded-md text-sm text-neutral-600 underline hover:text-neutral-900 focus:ring-2 focus:ring-offset-2"
                            >
                                Haz clic aquí para reenviar el correo electrónico de verificación.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Guardar</Button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="text-sm text-neutral-600">Guardado.</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
