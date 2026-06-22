<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    nombre: '',
    apellido: '',
    telefono: '',
    email: '',
    direccion: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Crea una cuenta" description="Ingresa tus datos para crear una cuenta">
        <Head title="Registrar" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="nombre">Nombre</Label>
                    <Input id="nombre" type="text" required autofocus tabindex="1" autocomplete="nombre" v-model="form.nombre" placeholder="Ingrese su nombre" />
                    <InputError :message="form.errors.nombre" />
                </div>

                <div class="grid gap-2">
                    <Label for="apellido">Apellido</Label>
                    <Input id="apellido" type="text" required autofocus tabindex="2" autocomplete="apellido" v-model="form.apellido" placeholder="Ingrese su apellido" />
                    <InputError :message="form.errors.apellido" />
                </div>
                
                <div class="grid gap-2">
                    <Label for="telefono">Telefono</Label>
                    <Input id="telefono" type="text" required autofocus tabindex="3" autocomplete="telefono" v-model="form.telefono" placeholder="+591 77630528" />
                    <InputError :message="form.errors.telefono" />
                </div>

                <div class="grid gap-2">
                    <Label for="direccion">Direccion</Label>
                    <Input id="direccion" type="text" required autofocus tabindex="4" autocomplete="direccion" v-model="form.direccion" placeholder="Ingrese su direccion" />
                    <InputError :message="form.errors.direccion" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" required tabindex="5" autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Contraseña</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        tabindex="6"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Contraseña"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirmar contraseña</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        tabindex="7"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Confirmar contraseña"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" tabindex="8" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Crear cuenta
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                ¿Ya tienes una cuenta?
                <TextLink :href="route('login')" class="underline underline-offset-4" tabindex="9">Iniciar sesión</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
