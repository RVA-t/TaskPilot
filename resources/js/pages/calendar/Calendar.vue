<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Calendar } from 'v-calendar'
import 'v-calendar/style.css'

const props = defineProps<{
    project: any;
    tasks: {
        id: number;
        title: string;
        deadline: string;
        status: number; // column_index
    }[];
}>()

const getStatusByIndex = (index: number): string => {
    switch (index) {
        case 0: return 'Бэклог'
        case 1: return 'В работе'
        case 2: return 'Тестирование'
        case 3: return 'Готово'
        default: return 'Неизвестно'
    }
}

const selectedDate = ref('')
const selectedTasks = computed(() =>
    props.tasks.filter(task => task.deadline.slice(0, 10) === selectedDate.value)
)

const taskMap = computed(() => {
    const map: Record<string, typeof props.tasks> = {}
    props.tasks.forEach(task => {
        const date = task.deadline.slice(0, 10)
        if (!map[date]) map[date] = []
        map[date].push(task)
    })
    return map
})

const calendarAttributes = computed(() => {
    return Object.entries(taskMap.value).map(([date, tasks]) => ({
        key: date,
        dates: date,
        dot: true,
        popover: {
            label: tasks.map(task =>
                `• ${task.title} (${getStatusByIndex(task.status)})`
            ).join('\n')  // Перенос строки
        },
        contentClass:
            tasks.some(t => t.status === 3) ? 'bg-green-500' :
            tasks.some(t => t.status === 1) ? 'bg-blue-500' :
            tasks.some(t => t.status === 2) ? 'bg-yellow-500' :
            'bg-gray-400'
    }))
})

const now = new Date()
const currentMonth = now.getMonth()
const currentYear = now.getFullYear()

// Задачи в текущем месяце
const monthlyTasks = computed(() =>
    props.tasks.filter(task => {
        const d = new Date(task.deadline)
        return d.getMonth() === currentMonth && d.getFullYear() === currentYear
    })
)

const stats = (tasks: typeof props.tasks) => ({
    backlog: tasks.filter(t => t.status === 0).length,
    inProgress: tasks.filter(t => t.status === 1).length,
    testing: tasks.filter(t => t.status === 2).length,
    done: tasks.filter(t => t.status === 3).length,
    total: tasks.length
})

const monthStats = computed(() => stats(monthlyTasks.value))
const allStats = computed(() => stats(props.tasks))

// Процент выполнения
const getProgress = (s: ReturnType<typeof stats>) =>
    s.total === 0 ? 0 : Math.round((s.done / s.total) * 100)


function onDayClick({ date }: { date: Date }) {
    // Получаем дату без сдвига по UTC
    selectedDate.value = date.toLocaleDateString('sv-SE') // 'YYYY-MM-DD'
}
</script>

<template>
    <AppLayout>
        <div class="flex gap-4 p-5 h-[calc(100vh-100px)]">
            <!-- Левая колонка с календарем и статистикой -->
            <div class="flex-1 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-xl p-4 flex flex-col gap-4">
                <!-- Заголовок -->
                <h1 class="text-2xl font-bold">Календарь задач</h1>

                <!-- Календарь (50% высоты) -->
                <div class="flex-1 min-h-0" style="flex: 0 0 45%;">
                    <Calendar
                        is-expanded
                        :attributes="calendarAttributes"
                        @dayclick="onDayClick"
                        class="w-full h-full"
                        style="height: 100%; width: 100%;"
                    />
                </div>

                <!-- Статистика задач -->
                <div class="flex-1 overflow-auto bg-gray-100 dark:bg-gray-700 rounded-lg p-4 space-y-6">
                    <!-- Статистика задач -->
                    <div class="flex-1 overflow-auto bg-gray-100 dark:bg-gray-700 rounded-lg p-4">
                        <div class="flex flex-col lg:flex-row gap-6">
                            <!-- За текущий месяц -->
                            <div class="flex-1">
                                <h2 class="text-xl font-semibold mb-2">📅 Успеваемость (текущий месяц)</h2>
                                <ul class="space-y-1 text-base">
                                    <li>📋 Бэклог: {{ monthStats.backlog }}</li>
                                    <li>🔧 В работе: {{ monthStats.inProgress }}</li>
                                    <li>🧪 Тестирование: {{ monthStats.testing }}</li>
                                    <li>✅ Готово: {{ monthStats.done }}</li>
                                </ul>
                                <div class="mt-2 h-4 bg-gray-300 dark:bg-gray-600 rounded">
                                    <div
                                        class="h-full bg-green-500 rounded"
                                        :style="{ width: getProgress(monthStats) + '%' }"
                                    ></div>
                                </div>
                                <p class="text-sm mt-1">{{ getProgress(monthStats) }}% завершено</p>
                            </div>

                            <!-- За всё время -->
                            <div class="flex-1">
                                <h2 class="text-xl font-semibold mb-2">📊 Успеваемость (всё время)</h2>
                                <ul class="space-y-1 text-base">
                                    <li>📋 Бэклог: {{ allStats.backlog }}</li>
                                    <li>🔧 В работе: {{ allStats.inProgress }}</li>
                                    <li>🧪 Тестирование: {{ allStats.testing }}</li>
                                    <li>✅ Готово: {{ allStats.done }}</li>
                                </ul>
                                <div class="mt-2 h-4 bg-gray-300 dark:bg-gray-600 rounded">
                                    <div
                                        class="h-full bg-green-600 rounded"
                                        :style="{ width: getProgress(allStats) + '%' }"
                                    ></div>
                                </div>
                                <p class="text-sm mt-1">{{ getProgress(allStats) }}% завершено</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar with tasks -->
            <div class="w-96 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-xl p-4 overflow-y-auto">
                <h2 class="text-xl font-bold mb-2">Задачи на {{ selectedDate || '...' }}</h2>
                <ul v-if="selectedTasks.length">
                    <li v-for="task in selectedTasks" :key="task.id" class="mb-3 border-b pb-2">
                        <p class="font-medium">{{ task.title }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ getStatusByIndex(task.status) }}
                        </p>
                    </li>
                </ul>
                <p v-else class="text-gray-500 dark:text-gray-400">Нет задач</p>
            </div>
        </div>
    </AppLayout>
</template>


<style>
.vc-popover-content {
    white-space: pre-line !important;
}

.vc-day-content {
    box-sizing: border-box;
    padding: 0.75rem !important;
    line-height: 1.4 !important;
    min-height: 3rem;
    min-width: 3rem;
    font-size: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: background 0.2s;
}

.vc-day-content:hover {
    background-color: rgba(100, 100, 100, 0.1);
}
</style>

