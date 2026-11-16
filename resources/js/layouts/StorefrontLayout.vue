<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    Search,
    ShoppingBag,
    Menu,
    X,
    ChevronDown,
    Home,
    Package,
    Settings,
    MapPin,
    Star,
    LogOut,
    User
} from '@lucide/vue';
import { admin_dashboard, customer_orders_index, customer_products_index, home } from '@/routes';

interface CategoryNode {
    id: string;
    name: string;
    slug: string;
    children?: { id: string; name: string; slug: string }[];
}

const page = usePage<{
    auth: { user: { id: string; name: string; email: string; role: string } | null };
    flash: { success?: string; error?: string; warning?: string; info?: string };
    cart_count: number;
    categories?: CategoryNode[];
}>();

const auth = computed(() => page.props.auth);
const categories = computed(() => page.props.categories ?? []);
const cartCount = computed(() => page.props.cart_count ?? 0);
const currentPath = computed(() => page.url.split('?')[0]);

const firstName = computed(() => {
    const name = auth.value?.user?.name ?? '';
    return name.split(' ')[0] || 'Account';
});

/* ---------- Category href helper ---------- */
function categoryHref(slug: string): string {
    const params = new URLSearchParams();
    params.append('categories[]', slug);
    return `/customer/products?${params.toString()}`;
}

/* Mobile menu */
const mobileMenuOpen = ref(false);

watch(mobileMenuOpen, (isOpen) => {
    if (typeof document !== 'undefined') {
        document.body.style.overflow = isOpen ? 'hidden' : '';
    }
});

/* Search */
const search = ref('');
function submitSearch() {
    const trimmed = search.value.trim();
    router.get('/customer/products', trimmed ? { search: trimmed } : {}, {
        preserveState: false,
        replace: true,
    });
}

/* Account dropdown */
const accountOpen = ref(false);
function toggleAccount() {
    accountOpen.value = !accountOpen.value;
}
function logout() {
    router.post('/logout');
}

