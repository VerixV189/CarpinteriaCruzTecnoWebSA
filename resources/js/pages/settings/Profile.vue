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
import { type BreadcrumbItem, type SharedData, type User } from '@/types';

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

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const form = useForm({
    nombre: user.nombre || '',
    apellido: user.apellido || '',
    telefono: user.telefono || '',
    email: user.email || '',
});

const submit = () => {
    form.patch(route('profile.update'), {
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
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="focus:outline-hidden rounded-md text-sm text-neutral-600 underline hover:text-neutral-900 focus:ring-2 focus:ring-offset-2"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
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
