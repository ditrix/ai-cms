<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const props = withDefaults(
    defineProps<{
        canRegister: boolean;
        clientsCount: number;
        status?: string;
        canResetPassword?: boolean;
    }>(),
    {
        canRegister: false,
        clientsCount: 0,
        canResetPassword: true,
    },
);

const isAuthenticated = computed(() => !!page.props.auth.user);
</script>

<template>
    <Head title="AI CMS - Система управления клиентами">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div
        class="flex min-h-screen flex-col items-center justify-center bg-white p-6 text-neutral-900 dark:bg-neutral-950 dark:text-neutral-50 lg:p-8"
    >
        <!-- Main Content -->
        <div class="w-full max-w-md space-y-8">
            <!-- Project Info -->
            <div class="space-y-6 text-center">
                <div class="space-y-2">
                    <h1 class="text-3xl font-semibold tracking-tight">
                        AI CMS
                    </h1>
                    <p class="text-base text-neutral-600 dark:text-neutral-400">
                        Система управления клиентами
                    </p>
                </div>

                <div class="space-y-3 rounded-2xl bg-neutral-50 p-6 dark:bg-neutral-900">
                    <div class="space-y-2 text-sm">
                        <p class="font-medium text-neutral-900 dark:text-neutral-50">
                            Назначение
                        </p>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Управление клиентской базой и менеджерами для
                            эффективной работы с клиентами
                        </p>
                    </div>

                    <div class="space-y-2 text-sm">
                        <p class="font-medium text-neutral-900 dark:text-neutral-50">
                            Цель проекта
                        </p>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Исследование возможностей вебкодинга и современных
                            технологий разработки
                        </p>
                    </div>

                    <div class="space-y-2 text-sm">
                        <p class="font-medium text-neutral-900 dark:text-neutral-50">
                            Зарегистрировано клиентов
                        </p>
                        <p class="text-2xl font-semibold text-neutral-900 dark:text-neutral-50">
                            {{ clientsCount }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Login Form -->
            <div v-if="!isAuthenticated" class="space-y-6">
                <div
                    v-if="status"
                    class="rounded-xl bg-green-50 p-4 text-center text-sm font-medium text-green-700 dark:bg-green-950 dark:text-green-400"
                >
                    {{ status }}
                </div>

                <div class="space-y-1 text-center">
                    <h2 class="text-xl font-semibold">Вход в систему</h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Введите email и пароль для входа
                    </p>
                </div>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="space-y-5"
                >
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="email@example.com"
                                class="h-12 rounded-xl border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900"
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <Label for="password">Пароль</Label>
                                <a
                                    v-if="canResetPassword"
                                    :href="request().url"
                                    class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                >
                                    Забыли пароль?
                                </a>
                </div>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Пароль"
                                class="h-12 rounded-xl border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="flex items-center space-x-3">
                            <Checkbox id="remember" name="remember" />
                            <Label
                                for="remember"
                                class="text-sm font-normal text-neutral-700 dark:text-neutral-300"
                            >
                                Запомнить меня
                            </Label>
                        </div>

                        <Button
                            type="submit"
                            class="h-12 w-full rounded-xl font-medium"
                            :disabled="processing"
                            data-test="login-button"
                        >
                            <Spinner v-if="processing" />
                            <span v-else>Войти</span>
                        </Button>
                    </div>
                </Form>
            </div>

            <!-- Authenticated State -->
            <div v-else class="space-y-4 text-center">
                <p class="text-lg font-medium text-neutral-900 dark:text-neutral-50">
                    Вы уже авторизованы
                </p>
                <Link
                    :href="dashboard()"
                    class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-neutral-900 font-medium text-white hover:bg-neutral-800 dark:bg-neutral-50 dark:text-neutral-900 dark:hover:bg-neutral-100"
                >
                    Перейти в Dashboard
                </Link>
            </div>

            <!-- Footer -->
            <div class="space-y-2 pt-8 text-center text-xs text-neutral-500 dark:text-neutral-400">
                <p>
                    Свободная лицензия
                </p>
                <p>
                    Разработано с использованием искусственного интеллекта
                </p>
                </div>
        </div>
    </div>
</template>
