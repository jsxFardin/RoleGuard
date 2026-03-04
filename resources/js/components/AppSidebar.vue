<script setup lang="ts">
// import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes/admin';
import { index as faqsIndex } from '@/routes/admin/faqs';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, HelpCircle, Users, Shield, ScrollText, MessageSquare } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const page = usePage<any>();
const { t } = useI18n();
const permissions = computed<string[]>(() => page.props.auth?.permissions || []);
const canAny = (slugs: string[]) => slugs.some((s) => permissions.value.includes(s));

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        { title: t('nav.dashboard'), href: dashboard(), icon: LayoutGrid },
        { title: t('nav.chat'), href: '/chat', icon: MessageSquare },
    ];

    if (canAny(['faqs.list', 'faqs.view', 'faqs.create', 'faqs.update', 'faqs.delete'])) {
        items.push({ title: t('nav.faqs'), href: faqsIndex(), icon: HelpCircle });
    }

    if (canAny(['audit.list', 'audit.view'])) {
        items.push({ title: t('nav.audit_logs'), href: '/admin/audit-logs', icon: ScrollText });
    }

    return items;
});



const userRoleNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (canAny(['users.list', 'users.update', 'users.view'])) {
        items.push({ title: t('nav.users'), href: '/admin/users', icon: Users });
    }

    if (canAny(['roles.list', 'roles.create', 'roles.update', 'roles.delete'])) {
        items.push({ title: t('nav.roles'), href: '/admin/roles', icon: Shield });
    }

    return items;
});

// const footerNavItems: NavItem[] = [
//     {
//         title: 'Github Repo',
//         href: 'https://github.com/laravel/vue-starter-kit',
//         icon: Folder,
//     },
//     {
//         title: 'Documentation',
//         href: 'https://laravel.com/docs/starter-kits#vue',
//         icon: BookOpen,
//     },
// ];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :label="t('nav.main')" :items="mainNavItems" />
            <NavMain v-if="userRoleNavItems.length" :label="t('nav.users_roles')" :items="userRoleNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