/* Category dropdown */
const categoriesOpen = ref(false);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-slate-50 text-slate-700 dark:bg-navy-900 dark:text-navy-100">

        <!-- Top utility bar -->
        <div
            class="hidden border-b border-slate-150 bg-slate-100/60 text-xs text-slate-400 dark:border-navy-600 dark:bg-navy-900/50 dark:text-navy-300 md:block">
            <div class="mx-auto flex h-9 w-full max-w-[1400px] items-center justify-between px-4 sm:px-6 lg:px-8">
                <p>Free delivery on orders above KES 5,000</p>
                <div class="flex items-center gap-4">
                    <Link href="/customer/orders"
                        class="transition-colors hover:text-primary dark:hover:text-accent-light">Track order</Link>
                    <Link href="/contact" class="transition-colors hover:text-primary dark:hover:text-accent-light">Help
                    </Link>
                </div>
            </div>
        </div>

        <!-- Sticky Header -->
        <header
            class="sticky top-0 z-40 border-b border-slate-150 bg-white/90 backdrop-blur-md dark:border-navy-600 dark:bg-navy-750/90">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center gap-3">

                    <!-- Mobile Menu Trigger -->
                    <button type="button"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-700 hover:bg-slate-200 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100 lg:hidden"
                        aria-label="Open menu" @click="mobileMenuOpen = true">
                        <Menu class="size-5" />
                    </button>

                    <!-- Brand -->
                    <Link href="/"
                        class="flex shrink-0 items-center gap-2.5 text-lg font-bold text-slate-800 dark:text-navy-50">
                        <span
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary font-bold text-white shadow-md dark:bg-accent">
                            <ShoppingBag class="size-4.5" />
                        </span>
                        <span class="hidden sm:inline">My Store</span>
                    </Link>

                    <!-- Desktop Search -->
                    <form class="mx-auto hidden w-full max-w-xl md:block" @submit.prevent="submitSearch">
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                            <input v-model="search" type="search" placeholder="Search products, categories…"
                                class="form-input w-full rounded-lg bg-slate-150 px-3.5 py-2.5 pl-10 text-sm ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:text-navy-100" />
                        </div>
                    </form>

                    <!-- Right actions -->
                    <div class="ml-auto flex items-center gap-3">
                        <Link href="/cart"
                            class="relative flex size-10 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-100 text-slate-700 transition-colors hover:bg-slate-200 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100"
                            title="Shopping Cart">
                            <span class="text-2xl">🛍️</span>
                            <span v-if="cartCount > 0"
                                class="absolute -right-1 -top-1 flex size-5 items-center justify-center rounded-full bg-error text-[10px] font-bold text-white shadow">
                                {{ cartCount }}
                            </span>
                        </Link>

                        <template v-if="auth?.user">
                            <div class="relative hidden lg:block">
                                <button type="button"
                                    class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-150 dark:text-navy-100 dark:hover:bg-navy-700 sm:px-3"
                                    @click="toggleAccount">
                                    <span
                                        class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                        {{ firstName.charAt(0).toUpperCase() }}
                                    </span>
                                    <span class="hidden sm:inline">{{ firstName }}</span>
                                    <ChevronDown class="hidden size-3.5 text-slate-400 sm:inline" />
                                </button>

                                <div v-if="accountOpen"
                                    class="absolute right-0 mt-2 w-56 rounded-xl border border-slate-150 bg-white py-1.5 shadow-xl dark:border-navy-600 dark:bg-navy-750"
                                    @mouseleave="accountOpen = false">

                                    <Link href="/customer/account" v-if="auth?.user?.role != 'admin'"
                                        class="flex items-center gap-2.5 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-700">
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-secondary/10 dark:bg-secondary-light/15">
                                            <span class="text-sm">✏️</span>
                                        </div> My account
                                    </Link>

                                    <Link href="/customer/orders" v-if="auth?.user?.role != 'admin'"
                                        class="flex items-center gap-2.5 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-700">
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-info/10 dark:bg-info/15">
                                            <Package class="size-4.5 text-info" />
                                        </div> Orders
                                    </Link>

                                    <Link href="/customer/addresses" v-if="auth?.user?.role != 'admin'"
                                        class="flex items-center gap-2.5 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-700">
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-warning/10 dark:bg-warning/15">
                                            <span class="text-sm">🧭</span>
                                        </div> Addresses
                                    </Link>

                                    <Link href="/customer/reviews" v-if="auth?.user?.role != 'admin'"
                                        class="flex items-center gap-2.5 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-700">
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-warning/10 dark:bg-warning/15">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5 text-warning"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z">
                                                </path>
                                            </svg>
                                        </div> Reviews
                                    </Link>

                                    <Link v-if="auth?.user?.role === 'admin'" :href="admin_dashboard.url()"
                                        class="flex items-center gap-2.5 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-700">
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 dark:bg-navy-700">
                                            <Settings class="size-4.5 text-slate-400" />
                                        </div> Admin panel
                                    </Link>

                                    <div class="my-1 border-t border-slate-150 dark:border-navy-600"></div>

                                    <button type="button"
                                        class="flex w-full items-center gap-2.5 px-4 py-1.5 text-left text-sm font-medium text-error transition-colors hover:bg-error/10 cursor-pointer"
                                        @click="logout">
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-error/10 dark:bg-error/15">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="size-4.5 text-rose-400 transition-colors hover:text-error"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                            </svg>
                                        </div> Sign out
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <Link href="/login"
                                class="btn btn-sm hidden bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-navy-700 dark:text-navy-100 sm:flex">
                                Login</Link>
                            <Link href="/register"
                                class="btn btn-sm hidden bg-primary text-white hover:bg-primary-focus dark:bg-accent sm:flex">
                                Sign up
                            </Link>
                        </template>
                    </div>
                </div>

                <!-- Mobile Search -->
                <div class="pb-3 md:hidden">
                    <form @submit.prevent="submitSearch">
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                            <input v-model="search" type="search" placeholder="Search products…"
                                class="form-input w-full rounded-lg bg-slate-150 px-3.5 py-2 pl-10 text-sm ring-primary/50 placeholder:text-slate-400 dark:bg-navy-900 dark:text-navy-100" />
                        </div>
                    </form>
                </div>
            </div>

            <!-- Secondary Navbar (Categories) -->
            <nav class="border-t border-slate-150 bg-white/50 dark:border-navy-600 dark:bg-navy-750/50 md:block">
                <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 lg:px-8">
                    <div
                        class="scrollbar-none flex h-11 items-center gap-2 overflow-x-auto whitespace-nowrap text-sm font-medium">
                        <Link :href="home.url()"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                            <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <path fill="currentColor" fill-opacity=".3"
                                    d="M5 14.059c0-1.01 0-1.514.222-1.945.221-.43.632-.724 1.453-1.31l4.163-2.974c.56-.4.842-.601 1.162-.601.32 0 .601.2 1.162.601l4.163 2.974c.821.586 1.232.88 1.453 1.31.222.43.222.935.222 1.945V19c0 .943 0 1.414-.293 1.707C18.414 21 17.943 21 17 21H7c-.943 0-1.414 0-1.707-.293C5 20.414 5 19.943 5 19v-4.94Z">
                                </path>
                                <path fill="currentColor"
                                    d="M3 12.387c0 .267 0 .4.084.441.084.041.19-.04.4-.204l7.288-5.669c.59-.459.885-.688 1.228-.688.343 0 .638.23 1.228.688l7.288 5.669c.21.163.316.245.4.204.084-.04.084-.174.084-.441v-.409c0-.48 0-.72-.102-.928-.101-.208-.291-.355-.67-.65l-7-5.445c-.59-.459-.885-.688-1.228-.688-.343 0-.638.23-1.228.688l-7 5.445c-.379.295-.569.442-.67.65-.102.208-.102.448-.102.928v.409Z">
                                </path>
                                <path fill="currentColor"
                                    d="M11.5 15.5h1A1.5 1.5 0 0 1 14 17v3.5h-4V17a1.5 1.5 0 0 1 1.5-1.5Z">
                                </path>
                                <path fill="currentColor"
                                    d="M17.5 5h-1a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5Z">
                                </path>
                            </svg>
                            <span class="truncate">Home</span>
                        </Link>

                        <Link :href="customer_products_index.url()"
                            class="shrink-0 rounded-lg px-3 py-1.5 transition-colors"
                            :class="currentPath === '/customer/products' ? 'bg-primary/10 font-semibold text-primary dark:bg-accent-light/10 dark:text-accent-light' : 'text-slate-600 hover:bg-slate-150 dark:text-navy-200 dark:hover:bg-navy-700'">
                            All Products
                        </Link>

                        <template v-if="categories.length">
                            <template v-for="cat in categories.slice(0, 6)" :key="cat.id">
                                <Link :href="categoryHref(cat.slug)"
                                    class="shrink-0 rounded-lg px-3 py-1.5 text-slate-600 transition-colors hover:bg-slate-150 hover:text-slate-900 dark:text-navy-200 dark:hover:bg-navy-700 dark:hover:text-navy-50">
                                    {{ cat.name }}
                                </Link>
                            </template>

                            <!-- More Categories Dropdown -->
                            <div v-if="categories.length > 6" class="relative shrink-0">
                                <button type="button"
                                    class="flex items-center gap-1 rounded-lg px-3 py-1.5 text-slate-600 transition-colors hover:bg-slate-150 hover:text-slate-900 dark:text-navy-200 dark:hover:bg-navy-750"
                                    @click="categoriesOpen = !categoriesOpen">
                                    More
                                    <ChevronDown class="size-3" />
                                </button>
                                <div v-if="categoriesOpen"
                                    class="absolute left-0 top-full z-50 mt-1 w-56 rounded-xl border border-slate-150 bg-white py-2 shadow-xl dark:border-navy-600 dark:bg-navy-750"
                                    @mouseleave="categoriesOpen = false">
                                    <Link v-for="cat in categories.slice(6)" :key="cat.id"
                                        :href="categoryHref(cat.slug)"
                                        class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-700">
                                        {{ cat.name }}
                                    </Link>
                                </div>
                            </div>
                        </template>

                        <div class="ml-auto flex shrink-0 items-center gap-4 text-xs">
                            <Link :href="customer_orders_index.url()"
                                class="flex text-center text-slate-400 transition-colors hover:text-slate-700 dark:text-navy-300 dark:hover:text-navy-100">
                                <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M9.96055 14.0401V12.9301C9.96055 12.5001 9.64055 12.2901 9.25055 12.4601L4.24055 14.6201C3.45055 14.9601 2.81055 14.5401 2.81055 13.6901V12.3701C2.81055 11.6901 3.32055 10.9001 3.95055 10.6401L9.65055 8.19006C9.81055 8.11006 9.95055 7.90006 9.95055 7.72006V4.66006C9.95055 3.70006 10.6605 2.55006 11.5105 2.11006C11.8105 1.96006 12.1705 1.96006 12.4705 2.11006C13.3305 2.55006 14.0405 3.69006 14.0405 4.65006V7.71006C14.0405 7.89006 14.1805 8.10006 14.3405 8.18006L15.3805 8.62006L9.96055 14.0401Z"
                                        fill="currentColor"></path>
                                    <path opacity="0.4"
                                        d="M21.1892 12.3701V13.6901C21.1892 14.5401 20.5492 14.9601 19.7592 14.6201L14.7492 12.4601C14.3492 12.3001 14.0392 12.5001 14.0392 12.9301V15.8401C14.0392 16.0701 14.1692 16.4001 14.3392 16.5601L16.6592 18.8901C16.8992 19.1301 17.0092 19.6101 16.8992 19.9401L16.4492 21.3001C16.2492 21.8901 15.5792 22.1601 15.0292 21.8901L12.6492 19.8901C12.2892 19.5801 11.6992 19.5801 11.3392 19.8901L8.95918 21.8901C8.40918 22.1701 7.73918 21.8901 7.55918 21.3001L7.10918 19.9401C6.99918 19.6001 7.10918 19.1301 7.34918 18.8901L9.66918 16.5601C9.82918 16.4001 9.95918 16.0701 9.95918 15.8401V14.0401L15.3792 8.62012L20.0492 10.6301C20.6792 10.9001 21.1892 11.6901 21.1892 12.3701Z"
                                        fill="currentColor"></path>
                                </svg>
                                <span class="truncate">Track Order</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Backdrop -->
        <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm transition-opacity duration-300 lg:hidden"
            :class="mobileMenuOpen ? 'opacity-100' : 'pointer-events-none opacity-0'" @click="mobileMenuOpen = false"
            aria-hidden="true"></div>

        <!-- Drawer Panel -->
        <div class="fixed inset-y-0 left-0 z-50 flex w-80 max-w-[85vw] flex-col bg-white shadow-xl transition-transform duration-300 ease-in-out dark:bg-navy-750 lg:hidden"
            :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex items-center justify-between border-b border-slate-150 p-4 dark:border-navy-600">
                <span class="font-bold text-slate-800 dark:text-navy-50">Menu</span>
                <button type="button"
                    class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-navy-300 dark:hover:bg-navy-700"
                    @click="mobileMenuOpen = false">
                    <X class="size-5" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto">
                <template v-if="auth?.user">
                    <div
                        class="flex items-center gap-3 border-b border-slate-150 bg-slate-50 p-4 dark:border-navy-600 dark:bg-navy-800/50">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-full bg-primary/10 text-lg font-bold text-primary dark:bg-accent-light/10 dark:text-accent-light">
                            {{ firstName.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-800 dark:text-navy-50">{{ auth.user.name
                            }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-navy-300">{{ auth.user.email }}</p>
                        </div>
                    </div>

                    <nav class="space-y-1 p-4">
                        <p
                            class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-400">
                            My Account
                        </p>

                        <Link href="/customer/account" @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-700">
                            <User class="size-4.5 text-slate-400" /> My Account
                        </Link>

                        <Link href="/customer/orders" @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-700">
                            <Package class="size-4.5 text-info" /> My Orders
                        </Link>

                        <Link href="/customer/addresses" @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-700">
                            <MapPin class="size-4.5 text-warning" /> Addresses
                        </Link>

                        <Link href="/customer/reviews" @click="mobileMenuOpen = false"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-700">
                            <Star class="size-4.5 text-warning" /> My Reviews
                        </Link>

                        <Link v-if="auth.user.role === 'admin'" href="/admin" @click="mobileMenuOpen = false"
                            class="mt-2 flex items-center gap-3 rounded-lg bg-slate-100 px-3 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-200 dark:bg-navy-800 dark:text-navy-100 dark:hover:bg-navy-700">
                            <Settings class="size-4.5 text-slate-500" /> Admin Panel
                        </Link>
                    </nav>
                </template>

                <template v-else>
                    <nav class="space-y-4 p-4">
                        <div class="space-y-1.5 border-b border-slate-150 pb-4 dark:border-navy-600">
                            <Link href="/" @click="mobileMenuOpen = false"
                                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-700">
                                <Home class="size-4.5 text-primary dark:text-accent-light" /> Home
                            </Link>
                            <Link href="/customer/products" @click="mobileMenuOpen = false"
                                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-navy-700">
                                <ShoppingBag class="size-4.5 text-info" /> Shop All
                            </Link>
                        </div>

                        <div class="space-y-3 pt-2">
                            <Link href="/login" @click="mobileMenuOpen = false"
                                class="btn w-full bg-slate-100 font-medium text-slate-700 hover:bg-slate-200 dark:bg-navy-700 dark:text-navy-100">
                                Log In
                            </Link>
                            <Link href="/register" @click="mobileMenuOpen = false"
                                class="btn w-full bg-primary font-medium text-white hover:bg-primary-focus dark:bg-accent">
                                Sign Up
                            </Link>
                        </div>
                    </nav>
                </template>
            </div>

            <div v-if="auth?.user" class="border-t border-slate-150 p-4 dark:border-navy-600 mt-auto">
                <button type="button" @click="logout"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-error/10 px-4 py-3 text-sm font-semibold text-error transition-colors hover:bg-error/20">
                    <LogOut class="size-4.5" /> Sign Out
                </button>
            </div>
        </div>

        <main class="mx-auto w-full max-w-[1400px] flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <slot />
        </main>

        <footer
            class="mt-auto border-t border-slate-150 bg-white py-10 text-xs text-slate-400 dark:border-navy-600 dark:bg-navy-750 dark:text-navy-300">
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 lg:px-8">
                <div class="mb-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="space-y-3">
                        <div class="flex items-center gap-2.5">
                            <span
                                class="flex size-7 items-center justify-center rounded-lg bg-primary text-xs font-bold text-white dark:bg-accent">M</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-navy-50">My Store</span>
                        </div>
                        <p class="leading-relaxed">Quality products delivered across Kenya. Shop with confidence and
                            secure
                            M-Pesa payments.</p>
                    </div>

                    <div>
                        <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-navy-200">Shop</h3>
                        <ul class="space-y-2">
                            <li>
                                <Link href="/customer/products" class="hover:underline">All products</Link>
                            </li>
                            <li>
                                <Link href="/customer/products?sort=newest" class="hover:underline">New arrivals</Link>
                            </li>
                            <li>
                                <Link href="/customer/products?sort=featured" class="hover:underline">Best sellers
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-navy-200">Help</h3>
                        <ul class="space-y-2">
                            <li>
                                <Link href="/contact" class="hover:underline">Contact us</Link>
                            </li>
                            <li>
                                <Link href="/customer/orders" class="hover:underline">Track order</Link>
                            </li>
                            <li>
                                <Link href="/policies/shipping" class="hover:underline">Shipping policy</Link>
                            </li>
                            <li>
                                <Link href="/policies/returns" class="hover:underline">Returns & refunds</Link>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-navy-200">Account</h3>
                        <ul class="space-y-2">
                            <template v-if="auth?.user">
                                <li>
                                    <Link href="/customer/account" class="hover:underline">My account</Link>
                                </li>
                                <li>
                                    <Link href="/customer/orders" class="hover:underline">Order history</Link>
                                </li>
                            </template>
                            <template v-else>
                                <li>
                                    <Link href="/login" class="hover:underline">Login</Link>
                                </li>
                                <li>
                                    <Link href="/register" class="hover:underline">Create account</Link>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div
                    class="flex flex-col items-center justify-between gap-4 border-t border-slate-150 pt-6 dark:border-navy-600 sm:flex-row">
                    <p>&copy; {{ new Date().getFullYear() }} My Store. All rights reserved.</p>
                    <div class="flex items-center gap-6">
                        <Link href="/policies/privacy" class="hover:underline">Privacy</Link>
                        <Link href="/policies/terms" class="hover:underline">Terms</Link>
                        <span class="font-medium text-slate-600 dark:text-navy-200">KES · Kenya</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>