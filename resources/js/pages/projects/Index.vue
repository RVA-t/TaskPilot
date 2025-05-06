<script setup lang="ts">
import { ref } from 'vue';
import {usePage, Link, Head} from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

import CreateProjectModal from '@/components/projects/CreateProjectModal.vue';
import {
    Dialog,
    DialogContent,
    DialogTrigger,
    DialogHeader,
    DialogTitle,
    DialogClose,
} from '@/components/ui/dialog';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

const showTasks = ref(false);
const page = usePage();
const user = page.props.auth.user;

defineProps<{
    projects: Array<{
        id: number;
        name: string;
        description: string | null;
    }>;
}>();
</script>

<template>
    <Head title="Список проектов"></Head>
    <div class="min-h-screen bg-background text-foreground transition-colors">
        <!-- Центрированная обертка 60% -->
        <div class="mx-auto w-full max-w-[60%] space-y-10 pt-8">

            <!-- Логотип -->
            <div class="flex justify-center">
                <div class="text-3xl font-bold">🚀 Список проектов</div>
            </div>

            <!-- Панель с кнопками и юзером -->
            <div class="flex justify-between items-center">
                <!-- Кнопка "Задачи" -->
                <Dialog v-model:open="showTasks">
                    <DialogTrigger as-child>
                        <Button class="rounded-full px-4 py-2" variant="secondary">
                            Задачи
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Мои задачи</DialogTitle>
                        </DialogHeader>
                        <div class="text-sm text-muted-foreground">
                            Здесь будут отображаться задачи, связанные с вами.
                        </div>
                        <DialogClose as-child>
                            <Button class="mt-4" variant="outline">Закрыть</Button>
                        </DialogClose>
                    </DialogContent>
                </Dialog>

                <!-- Инфо о пользователе -->
                <div class="text-right text-sm text-muted-foreground">
                    <div>{{ user.name }}</div>
                    <div>{{ user.email }}</div>
                </div>
            </div>

            <!-- Заголовок "Все проекты" -->
            <h2 class="text-xl font-semibold text-left">Все проекты</h2>

            <!-- Сетка проектов + кнопка -->
            <div class="grid grid-cols-3 gap-6">
                <!-- Карточки проектов -->
                <Card
                    v-for="project in projects"
                    :key="project.id"
                    class="hover:shadow-lg transition cursor-pointer"
                    @click="router.visit(`/dashboard/${project.id}`)"
                >
                    <CardContent class="p-4">
                        <h2 class="font-semibold text-lg mb-1">{{ project.name }}</h2>
                        <p class="text-sm text-muted-foreground">{{ project.description }}</p>
                    </CardContent>
                </Card>


                <!-- Кнопка "Добавить проект" -->
                <div class="flex justify-center items-center">
                    <CreateProjectModal />
                </div>
            </div>
        </div>
    </div>
</template>
