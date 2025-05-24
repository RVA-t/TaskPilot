<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import {Head, router} from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';

const props = defineProps<{
    project: any;
    tasks: { id: number; title: string; deadline: string | null }[];
    members: { id: number; name: string; telegram_chat_id: string | null }[];
}>();

const selectedTasks = ref<number[]>([]);
const selectedUsers = ref<number[]>([]);
const additionalMessage = ref('');
const showConfirm = ref(false);

const sendNotification = () => {
    router.post(
        `/projects/${props.project.id}/notify`,
        {
            taskIds: selectedTasks.value,
            userIds: selectedUsers.value,
            message: additionalMessage.value,
        },
        {
            onSuccess: () => {
                toast.success('Уведомление отправлено!');
                selectedTasks.value = [];
                selectedUsers.value = [];
                additionalMessage.value = '';
                showConfirm.value = false;
            },
            onError: () => {
                toast.error('Произошла ошибка при отправке.');
            },
        }
    );
};

const taskSearch = ref('');
const userSearch = ref('');

const filteredTasks = computed(() =>
    props.tasks.filter(task =>
        task.title.toLowerCase().includes(taskSearch.value.toLowerCase())
    )
);

const filteredUsers = computed(() =>
    props.members.filter(user =>
        user.name.toLowerCase().includes(userSearch.value.toLowerCase())
    )
);

</script>

<template>
    <Head title="Telegram-уведомления"></Head>
    <AppLayout>
        <div class="flex flex-col gap-6 p-6 w-full h-full bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
            <h1 class="text-2xl font-bold mb-2">Telegram-уведомления</h1>

            <!-- Поиски -->
            <div class="flex flex-col md:flex-row gap-4">
                <input
                    v-model="taskSearch"
                    placeholder="🔍 Поиск задач..."
                    class="w-full md:w-1/2 p-2 border rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                />
                <input
                    v-model="userSearch"
                    placeholder="🔍 Поиск участников..."
                    class="w-full md:w-1/2 p-2 border rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                />
            </div>

            <!-- Контейнеры -->
            <div class="flex flex-col md:flex-row gap-6 flex-1 overflow-hidden">
                <!-- Задачи -->
                <div class="flex-1 flex flex-col border border-gray-300 dark:border-gray-600 rounded-xl p-4 overflow-hidden">
                    <h2 class="text-lg font-semibold mb-2">Задачи</h2>
                    <div class="flex-1 overflow-y-auto pr-2 min-h-0">
                        <ul class="space-y-3">
                            <li
                                v-for="task in filteredTasks"
                                :key="task.id"
                                class="flex items-start border-b pb-2"
                            >
                                <input
                                    type="checkbox"
                                    :value="task.id"
                                    v-model="selectedTasks"
                                    class="mt-1 mr-2"
                                />
                                <div>
                                    <div class="font-medium">{{ task.title }}</div>
                                    <div class="text-sm text-gray-500">
                                        📅 Дедлайн: {{ task.deadline ?? 'не указан' }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>


                <!-- Участники -->
                <div class="flex-1 flex flex-col border border-gray-300 dark:border-gray-600 rounded-xl p-4 overflow-hidden">
                    <h2 class="text-lg font-semibold mb-2">Участники</h2>
                    <div class="flex-1 overflow-y-auto pr-2">
                        <ul class="space-y-3">
                            <li
                                v-for="user in filteredUsers"
                                :key="user.id"
                                class="flex items-start border-b pb-2"
                            >
                                <input
                                    type="checkbox"
                                    :value="user.id"
                                    v-model="selectedUsers"
                                    class="mt-1 mr-2"
                                    :disabled="!user.telegram_chat_id"
                                />
                                <div>
                                    <div class="font-medium flex items-center gap-1">
                                        {{ user.name }}
                                        <span v-if="user.telegram_chat_id" class="text-green-500">📨</span>
                                        <span v-else class="text-red-500 text-sm">— не привязан Telegram</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Кнопка -->
            <div>
                <button
                    class="w-full md:w-auto px-6 py-3 bg-indigo-700 text-white rounded-lg hover:bg-indigo-800 transition"
                    :disabled="!selectedTasks.length || !selectedUsers.length"
                    @click="showConfirm = true"
                >
                    Отправить уведомление
                </button>
            </div>

            <!-- Модалка -->
            <div
                v-if="showConfirm"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-xl max-w-md w-full">
                    <h3 class="text-lg font-semibold mb-4">Дополнительное сообщение</h3>
                    <textarea
                        v-model="additionalMessage"
                        rows="4"
                        placeholder="Напишите сообщение..."
                        class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-800 dark:text-gray-100"
                    ></textarea>

                    <div class="flex justify-end mt-4 gap-2">
                        <button
                            class="px-4 py-2 bg-gray-300 dark:bg-gray-700 rounded hover:bg-gray-400"
                            @click="showConfirm = false"
                        >
                            Отмена
                        </button>
                        <button
                            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
                            @click="sendNotification"
                        >
                            Подтвердить отправку
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

