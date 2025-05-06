// resources/js/stores/useProjectStore.ts
import { defineStore } from 'pinia';

export const useProjectStore = defineStore('project', {
    state: () => ({
        currentId: null as number | null,
    }),
    actions: {
        setId(id: number) {
            this.currentId = id;
        },
    },
});
