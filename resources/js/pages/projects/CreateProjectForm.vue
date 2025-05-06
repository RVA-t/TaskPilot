<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';

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
    <form @submit.prevent="submit" class="space-y-4 p-4 border rounded">
        <div>
            <Label for="name">Project Name</Label>
            <Input id="name" v-model="form.name" type="text" />
            <InputError :message="form.errors.name" />
        </div>

        <div>
            <Label for="description">Description</Label>
            <Input id="description" v-model="form.description" type="text" />
            <InputError :message="form.errors.description" />
        </div>

        <Button type="submit" :disabled="form.processing">
            Create Project
        </Button>
    </form>
</template>
