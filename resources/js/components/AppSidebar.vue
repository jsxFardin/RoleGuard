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
import { index as servicesIndex } from '@/routes/admin/services';
import { index as faqsIndex } from '@/routes/admin/faqs';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {LayoutGrid, Package, HelpCircle, Users, Shield } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage<any>();
const permissions = computed<string[]>(() => page.props.auth?.permissions || []);
const canAny = (slugs: string[]) => slugs.some((s) => permissions.value.includes(s));

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
    ];

    if (canAny(['services.list', 'services.view', 'services.create', 'services.update', 'services.delete'])) {
        items.push({ title: 'Services', href: servicesIndex(), icon: Package });
    }

    if (canAny(['faqs.list', 'faqs.view', 'faqs.create', 'faqs.update', 'faqs.delete'])) {
        items.push({ title: 'FAQs', href: faqsIndex(), icon: HelpCircle });
    }

    return items;
});

const userRoleNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (canAny(['users.list', 'users.update', 'users.view'])) {
        items.push({ title: 'Users', href: '/admin/users', icon: Users });
    }

    if (canAny(['roles.list', 'roles.create', 'roles.update', 'roles.delete'])) {
        items.push({ title: 'Roles', href: '/admin/roles', icon: Shield });
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
            <NavMain label="Main" :items="mainNavItems" />
            <NavMain v-if="userRoleNavItems.length" label="Users & Roles" :items="userRoleNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
