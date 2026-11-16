<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    BarChart3,
    BookOpen,
    Boxes,
    FolderGit2,
    FolderTree,
    Image as ImageIcon,
    LayoutGrid,
    Package,
    Settings,
    ShoppingBag,
    Sliders,
    Tag,
    TicketPercent,
    Truck,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavUser from '@/components/NavUser.vue';

interface NavItem {
    title: string;
    href: string;
    icon: any;
}

const page = usePage();
const currentPath = computed(() => page.url.split('?')[0]);

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid },
    { title: 'Products', href: '/admin/products', icon: Package },
    { title: 'Categories', href: '/admin/categories', icon: FolderTree },
    { title: 'Brands', href: '/admin/brands', icon: Tag },
    { title: 'Attributes', href: '/admin/attributes', icon: Sliders },
    { title: 'Inventory', href: '/admin/inventory', icon: Boxes },
    { title: 'Orders', href: '/admin/orders', icon: ShoppingBag },
    { title: 'Shipping', href: '/admin/shipping', icon: Truck },
    { title: 'Coupons', href: '/admin/coupons', icon: TicketPercent },
    { title: 'Reports', href: '/admin/reports', icon: BarChart3 },
    { title: 'Banners', href: '/admin/banners', icon: ImageIcon },
    { title: 'Settings', href: '/admin/settings', icon: Settings },
];

// Sidebar state — toggled by the header's hamburger
const isOpen = ref(true);
const emit = defineEmits<{ (e: 'toggle', open: boolean): void }>();

function toggle() {
    isOpen.value = !isOpen.value;
    emit('toggle', isOpen.value);
    document.body.classList.toggle('is-sidebar-open', isOpen.value);
}

// Auto-detect active route
const isActive = (href: string) =>
    currentPath.value === href || currentPath.value.startsWith(href + '/');

// Expose the toggle so the header can call it
defineExpose({ toggle, isOpen });

// Sync body class on mount based on default state
document.body.classList.toggle('is-sidebar-open', isOpen.value);
</script>

<template>
    <!-- ============ Icon rail (always visible, leftmost column) ============ -->
    <div class="main-sidebar">
        <div class="flex h-full w-full flex-col items-center bg-white py-4 shadow-sm dark:bg-navy-750">
            <!-- Logo -->
            <Link
                href="/admin/dashboard"
                class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-white"
                title="Dashboard"
            >
                <AppLogo class="h-6 w-6" />
            </Link>

            <div class="my-3 h-px w-8 bg-slate-200 dark:bg-navy-600" />

            <!-- Icon rail buttons -->
            <nav class="flex flex-1 flex-col items-center gap-1 overflow-y-auto px-2">
                <Link
                    v-for="item in mainNavItems"
                    :key="item.href"
                    :href="item.href"
                    :title="item.title"
                    class="group relative flex h-10 w-10 items-center justify-center rounded-lg transition-colors"
                    :class="
                        isActive(item.href)
                            ? 'bg-primary/10 text-primary dark:bg-accent/20 dark:text-accent-light'
                            : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:text-navy-200 dark:hover:bg-navy-700 dark:hover:text-white'
                    "
                >
                    <component :is="item.icon" class="h-5 w-5" />
                    <!-- Hover tooltip -->
                    <span class="pointer-events-none absolute left-full ml-2 whitespace-nowrap rounded-md bg-slate-800 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100 dark:bg-navy-500">
                        {{ item.title }}
                    </span>
                </Link>
            </nav>

            <div class="my-3 h-px w-8 bg-slate-200 dark:bg-navy-600" />

            <!-- Toggle button to open/close the panel -->
            <button
                type="button"
                class="mb-2 flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-700"
                :class="{ 'menu-toggle active': !isOpen }"
                :aria-label="isOpen ? 'Collapse sidebar' : 'Expand sidebar'"
                @click="toggle"
            >
                <span class="sr-only">Toggle sidebar</span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- User chip at bottom -->
            <div class="mb-1">
                <NavUser compact />
            </div>
        </div>
    </div>

    <!-- ============ Slide-in panel (expanded menu with labels) ============ -->
    <div class="sidebar-panel">
        <div class="flex h-full w-full flex-col bg-white dark:bg-navy-750">
            <!-- Header -->
            <div class="flex h-16 items-center gap-3 border-b border-slate-150 px-4 dark:border-navy-700">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-white">
                    <AppLogo class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-navy-50">Admin Panel</p>
                    <p class="text-xs text-slate-500 dark:text-navy-300">My Store</p>
                </div>
            </div>

            <!-- Nav list -->
            <nav class="flex-1 overflow-y-auto py-3 is-scrollbar-hidden">
                <p class="mb-1 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-400">
                    Manage
                </p>
                <ul>
                    <li v-for="item in mainNavItems" :key="item.href">
                        <Link
                            :href="item.href"
                            class="mx-2 flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors"
                            :class="
                                isActive(item.href)
                                    ? 'bg-primary/10 font-medium text-primary dark:bg-accent/20 dark:text-accent-light'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-800 dark:text-navy-200 dark:hover:bg-navy-700 dark:hover:text-white'
                            "
                        >
                            <component :is="item.icon" class="h-4 w-4 shrink-0" />
                            <span class="truncate">{{ item.title }}</span>
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- Footer links -->
            <div class="border-t border-slate-150 p-3 dark:border-navy-700">
                <a
                    href="https://github.com/laravel/vue-starter-kit"
                    target="_blank"
                    class="mb-1 flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-700"
                >
                    <FolderGit2 class="h-4 w-4" />
                    Repository
                </a>
                <a
                    href="https://laravel.com/docs/starter-kits#vue"
                    target="_blank"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-700"
                >
                    <BookOpen class="h-4 w-4" />
                    Documentation
                </a>
            </div>
        </div>
    </div>
</template>