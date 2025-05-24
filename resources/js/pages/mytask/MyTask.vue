<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import {Head} from "@inertiajs/vue3";


const props = defineProps<{
    project: any,
    tasks: { id: number, title: string, column_index: number }[]
}>();

const getStatusByIndex = (index: number): string => {
    switch (index) {
        case 0: return 'Бэклог'
        case 1: return 'В работе'
        case 2: return 'Тестирование'
        case 3: return 'Готово'
        default: return 'Неизвестно'
    }
}

const search = ref('');

const filteredTasks = computed(() =>
    props.tasks.filter(task =>
        task.title.toLowerCase().includes(search.value.toLowerCase())
    )
);

const completedTasksCount = computed(() =>
    props.tasks.filter(task => task.column_index === 3).length
);

const completionRate = computed(() => {
    const total = props.tasks.length;
    return total === 0 ? 0 : Math.round((completedTasksCount.value / total) * 100);
});

</script>

<template>
    <Head title="Мои задачи"></Head>
    <AppLayout>
        <div class="flex h-full  flex-col gap-6 rounded-xl p-6 bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-100">
            <h1 class="text-3xl font-bold">Мои задачи — {{ project.name }}</h1>

            <input
                v-model="search"
                type="text"
                placeholder="Поиск задач..."
                class="border border-gray-300 dark:border-gray-600 rounded px-4 py-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring focus:ring-blue-100"
            />

            <div v-if="filteredTasks.length === 0" class="text-gray-600 dark:text-gray-300">
                Нет задач по данному фильтру.
            </div>

            <ul class="space-y-4">
                <li
                    v-for="task in filteredTasks"
                    :key="task.id"
                    class="p-4 border rounded shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                >
                    <div class="font-medium">{{ task.title }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-300">Статус: {{ getStatusByIndex(task.column_index) }}</div>
                </li>
            </ul>
        </div>
        <div class="rounded-lg bg-blue-100 dark:bg-gray-800 p-4 text-gray-800 dark:text-blue-200 shadow-sm">
            <h2 class="font-semibold text-lg mb-1">Выполнено</h2>
            <div class="flex items-center gap-4">
                <div class="w-full bg-gray-600 dark:bg-gray-700 rounded h-3">
                    <div
                        class="bg-green-600 h-3 rounded transition-all duration-300"
                        :style="{ width: `${completionRate}%` }"
                    />
                </div>
                <span class="min-w-[50px] text-sm">{{ completionRate }}%</span>
            </div>
        </div>

    </AppLayout>
</template>
