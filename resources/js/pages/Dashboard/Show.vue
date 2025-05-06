<script setup lang="ts">
import { ref, nextTick, reactive, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import draggable from 'vuedraggable';

axios.defaults.withCredentials = true;

 //**// Этот кусок кода необходим для того, чтобы я мог хранить свой id в глобальном хранилище и в
// дальнейшем доставать из любого места свой id и использовать его (пример где используется AppSidebar.vue)
import { usePage } from '@inertiajs/vue3';
import { useProjectStore } from '@/stores/useProjectStore';

const page = usePage();
const projectId = Number(page.props.project?.id); // предположим, ты передаёшь `project` как prop

const store = useProjectStore();
store.setId(projectId);
//**//

import { onMounted } from 'vue';

const loadTasks = async () => {
    try {
        const response = await axios.get(`/api/projects/${props.project.id}/tasks`);
        const tasks = response.data;

        console.log('Загруженные задачи:', tasks);

        // Очищаем колонки перед повторной загрузкой
        columns.value.forEach(column => {
            column.tasks = [];
        });

        // Распределяем задачи по колонкам
        tasks.forEach(task => {
            if (columns.value[task.column_index]) {
                columns.value[task.column_index].tasks.push(task);
            }
        });
    } catch (error) {
        console.error('Ошибка при загрузке задач:', error);
    }
};

onMounted(() => {
    loadTasks();
});



const props = defineProps<{
    project: {
        id: number;
        name: string;
        description: string | null;
    };
    users: Array<{
        id: number;
        name: string
    }>;
}>();

const breadcrumbs = [
    { title: 'Проекты', href: '/projects' },
    { title: props.project.name, href: `/dashboard/${props.project.id}` },
];

const columns = ref([
    { title: 'Бэклог', tasks: [], newTaskTitle: '', isAdding: false },
    { title: 'В работе', tasks: [], newTaskTitle: '', isAdding: false },
    { title: 'Тестирование', tasks: [], newTaskTitle: '', isAdding: false },
    { title: 'Готово', tasks: [], newTaskTitle: '', isAdding: false },
]);

const newTaskInputRefs = reactive<Array<HTMLInputElement | null>>([]);

const startAddingTask = (columnIndex: number) => {
    columns.value[columnIndex].isAdding = true;

    // гарантируем, что массив newTaskInputRefs имеет нужную длину
    if (!newTaskInputRefs[columnIndex]) {
        newTaskInputRefs[columnIndex] = null;
    }

    nextTick(() => {
        newTaskInputRefs[columnIndex]?.focus();
    });
};

const onEnd = (event, tasks) => {
    // Получаем саму задачу, а не полагаемся на индекс
    const movedTask = event.item?.__draggable_context?.element;

    if (!movedTask) {
        console.error('Задача не найдена через __draggable_context');
        return;
    }

    // Получаем индекс колонки
    const targetColumnElement = event.to.closest('[data-column-index]');
    if (!targetColumnElement) {
        console.error('Целевая колонка не найдена');
        return;
    }

    const newColumnIndex = Number(targetColumnElement.getAttribute('data-column-index'));

    console.log({ movedTask, newColumnIndex });

    saveTaskColumn(movedTask, newColumnIndex);
};

const saveTaskColumn = async (task, columnIndex) => {
    if (!task) {
        console.error('Задача не передана в функцию');
        return;
    }
    if (!task.id) {
        console.error('Задача не имеет ID');
        return;
    }

    try {
        await axios.patch(`/api/tasks/${task.id}/column`, {
            column_index: columnIndex,
        });
    } catch (error) {
        console.error('Ошибка при изменении колонки задачи:', error);
    }
};

const saveTask = async (columnIndex: number) => {
    const title = columns.value[columnIndex].newTaskTitle.trim();
    if (!title) {
        columns.value[columnIndex].isAdding = false;
        return;
    }

    try {
        const response = await axios.post('/api/tasks', {
            title,
            project_id: props.project.id,
            column_index: columnIndex,
            deadline: null,
        });

        console.log('Задача сохранена:', response.data);

        columns.value[columnIndex].tasks.push(response.data);
        columns.value[columnIndex].newTaskTitle = '';
        columns.value[columnIndex].isAdding = false;
    } catch (error) {
        console.error('Ошибка при создании задачи:', error);
    }
};

const selectedTask = ref(null); // активная задача
const isTaskModalOpen = ref(false);

const openTaskModal = async (task: any) => {
    try {
        const response = await axios.get(`/api/tasks/${task.id}`);
        selectedTask.value = response.data;
        isTaskModalOpen.value = true;
    } catch (error) {
        console.error('Ошибка при получении задачи:', error);
    }
};

const closeTaskModal = () => {
    selectedTask.value = null;
    isTaskModalOpen.value = false;
};

const saveTaskDetails = async () => {
    if (!selectedTask.value){
        return;
    }

    try {
        const response = await axios.patch(`/api/tasks/${selectedTask.value.id}`, selectedTask.value);
        const updatedTask = response.data;

        // Найти задачу в старой колонке
        columns.value.forEach((col, idx) => {
            const taskIdx = col.tasks.findIndex((t) => t.id === updatedTask.id);
            if (taskIdx !== -1) {
                col.tasks.splice(taskIdx, 1); // Удаляем из старой колонки
            }
        });

        // Добавить задачу в новую колонку
        columns.value[updatedTask.column_index].tasks.push(updatedTask);

        closeTaskModal();
    } catch (error) {
        console.error('Ошибка при обновлении задачи:', error);
    }
};


const newComment = ref('');

const submitComment = async () => {
    if (!selectedTask.value || !newComment.value.trim()) return;

    try {

        await axios.get('http://localhost:8000/sanctum/csrf-cookie', {
            withCredentials: true,
        });

        const response = await axios.post(`/api/tasks/${selectedTask.value.id}/comments`, {
            body: newComment.value,
        }, {
            withCredentials: true, // ← ЭТО ОБЯЗАТЕЛЬНО!
        });

        if (!selectedTask.value.comments) {
            selectedTask.value.comments = [];
        }

        selectedTask.value.comments.push(response.data);
        newComment.value = '';
    } catch (error) {
        console.error('Ошибка при добавлении комментария:', error);
    }
};

const showOnlyUrgent = ref(false);

const filteredColumns = computed(() => {
    return columns.value.map(column => {
        const filteredTasks = showOnlyUrgent.value
            ? column.tasks.filter(task => task.urgent)
            : column.tasks;

        return {
            ...column,
            tasks: filteredTasks,
        };
    });
});
</script>

<template>
<!--    <pre>{{ props.users }}</pre>-->
    <Head :title="`Проект: ${props.project.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-100">

            <div class="flex items-center justify-between mb-4">
                <div class="text-2xl font-semibold">{{ props.project.name }}</div>
                <label class="flex items-center space-x-2 text-sm">
                    <span>Показать только срочные</span>
                    <input
                        type="checkbox"
                        v-model="showOnlyUrgent"
                        class="form-checkbox h-4 w-4 text-red-600"
                    />
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 w-full">
                <div
                    v-for="(column, columnIndex) in filteredColumns"
                    :key="columnIndex"
                    class="flex flex-col p-4 rounded-xl shadow-md bg-gray-50 text-gray-900 border border-gray-200 dark:bg-gray-900 dark:text-gray-100 dark:border-gray-600"
                    :data-column-index="columnIndex"
                >
                    <h3 class="text-lg font-bold mb-2">{{ column.title }}</h3>

                    <draggable
                        class="flex flex-col gap-2 mb-4"
                        :list="column.tasks"
                        :group="{ name: 'tasks', pull: true, put: true }"
                        @end="(event) => onEnd(event, column.tasks, columnIndex)"
                        :item-key="'id'"
                    >
                        <template #item="{ element }">
                            <div
                                :key="element.id"
                                class="relative p-2 rounded-lg border bg-white text-gray-900 border-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-500"
                            >
                                <div
                                    @click="openTaskModal(element)"
                                    class="cursor-pointer break-words whitespace-pre-wrap"
                                >
                                    {{ element.title }}
                                </div>
                            </div>
                        </template>
                    </draggable>

                    <div v-if="column.isAdding" class="mb-2">
                        <textarea
                            v-model="column.newTaskTitle"
                            :ref="el => newTaskInputRefs[columnIndex] = el"
                            @blur="saveTask(columnIndex)"
                            @keydown.enter="saveTask(columnIndex)"
                            @input="autoResize"
                            class="w-full px-2 py-1 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-black overflow-hidden break-words whitespace-pre-wrap"
                            placeholder="Введите название задачи"
                            rows="1"
                        />
                    </div>

                    <button
                        v-if="!column.isAdding"
                        @click="startAddingTask(columnIndex)"
                        class="mt-4 px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-indigo-800 transition-colors border border-gray-300"
                    >
                        Добавить задачу
                    </button>
                </div>
            </div>
        </div>

        <transition name="fade-scale">
            <div
                v-if="isTaskModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            >
                <div
                    class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-2xl shadow-2xl p-6 sm:p-8 text-gray-900 dark:text-white transition-all"
                >
                    <h2 class="text-2xl font-semibold mb-6 text-center">
                        Редактировать задачу
                    </h2>

                    <div class="space-y-4">

                        <!-- Название + Срочная -->
                        <div class="flex flex-col md:flex-row md:items-center md:gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium mb-1">Название</label>
                                <input
                                    v-model="selectedTask.title"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>
                            <div class="mt-2 md:mt-6 flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    v-model="selectedTask.urgent"
                                    class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                />
                                <label class="text-sm font-medium">Срочная задача</label>
                            </div>
                        </div>

                        <!-- Дедлайн + Колонка -->
                        <div class="flex flex-col md:flex-row md:gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium mb-1">Дедлайн</label>
                                <input
                                    v-model="selectedTask.deadline"
                                    type="date"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium mb-1">Колонка</label>
                                <select
                                    v-model="selectedTask.column_index"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option
                                        v-for="(column, index) in columns"
                                        :key="index"
                                        :value="index"
                                    >
                                        {{ column.title }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Куратор + Исполнитель -->
                        <div class="flex flex-col md:flex-row md:gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium mb-1">Куратор</label>
                                <select
                                    v-model="selectedTask.curator_id"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option disabled value="">Выберите куратора</option>
                                    <option
                                        v-for="user in props.users"
                                        :key="user.id"
                                        :value="user.id"
                                    >
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="flex-1">
                                <label class="block text-sm font-medium mb-1">Исполнитель</label>
                                <select
                                    v-model="selectedTask.assignee_id"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option disabled value="">Выберите исполнителя</option>
                                    <option
                                        v-for="user in props.users"
                                        :key="user.id"
                                        :value="user.id"
                                    >
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Описание -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Описание</label>
                            <textarea
                                v-model="selectedTask.description"
                                rows="3"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            ></textarea>
                        </div>

                        <!-- Комментарии -->
                        <div class="pt-2 border-t border-gray-300 dark:border-gray-700">
                            <h3 class="text-lg font-semibold mt-4 mb-2">Комментарии</h3>

                            <div
                                v-if="selectedTask.comments?.length"
                                class="space-y-2 max-h-64 overflow-y-auto pr-2"
                            >
                                <div
                                    v-for="comment in selectedTask.comments"
                                    :key="comment.id"
                                    class="flex gap-3 items-start p-3 rounded-lg border bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700"
                                >
                                    <!-- Аватар -->
                                    <div
                                        class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-sm cursor-pointer"
                                        :title="`${comment.author.name} (${comment.author.email})`"
                                    >
                                        {{ comment.author.name.charAt(0).toUpperCase() }}
                                    </div>

                                    <!-- Текст комментария -->
                                    <div class="flex-1">
                                        <div class="text-sm font-semibold mb-1">{{ comment.author.name }}</div>
                                        <div class="text-sm">{{ comment.body }}</div>
                                    </div>
                                </div>

                            </div>

                            <div class="mt-4">
            <textarea
                v-model="newComment"
                placeholder="Напишите комментарий..."
                rows="2"
                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
            ></textarea>
                                <button
                                    @click="submitComment"
                                    class="mt-2 px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg"
                                >
                                    Отправить
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            @click="closeTaskModal"
                            class="px-4 py-2 text-sm rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white transition"
                        >
                            Отмена
                        </button>
                        <button
                            @click="saveTaskDetails"
                            class="px-4 py-2 text-sm rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition"
                        >
                            Сохранить
                        </button>
                    </div>
                </div>
            </div>
        </transition>


    </AppLayout>
</template>

<style scoped>
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: all 0.25s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
    opacity: 0;
    transform: scale(0.95);
}

.fade-scale-enter-to,
.fade-scale-leave-from {
    opacity: 1;
    transform: scale(1);
}
</style>
