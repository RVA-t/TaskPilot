<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import { Dialog, DialogContent, DialogTrigger, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

const form = useForm({
    name: '',
    description: '',
});

const submit = () => {
    form.post(route('projects.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="outline" size="lg">+ Новый проект</Button>
        </DialogTrigger>

        <DialogContent>
            <DialogHeader>
                <DialogTitle>Создание проекта</DialogTitle>
                <DialogDescription>Заполните данные проекта, чтобы начать работу.</DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4 mt-4">
                <div>
                    <Label for="name">Название</Label>
                    <Input id="name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div>
                    <Label for="description">Описание</Label>
                    <Input id="description" v-model="form.description" />
                    <InputError :message="form.errors.description" />
                </div>

                <DialogFooter class="mt-4">
                    <DialogClose as-child>
                        <Button variant="secondary">Отмена</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="form.processing">Создать</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
