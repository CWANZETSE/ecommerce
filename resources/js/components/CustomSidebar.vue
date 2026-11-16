<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    User as UserIcon,
    ShoppingBag,
    ArrowRight,
} from '@lucide/vue';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const page = usePage();
const currentPath = computed(() => page.url.split('?')[0]);
const user = computed(() => (page.props as any).auth?.user ?? null);

const isActive = (href: string) =>
    currentPath.value === href || currentPath.value.startsWith(href + '/');

const userInitial = computed(() => {
    const name = user.value?.name ?? '';
    return name.charAt(0).toUpperCase() || '?';
});

function logout() {
    router.post('/logout');
}
</script>

<template>
    <!-- Mobile backdrop -->
    <div v-if="props.open" class="fixed inset-0 z-40 bg-black/40 md:hidden" @click="emit('close')" />

    <!-- Single panel — slides on mobile, always visible on desktop -->
    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-[300px] flex-col border-r border-slate-150 bg-white transition-transform duration-300 ease-in-out dark:border-navy-700 dark:bg-navy-750 md:translate-x-0"
        :class="!props.open && 'max-md:-translate-x-full'">
        <!-- Logo -->
        <div class="flex h-[61px] shrink-0 items-center gap-3 border-b border-slate-150 px-4 dark:border-navy-700">
            <Link href="/admin/dashboard"
                class="flex size-9 items-center justify-center rounded-lg bg-primary text-white dark:bg-accent">
                <span class="text-base font-bold">A</span>
            </Link>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-slate-800 dark:text-navy-50">Admin Panel</p>
                <p class="truncate text-tiny text-slate-500 dark:text-navy-300">My Store</p>
            </div>
        </div>

        <!-- Nav -->
        <nav class="is-scrollbar-hidden mt-2 flex-1 overflow-y-auto px-3 pb-3">
            <ul class="flex flex-col gap-1">
                <!-- Dashboard -->
                <li>
                    <Link href="/admin/dashboard"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/dashboard') ? 'bg-primary/20 font-medium text-primary dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-7 shrink-0 items-center justify-center rounded-lg bg-secondary/10 dark:bg-secondary">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M9.85714 3H4.14286C3.51167 3 3 3.51167 3 4.14286V9.85714C3 10.4883 3.51167 11 4.14286 11H9.85714C10.4883 11 11 10.4883 11 9.85714V4.14286C11 3.51167 10.4883 3 9.85714 3Z"
                                fill="currentColor"></path>
                            <path
                                d="M9.85714 12.8999H4.14286C3.51167 12.8999 3 13.4116 3 14.0428V19.757C3 20.3882 3.51167 20.8999 4.14286 20.8999H9.85714C10.4883 20.8999 11 20.3882 11 19.757V14.0428C11 13.4116 10.4883 12.8999 9.85714 12.8999Z"
                                fill="currentColor" fill-opacity="0.3"></path>
                            <path
                                d="M19.757 3H14.0428C13.4116 3 12.8999 3.51167 12.8999 4.14286V9.85714C12.8999 10.4883 13.4116 11 14.0428 11H19.757C20.3882 11 20.8999 10.4883 20.8999 9.85714V4.14286C20.8999 3.51167 20.3882 3 19.757 3Z"
                                fill="currentColor" fill-opacity="0.3"></path>
                            <path
                                d="M19.757 12.8999H14.0428C13.4116 12.8999 12.8999 13.4116 12.8999 14.0428V19.757C12.8999 20.3882 13.4116 20.8999 14.0428 20.8999H19.757C20.3882 20.8999 20.8999 20.3882 20.8999 19.757V14.0428C20.8999 13.4116 20.3882 12.8999 19.757 12.8999Z"
                                fill="currentColor" fill-opacity="0.3"></path>
                        </svg>
                        </span>
                        <span class="truncate">Dashboard</span>
                    </Link>
                </li>

                <!-- Products -->
                <li>
                    <Link href="/admin/products"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/products') ? 'bg-warning/20 font-medium text-warning dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-7 shrink-0 items-center justify-center rounded-lg bg-warning/10 dark:bg-warning">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M16 12.9V17.1C16 20.6 14.6 22 11.1 22H6.9C3.4 22 2 20.6 2 17.1V12.9C2 9.4 3.4 8 6.9 8H11.1C14.6 8 16 9.4 16 12.9Z"
                                fill="currentColor"></path>
                            <path opacity="0.4"
                                d="M17.0998 2H12.8998C9.44976 2 8.04977 3.37 8.00977 6.75H11.0998C15.2998 6.75 17.2498 8.7 17.2498 12.9V15.99C20.6298 15.95 21.9998 14.55 21.9998 11.1V6.9C21.9998 3.4 20.5998 2 17.0998 2Z"
                                fill="currentColor"></path>
                        </svg>
                        </span>
                        <span class="truncate">Products</span>
                    </Link>
                </li>

                <!-- Categories -->
                <li>
                    <Link href="/admin/categories"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/categories') ? 'bg-primary/20 font-medium text-primary dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-7 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-opacity="0.25"
                                d="M21.0001 16.05V18.75C21.0001 20.1 20.1001 21 18.7501 21H6.6001C6.9691 21 7.3471 20.946 7.6981 20.829C7.7971 20.793 7.89609 20.757 7.99509 20.712C8.31009 20.586 8.61611 20.406 8.88611 20.172C8.96711 20.109 9.05711 20.028 9.13811 19.947L9.17409 19.911L15.2941 13.8H18.7501C20.1001 13.8 21.0001 14.7 21.0001 16.05Z"
                                fill="currentColor"></path>
                            <path fill-opacity="0.5"
                                d="M17.7324 11.361L15.2934 13.8L9.17334 19.9111C9.80333 19.2631 10.1993 18.372 10.1993 17.4V8.70601L12.6384 6.26701C13.5924 5.31301 14.8704 5.31301 15.8244 6.26701L17.7324 8.17501C18.6864 9.12901 18.6864 10.407 17.7324 11.361Z"
                                fill="currentColor"></path>
                            <path
                                d="M7.95 3H5.25C3.9 3 3 3.9 3 5.25V17.4C3 17.643 3.02699 17.886 3.07199 18.12C3.09899 18.237 3.12599 18.354 3.16199 18.471C3.20699 18.606 3.252 18.741 3.306 18.867C3.315 18.876 3.31501 18.885 3.31501 18.885C3.32401 18.885 3.32401 18.885 3.31501 18.894C3.44101 19.146 3.585 19.389 3.756 19.614C3.855 19.731 3.95401 19.839 4.05301 19.947C4.15201 20.055 4.26 20.145 4.377 20.235L4.38601 20.244C4.61101 20.415 4.854 20.559 5.106 20.685C5.115 20.676 5.11501 20.676 5.11501 20.685C5.25001 20.748 5.385 20.793 5.529 20.838C5.646 20.874 5.76301 20.901 5.88001 20.928C6.11401 20.973 6.357 21 6.6 21C6.969 21 7.347 20.946 7.698 20.829C7.797 20.793 7.89599 20.757 7.99499 20.712C8.30999 20.586 8.61601 20.406 8.88601 20.172C8.96701 20.109 9.05701 20.028 9.13801 19.947L9.17399 19.911C9.80399 19.263 10.2 18.372 10.2 17.4V5.25C10.2 3.9 9.3 3 7.95 3ZM6.6 18.75C5.853 18.75 5.25 18.147 5.25 17.4C5.25 16.653 5.853 16.05 6.6 16.05C7.347 16.05 7.95 16.653 7.95 17.4C7.95 18.147 7.347 18.75 6.6 18.75Z"
                                fill="currentColor"></path>
                        </svg>
                        </span>
                        <span class="truncate">Categories</span>
                    </Link>
                </li>

                <!-- Brands -->
                <li>
                    <Link href="/admin/brands"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/brands') ? 'bg-success/20 font-medium text-success dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-7 shrink-0 items-center justify-center rounded-lg bg-success/10 dark:bg-success">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-opacity="0.5"
                                d="M14.2498 16C14.2498 17.5487 13.576 18.9487 12.4998 19.9025C11.5723 20.7425 10.3473 21.25 8.99976 21.25C6.10351 21.25 3.74976 18.8962 3.74976 16C3.74976 13.585 5.39476 11.5375 7.61726 10.9337C8.22101 12.4562 9.51601 13.6287 11.1173 14.0662C11.5548 14.1887 12.0185 14.25 12.4998 14.25C12.981 14.25 13.4448 14.1887 13.8823 14.0662C14.1185 14.6612 14.2498 15.3175 14.2498 16Z"
                                fill="currentColor"></path>
                            <path
                                d="M17.75 9.00012C17.75 9.68262 17.6187 10.3389 17.3825 10.9339C16.7787 12.4564 15.4837 13.6289 13.8825 14.0664C13.445 14.1889 12.9813 14.2501 12.5 14.2501C12.0187 14.2501 11.555 14.1889 11.1175 14.0664C9.51625 13.6289 8.22125 12.4564 7.6175 10.9339C7.38125 10.3389 7.25 9.68262 7.25 9.00012C7.25 6.10387 9.60375 3.75012 12.5 3.75012C15.3962 3.75012 17.75 6.10387 17.75 9.00012Z"
                                fill="currentColor"></path>
                            <path fill-opacity="0.3"
                                d="M21.25 16C21.25 18.8962 18.8962 21.25 16 21.25C14.6525 21.25 13.4275 20.7425 12.5 19.9025C13.5763 18.9487 14.25 17.5487 14.25 16C14.25 15.3175 14.1187 14.6612 13.8825 14.0662C15.4837 13.6287 16.7787 12.4562 17.3825 10.9337C19.605 11.5375 21.25 13.585 21.25 16Z"
                                fill="currentColor"></path>
                        </svg>
                        </span>
                        <span class="truncate">Brands</span>
                    </Link>
                </li>

                <!-- Attributes -->
                <li>
                    <Link href="/admin/attributes"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/attributes') ? 'bg-warning/20 font-medium text-warning dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-7 shrink-0 items-center justify-center rounded-lg bg-warning/10 dark:bg-warning">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.4"
                                d="M18.5094 1.7998C18.0094 1.7998 17.6094 2.1998 17.6094 2.6998V7.3498C17.6094 7.8498 18.0094 8.2498 18.5094 8.2498C19.0094 8.2498 19.4094 7.8498 19.4094 7.3498V2.6998C19.4094 2.1998 19.0094 1.7998 18.5094 1.7998Z"
                                fill="currentColor"></path>
                            <path
                                d="M11.9996 15.75C11.4996 15.75 11.0996 16.15 11.0996 16.65V21.3C11.0996 21.8 11.4996 22.2 11.9996 22.2C12.4996 22.2 12.8996 21.8 12.8996 21.3V16.65C12.8996 16.16 12.4996 15.75 11.9996 15.75Z"
                                fill="currentColor"></path>
                            <path opacity="0.4"
                                d="M5.48984 1.7998C4.98984 1.7998 4.58984 2.1998 4.58984 2.6998V7.3498C4.58984 7.8498 4.98984 8.2498 5.48984 8.2498C5.98984 8.2498 6.38984 7.8498 6.38984 7.3498V2.6998C6.38984 2.1998 5.97984 1.7998 5.48984 1.7998Z"
                                fill="currentColor"></path>
                            <path opacity="0.4"
                                d="M7.35047 10.1699H3.63047C3.13047 10.1699 2.73047 10.5699 2.73047 11.0699C2.73047 11.5699 3.13047 11.9699 3.63047 11.9699H4.59047V21.2999C4.59047 21.7999 4.99047 22.1999 5.49047 22.1999C5.99047 22.1999 6.39047 21.7999 6.39047 21.2999V11.9699H7.35047C7.85047 11.9699 8.25047 11.5699 8.25047 11.0699C8.25047 10.5699 7.84047 10.1699 7.35047 10.1699Z"
                                fill="currentColor"></path>
                            <path opacity="0.4"
                                d="M20.37 10.1699H16.65C16.15 10.1699 15.75 10.5699 15.75 11.0699C15.75 11.5699 16.15 11.9699 16.65 11.9699H17.61V21.2999C17.61 21.7999 18.01 22.1999 18.51 22.1999C19.01 22.1999 19.41 21.7999 19.41 21.2999V11.9699H20.37C20.87 11.9699 21.27 11.5699 21.27 11.0699C21.27 10.5699 20.87 10.1699 20.37 10.1699Z"
                                fill="currentColor"></path>
                            <path
                                d="M13.8602 12.0298H12.9002V2.6998C12.9002 2.1998 12.5002 1.7998 12.0002 1.7998C11.5002 1.7998 11.1002 2.1998 11.1002 2.6998V12.0298H10.1402C9.64023 12.0298 9.24023 12.4298 9.24023 12.9298C9.24023 13.4298 9.64023 13.8298 10.1402 13.8298H13.8602C14.3602 13.8298 14.7602 13.4298 14.7602 12.9298C14.7602 12.4298 14.3602 12.0298 13.8602 12.0298Z"
                                fill="currentColor"></path>
                        </svg>
                        </span>
                        <span class="truncate">Attributes</span>
                    </Link>
                </li>

                <!-- Inventory -->
                <li>
                    <Link href="/admin/inventory"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/inventory') ? 'bg-primary/20 font-medium text-primary dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-7 shrink-0 items-center justify-center rounded-lg bg-secondary/10 dark:bg-secondary">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.4"
                                d="M21.9897 6.01996C22.0097 6.25996 21.9897 6.50995 21.9297 6.75995L18.5597 20.2899C18.3197 21.2999 17.4198 21.9999 16.3798 21.9999H3.23974C1.72974 21.9999 0.659755 20.5199 1.09976 19.0699L5.30975 5.53992C5.59975 4.59992 6.46976 3.95996 7.44976 3.95996H19.7498C20.7097 3.95996 21.4898 4.52996 21.8198 5.32996C21.9198 5.53996 21.9697 5.77996 21.9897 6.01996Z"
                                fill="currentColor"></path>
                            <path
                                d="M22.9908 19.62C23.0908 20.91 22.0709 22 20.7809 22H16.3809C17.4209 22 18.3209 21.3 18.5609 20.29L21.9308 6.76001C21.9908 6.51001 22.0108 6.26002 21.9908 6.02002L22.0009 6L22.9908 19.62Z"
                                fill="currentColor"></path>
                            <path
                                d="M9.67977 7.12989C9.61977 7.12989 9.55977 7.11987 9.49977 7.10987C9.09977 7.00987 8.84979 6.6099 8.94979 6.1999L9.98976 1.87989C10.0898 1.47989 10.4898 1.2399 10.8998 1.3299C11.2998 1.4299 11.5498 1.82987 11.4498 2.23987L10.4098 6.55988C10.3298 6.89988 10.0198 7.12989 9.67977 7.12989Z"
                                fill="currentColor"></path>
                            <path
                                d="M16.3795 7.1398C16.3295 7.1398 16.2694 7.13978 16.2194 7.11978C15.8194 7.02978 15.5595 6.62977 15.6395 6.22977L16.5794 1.8898C16.6694 1.4798 17.0694 1.22978 17.4694 1.30978C17.8694 1.39978 18.1294 1.7998 18.0494 2.1998L17.1094 6.53976C17.0394 6.89977 16.7295 7.1398 16.3795 7.1398Z"
                                fill="currentColor"></path>
                            <path
                                d="M15.6992 12.75H7.69922C7.28922 12.75 6.94922 12.41 6.94922 12C6.94922 11.59 7.28922 11.25 7.69922 11.25H15.6992C16.1092 11.25 16.4492 11.59 16.4492 12C16.4492 12.41 16.1092 12.75 15.6992 12.75Z"
                                fill="currentColor"></path>
                            <path
                                d="M14.6992 16.75H6.69922C6.28922 16.75 5.94922 16.41 5.94922 16C5.94922 15.59 6.28922 15.25 6.69922 15.25H14.6992C15.1092 15.25 15.4492 15.59 15.4492 16C15.4492 16.41 15.1092 16.75 14.6992 16.75Z"
                                fill="currentColor"></path>
                        </svg>
                        </span>
                        <span class="truncate">Inventory</span>
                    </Link>
                </li>

                <!-- Orders -->
                <li>
                    <Link href="/admin/orders"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/orders') ? 'bg-success/20 font-medium text-success dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-7 shrink-0 items-center justify-center rounded-lg bg-success/10 dark:bg-success">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M16.25 22.5C17.2165 22.5 18 21.7165 18 20.75C18 19.7835 17.2165 19 16.25 19C15.2835 19 14.5 19.7835 14.5 20.75C14.5 21.7165 15.2835 22.5 16.25 22.5Z"
                                fill="currentColor"></path>
                            <path
                                d="M8.25 22.5C9.2165 22.5 10 21.7165 10 20.75C10 19.7835 9.2165 19 8.25 19C7.2835 19 6.5 19.7835 6.5 20.75C6.5 21.7165 7.2835 22.5 8.25 22.5Z"
                                fill="currentColor"></path>
                            <path opacity="0.4"
                                d="M4.84 3.94L4.64 6.39C4.6 6.86 4.97 7.25 5.44 7.25H20.75C21.17 7.25 21.52 6.92999 21.55 6.50999C21.68 4.73999 20.33 3.3 18.56 3.3H6.28999C6.18999 2.86 5.98999 2.44 5.67999 2.09C5.18999 1.56 4.49 1.25 3.77 1.25H2C1.59 1.25 1.25 1.59 1.25 2C1.25 2.41 1.59 2.75 2 2.75H3.74001C4.05001 2.75 4.34 2.88001 4.55 3.10001C4.76 3.33001 4.86 3.63 4.84 3.94Z"
                                fill="currentColor"></path>
                            <path
                                d="M20.5101 8.75H5.17006C4.75006 8.75 4.41005 9.07 4.37005 9.48L4.01005 13.83C3.87005 15.53 5.21006 17 6.92006 17H18.0401C19.5401 17 20.8601 15.77 20.9701 14.27L21.3001 9.60001C21.3401 9.14001 20.9801 8.75 20.5101 8.75Z"
                                fill="currentColor"></path>
                        </svg>
                        </span>
                        <span class="truncate">Orders</span>
                    </Link>
                </li>

                <!-- Shipping -->
                <li>
                    <Link href="/admin/shipping"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/shipping') ? 'bg-secondary/20 font-medium text-secondary dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-7 shrink-0 items-center justify-center rounded-lg bg-secondary/10 dark:bg-secondary">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 3V12C15 13.1 14.1 14 13 14H2V6C2 3.79 3.79 2 6 2H14C14.55 2 15 2.45 15 3Z"
                                fill="currentColor"></path>
                            <path opacity="0.4"
                                d="M22 14V17C22 18.66 20.66 20 19 20H18C18 18.9 17.1 18 16 18C14.9 18 14 18.9 14 20H10C10 18.9 9.1 18 8 18C6.9 18 6 18.9 6 20H5C3.34 20 2 18.66 2 17V14H13C14.1 14 15 13.1 15 12V5H16.84C17.56 5 18.22 5.39001 18.58 6.01001L20.29 9H19C18.45 9 18 9.45 18 10V13C18 13.55 18.45 14 19 14H22Z"
                                fill="currentColor"></path>
                            <path
                                d="M8 22C9.10457 22 10 21.1046 10 20C10 18.8954 9.10457 18 8 18C6.89543 18 6 18.8954 6 20C6 21.1046 6.89543 22 8 22Z"
                                fill="currentColor"></path>
                            <path
                                d="M16 22C17.1046 22 18 21.1046 18 20C18 18.8954 17.1046 18 16 18C14.8954 18 14 18.8954 14 20C14 21.1046 14.8954 22 16 22Z"
                                fill="currentColor"></path>
                            <path
                                d="M22 12.53V14H19C18.45 14 18 13.55 18 13V10C18 9.45 18.45 9 19 9H20.29L21.74 11.54C21.91 11.84 22 12.18 22 12.53Z"
                                fill="currentColor"></path>
                        </svg>
                        </span>
                        <span class="truncate">Shipping</span>
                    </Link>
                </li>

                <!-- Coupons -->
                <li>
                    <Link href="/admin/coupons"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/coupons') ? 'bg-warning/20 font-medium text-warning dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-7 shrink-0 items-center justify-center rounded-lg bg-warning/10 dark:bg-warning">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M5 15C4.06 15 3.19 15.33 2.5 15.88C1.58 16.61 1 17.74 1 19C1 19.75 1.21 20.46 1.58 21.06C2.27 22.22 3.54 23 5 23C6.01 23 6.93001 22.63 7.63 22C7.94 21.74 8.21 21.42 8.42 21.06C8.79 20.46 9 19.75 9 19C9 16.79 7.21 15 5 15ZM7.07001 18.57L4.94 20.54C4.8 20.67 4.60999 20.74 4.42999 20.74C4.23999 20.74 4.04999 20.67 3.89999 20.52L2.91 19.53C2.62 19.24 2.62 18.76 2.91 18.47C3.2 18.18 3.68 18.18 3.97 18.47L4.45 18.95L6.05 17.47C6.35 17.19 6.83 17.21 7.11 17.51C7.39 17.81 7.37001 18.29 7.07001 18.57Z"
                                fill="currentColor"></path>
                            <path opacity="0.4"
                                d="M19.48 12.95H21.5V11.51C21.5 9.44001 19.81 7.75 17.74 7.75H6.25999C4.18999 7.75 2.5 9.44001 2.5 11.51V15.88C3.19 15.33 4.06 15 5 15C7.21 15 9 16.79 9 19C9 19.75 8.79 20.46 8.42 21.06C8.21 21.42 7.94 21.74 7.63 22H17.74C19.81 22 21.5 20.31 21.5 18.24V17.05H19.6C18.52 17.05 17.53 16.26 17.44 15.18C17.38 14.55 17.62 13.96 18.04 13.55C18.41 13.17 18.92 12.95 19.48 12.95Z"
                                fill="currentColor"></path>
                            <path
                                d="M14.85 3.95012V7.75011H6.25999C4.18999 7.75011 2.5 9.44012 2.5 11.5101V7.84014C2.5 6.65014 3.23 5.59009 4.34 5.17009L12.28 2.17009C13.52 1.71009 14.85 2.62012 14.85 3.95012Z"
                                fill="currentColor"></path>
                            <path
                                d="M22.5608 13.9702V16.0302C22.5608 16.5802 22.1208 17.0302 21.5608 17.0502H19.6008C18.5208 17.0502 17.5308 16.2602 17.4408 15.1802C17.3808 14.5502 17.6208 13.9602 18.0408 13.5502C18.4108 13.1702 18.9208 12.9502 19.4808 12.9502H21.5608C22.1208 12.9702 22.5608 13.4202 22.5608 13.9702Z"
                                fill="currentColor"></path>
                            <path
                                d="M14 12.75H7C6.59 12.75 6.25 12.41 6.25 12C6.25 11.59 6.59 11.25 7 11.25H14C14.41 11.25 14.75 11.59 14.75 12C14.75 12.41 14.41 12.75 14 12.75Z"
                                fill="currentColor"></path>
                        </svg>
                        </span>
                        <span class="truncate">Coupons</span>
                    </Link>
                </li>

                <!-- Reports -->
                <li>
                    <Link href="/admin/reports"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/reports') ? 'bg-primary/20 font-medium text-primary dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-7 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M22 22H2C1.59 22 1.25 21.66 1.25 21.25C1.25 20.84 1.59 20.5 2 20.5H22C22.41 20.5 22.75 20.84 22.75 21.25C22.75 21.66 22.41 22 22 22Z"
                                fill="currentColor"></path>
                            <path d="M9.75 4V22H14.25V4C14.25 2.9 13.8 2 12.45 2H11.55C10.2 2 9.75 2.9 9.75 4Z"
                                fill="currentColor"></path>
                            <path opacity="0.4" d="M3 10V22H7V10C7 8.9 6.6 8 5.4 8H4.6C3.4 8 3 8.9 3 10Z"
                                fill="currentColor"></path>
                            <path opacity="0.4" d="M17 15V22H21V15C21 13.9 20.6 13 19.4 13H18.6C17.4 13 17 13.9 17 15Z"
                                fill="currentColor"></path>
                        </svg>
                        </span>
                        <span class="truncate">Reports</span>
                    </Link>
                </li>

                <!-- Banners -->
                <li>
                    <Link href="/admin/banners"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/banners') ? 'bg-warning/20 font-medium text-warning dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-7 shrink-0 items-center justify-center rounded-lg bg-warning/10 dark:bg-warning">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M13.3111 14.75H5.03356C3.36523 14.75 2.30189 12.9625 3.10856 11.4958L5.24439 7.60911L7.24273 3.96995C8.07689 2.45745 10.2586 2.45745 11.0927 3.96995L13.1002 7.60911L14.0627 9.35995L15.2361 11.4958C16.0427 12.9625 14.9794 14.75 13.3111 14.75Z"
                                fill="currentColor"></path>
                            <path fill-opacity="0.3"
                                d="M21.1667 15.2083C21.1667 18.4992 18.4992 21.1667 15.2083 21.1667C11.9175 21.1667 9.25 18.4992 9.25 15.2083C9.25 15.0525 9.25917 14.9058 9.26833 14.75H13.3108C14.9792 14.75 16.0425 12.9625 15.2358 11.4958L14.0625 9.36C14.4292 9.28666 14.8142 9.25 15.2083 9.25C18.4992 9.25 21.1667 11.9175 21.1667 15.2083Z"
                                fill="currentColor"></path>
                        </svg>
                        </span>
                        <span class="truncate">Banners</span>
                    </Link>
                </li>

                <!-- Settings -->
                <li>
                    <Link href="/admin/settings"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/settings') ? 'bg-primary/20 font-medium text-primary dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-7 shrink-0 items-center justify-center rounded-lg bg-secondary/10 dark:bg-secondary">
                            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-opacity="0.3" fill="currentColor"
                                    d="M2 12.947v-1.771c0-1.047.85-1.913 1.899-1.913 1.81 0 2.549-1.288 1.64-2.868a1.919 1.919 0 0 1 .699-2.607l1.729-.996c.79-.474 1.81-.192 2.279.603l.11.192c.9 1.58 2.379 1.58 3.288 0l.11-.192c.47-.795 1.49-1.077 2.279-.603l1.73.996a1.92 1.92 0 0 1 .699 2.607c-.91 1.58-.17 2.868 1.639 2.868 1.04 0 1.899.856 1.899 1.912v1.772c0 1.047-.85 1.912-1.9 1.912-1.808 0-2.548 1.288-1.638 2.869.52.915.21 2.083-.7 2.606l-1.729.997c-.79.473-1.81.191-2.279-.604l-.11-.191c-.9-1.58-2.379-1.58-3.288 0l-.11.19c-.47.796-1.49 1.078-2.279.605l-1.73-.997a1.919 1.919 0 0 1-.699-2.606c.91-1.58.17-2.869-1.639-2.869A1.911 1.911 0 0 1 2 12.947Z">
                                </path>
                                <path fill="currentColor"
                                    d="M11.995 15.332c1.794 0 3.248-1.464 3.248-3.27 0-1.807-1.454-3.272-3.248-3.272-1.794 0-3.248 1.465-3.248 3.271 0 1.807 1.454 3.271 3.248 3.271Z">
                                </path>
                            </svg>
                        </span>

                        <span class="truncate">Settings</span>
                    </Link>
                </li>
            </ul>
        </nav>

        <!-- Footer -->
        <footer class="shrink-0 border-t border-slate-150 p-3 dark:border-navy-700">
            <div
                class="group flex items-center space-x-3 py-2 shrink-0 px-3 pt-3 tracking-wide outline-hidden transition-all bg-slate-100 dark:bg-navy-800">
                <div class="flex size-8 items-center justify-center rounded-lg bg-warning text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>

                <div>
                    <h2
                        class="font-medium text-slate-700 transition-colors group-hover:text-primary group-focus:text-primary dark:text-navy-100 dark:group-hover:text-accent-light dark:group-focus:text-accent-light">
                        {{ user?.name ?? 'Administrator' }}
                    </h2>
                    <div class="text-xs text-slate-400 line-clamp-1 dark:text-navy-300">
                        Administrator
                    </div>
                </div>
            </div>
            <Link href="/"
                class="group flex items-center gap-3 rounded-lg px-3 py-2 mt-2 text-xs-plus font-medium text-slate-600 transition-colors duration-200 hover:bg-primary/20 hover:text-primary focus:bg-primary/20 focus:text-primary active:bg-primary/25 dark:text-navy-200 dark:hover:bg-navy-300/20 dark:hover:text-accent-light dark:focus:bg-navy-300/20 dark:focus:text-accent-light dark:active:bg-navy-300/25">
                <span
                            class="relative flex size-8 shrink-0 items-center justify-center rounded-lg bg-secondary/10 dark:bg-secondary">
                    🛍️
                </span>
                <span class="flex-1 truncate">View storefront</span>
                <ArrowRight
                    class="size-4 shrink-0 -translate-x-1 opacity-0 transition-all duration-200 group-hover:translate-x-0 group-hover:opacity-100" />
            </Link>

        </footer>
    </aside>

    <!-- Desktop spacer so main content is pushed right of the fixed sidebar -->
    <div class="hidden w-[260px] shrink-0 md:block" aria-hidden="true"></div>
</template>