<script setup lang="ts">
import { ref, computed } from 'vue';
import {Head, router} from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    project: any;
    members: any[];
}>();

const search = ref('');
const confirmRemove = ref<number | null>(null);

const newEmail = ref('');
const error = ref('');
const showAddModal = ref(false);

const filteredMembers = computed(() =>
    props.members.filter(m =>
        m.name.toLowerCase().includes(search.value.toLowerCase()) ||
        m.email.toLowerCase().includes(search.value.toLowerCase())
    )
);

function removeMember(userId: number) {
    router.delete(`/projects/${props.project.id}/members/${userId}`, {
        onFinish: () => confirmRemove.value = null,
    });
}

function addMember() {
    error.value = '';
    router.post(
        `/projects/${props.project.id}/members`,
        { email: newEmail.value },
        {
            onSuccess: () => {
                newEmail.value = '';
                showAddModal.value = false;
            },
            onError: (errs) => {
                error.value = errs.email || 'Ошибка добавления.';
            },
        }
    );
}
</script>

<template>
    <Head title="Участники"></Head>
    <AppLayout>
        <div class="flex h-full  flex-col gap-6 rounded-xl p-6 bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-100">
            <!-- Заголовок и кнопка -->
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-bold">Участники проекта</h1>
                <button
                    @click="showAddModal = true"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
                >
                    Добавить участника
                </button>
            </div>

            <!-- Поиск -->
            <input
                v-model="search"
                type="text"
                placeholder="Поиск по имени или email..."
                class="border border-gray-300 dark:border-gray-600 rounded px-4 py-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring focus:ring-blue-100"
            />

            <!-- Список участников -->
            <div class="grid gap-4">
                <div
                    v-for="member in filteredMembers"
                    :key="member.id"
                    class="flex items-center justify-between p-4 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl shadow hover:shadow-md transition"
                >
                    <div class="flex items-center gap-4">
                        <img
                            class="w-10 h-10 rounded-full"
                            :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(member.name)}&background=random`"
                            :alt="member.name"
                        />
                        <div>
                            <p class="font-semibold">{{ member.name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ member.email }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <span
                            v-if="member.id === props.project.owner_id"
                            class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 px-2 py-1 rounded"
                        >
                            Владелец
                        </span>

                        <button
                            v-if="member.id !== props.project.owner_id"
                            @click="confirmRemove = member.id"
                            class="text-red-600 dark:text-red-400 hover:underline text-sm"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>

            <!-- Модалка удаления -->
            <div v-if="confirmRemove" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-xl space-y-4 w-full max-w-sm text-gray-900 dark:text-gray-100">
                    <h2 class="text-lg font-semibold">Удалить участника?</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Вы уверены, что хотите удалить участника из проекта? Это действие нельзя отменить.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button @click="confirmRemove = null" class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-100">
                            Отмена
                        </button>
                        <button
                            @click="removeMember(confirmRemove!)"
                            class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>

            <!-- Модалка добавления -->
            <div v-if="showAddModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-xl space-y-4 w-full max-w-sm text-gray-900 dark:text-gray-100">
                    <h2 class="text-lg font-semibold">Добавить участника</h2>
                    <input
                        v-model="newEmail"
                        type="email"
                        placeholder="Email участника"
                        class="border border-gray-300 dark:border-gray-600 rounded px-4 py-2 w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring focus:ring-blue-100"
                    />
                    <p v-if="error" class="text-sm text-red-500">{{ error }}</p>
                    <div class="flex justify-end gap-3">
                        <button @click="showAddModal = false" class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-100">
                            Отмена
                        </button>
                        <button
                            @click="addMember"
                            class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700"
                        >
                            Добавить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
