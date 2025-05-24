<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    BotMessageSquare,
    CalendarCheck2, ClipboardList,
    Columns4,
    Folder,
    LayoutList,
    MessageCircleCode,
    Users
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { usePage } from '@inertiajs/vue3';

const currentProjectId = usePage().props.currentProjectId;

const mainNavItems: NavItem[] = [
    {
        title: 'Проекты',
        href: '/projects',
        icon: LayoutList,
    },
    {
        title: 'Текущий проект',
        href: `/dashboard/${currentProjectId}`,
        icon: Columns4,
    },
    {
        title: 'Участники',
        href: `/projects/${currentProjectId}/members`,
        icon: Users,
    },
    {
        title: 'Календарь',
        href: `/projects/${currentProjectId}/calendar`,
        icon: CalendarCheck2,
    },
    {
        title: 'Мои задачи',
        href: `/projects/${currentProjectId}/my-tasks`,
        icon: ClipboardList,
    },
    {
        title: 'Сообщить в Telegram',
        href: `/projects/${currentProjectId}/notify`,
        icon: BotMessageSquare,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/RVA-t/TaskPilot',
        icon: Folder,
    },
    {
        title: 'Документация',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
