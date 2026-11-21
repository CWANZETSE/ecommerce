<script setup lang="ts">
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import { onMounted, onBeforeUnmount } from 'vue';
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
    User,
    Sun,
    Moon,
    SparkleIcon,
} from "@lucide/vue";
import {
    admin_dashboard,
    customer_orders_index,
    customer_products_index,
    home,
} from "@/routes";
import HomeSVG from "@/components/SVG/homeSVG.vue";
import CartSVG from "@/components/SVG/cartSVG.vue";
import NoteSVG from "@/components/SVG/NoteSVG.vue";
import PackageSVG from "@/components/SVG/packageSVG.vue";
import BoxSVG from "@/components/SVG/boxSVG.vue";
import ShippingSVG from "@/components/SVG/shippingSVG.vue";
import MicrophoneSVG from "@/components/SVG/microphoneSVG.vue";
import GlobalLoadingOverlay from '@/components/GlobalLoadingOverlay.vue';
interface CategoryNode {
    id: string;
    name: string;
    slug: string;
    children?: { id: string; name: string; slug: string }[];
}

const page = usePage<{
    auth: {
        user: { id: string; name: string; email: string; role: string } | null;
    };
    flash: {
        success?: string;
        error?: string;
        warning?: string;
        info?: string;
    };
    cart_count: number;
    categories?: CategoryNode[];
}>();
// Global loading state.
const isLoading = ref(false);

const auth = computed(() => page.props.auth);
const categories = computed(() => page.props.categories ?? []);
const cartCount = computed(() => page.props.cart_count ?? 0);
const currentPath = computed(() => page.url.split("?")[0]);
import { useToast } from '@/composables/useToast';
import { Toaster } from '@/components/ui/sonner';
// Initialize global toast watcher.
useToast();

const firstName = computed(() => {
    const name = auth.value?.user?.name ?? "";
    return name.split(" ")[0] || "Account";
});

/* ---------- Category href helper ---------- */
function categoryHref(slug: string): string {
    const params = new URLSearchParams();
    params.append("categories[]", slug);
    return `/customer/products?${params.toString()}`;
}

/* Mobile menu */
const mobileMenuOpen = ref(false);

watch(mobileMenuOpen, (isOpen) => {
    if (typeof document !== "undefined") {
        document.body.style.overflow = isOpen ? "hidden" : "";
    }
});

/* Search */
const search = ref("");
function submitSearch() {
    const trimmed = search.value.trim();
    router.get("/customer/products", trimmed ? { search: trimmed } : {}, {
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
    router.post("/logout");
}

/* Category dropdown */
const categoriesOpen = ref(false);

/* -------- Theme -------- */
const isDark = ref(false);

function toggleTheme() {
    const next = !document.documentElement.classList.contains("dark");
    document.documentElement.classList.toggle("dark", next);
    localStorage.setItem("theme", next ? "dark" : "light");
    isDark.value = next;
}

// Inertia event listener cleanup callbacks.
let removeStartListener: (() => void) | undefined;
let removeFinishListener: (() => void) | undefined;

onMounted(() => {
    // Show loading overlay when an Inertia request starts.
    removeStartListener = router.on('start', () => {
        isLoading.value = true;
    });

    // Hide loading overlay when an Inertia request finishes.
    removeFinishListener = router.on('finish', () => {
        isLoading.value = false;
    });
});

onBeforeUnmount(() => {
    removeStartListener?.();
    removeFinishListener?.();

    isLoading.value = false;
});
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-slate-50 text-slate-700 dark:bg-navy-900 dark:text-navy-100"
    >
        <!-- Top utility bar -->
        <div
            class="border-b border-slate-150 bg-slate-100/60 text-xs text-slate-400 dark:border-navy-600 dark:bg-navy-750 dark:text-navy-300"
        >
            <div
                class="mx-auto flex h-9 w-full max-w-[1400px] items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <p></p>
                <div class="flex items-center gap-4">
                    <Link
                        href="/contact"
                        class="transition-colors hover:text-primary dark:hover:text-accent-light"
                    >
                        Help? Call 0720705815
                    </Link>
                </div>
            </div>
        </div>

        <!-- Sticky Header -->
        <header
            class="sticky top-0 z-40 border-b border-slate-150 bg-white/90 backdrop-blur-md dark:border-navy-600 dark:bg-navy-750 dark:text-navy-300"
        >
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center gap-3">
                    <!-- Mobile Menu Trigger -->
                    <button
                        type="button"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-100 text-slate-700 hover:bg-slate-200 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100 lg:hidden"
                        aria-label="Open menu"
                        @click="mobileMenuOpen = true"
                    >
                        <Menu class="size-5" />
                    </button>

                    <!-- Brand -->
                    <Link href="/" class="flex shrink-0 items-center gap-2.5">
                        <img
                            v-if="page.props.store_logo_url"
                            :src="page.props.store_logo_url"
                            :alt="page.props.store_name"
                            class="h-8 w-auto"
                        />
                        <span
                            v-else
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary font-bold text-white shadow-md dark:bg-accent"
                        >
                            <ShoppingBag class="size-4.5" />
                        </span>
                        <span
                            class="hidden text-lg font-bold text-slate-800 dark:text-navy-50 sm:inline"
                        >
                            {{ page.props.store_name }}
                        </span>
                    </Link>

                    <!-- Desktop Search -->
                    <form
                        class="mx-auto hidden w-full max-w-xl lg:block"
                        @submit.prevent="submitSearch"
                    >
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />
                            <input
                                v-model="search"
                                type="search"
                                placeholder="Search products, categories…"
                                class="form-input w-full rounded-lg bg-slate-150 px-3.5 py-2.5 pl-10 text-sm ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:text-navy-100"
                            />
                        </div>
                    </form>

                    <!-- Right actions -->
                    <div class="ml-auto flex items-center gap-3">
                        <button
                            type="button"
                            class="btn flex size-10 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-100 p-0 text-slate-700 transition-colors hover:bg-slate-200 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100 dark:hover:bg-accent-light/20"
                            :aria-label="
                                isDark
                                    ? 'Switch to light mode'
                                    : 'Switch to dark mode'
                            "
                            @click="toggleTheme"
                        >
                            <Sun v-if="!isDark" class="size-5 text-amber-400" />
                            <Moon v-else class="size-5 text-amber-400" />
                        </button>
                        <Link
                            href="/cart"
                            class="relative flex size-10 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light dark:border-navy-500"
                            title="Shopping Cart"
                        >
                            <span class="text-2xl">
                                <CartSVG />
                            </span>
                            <span
                                v-if="cartCount > 0"
                                class="absolute -right-1 -top-1 flex size-5 items-center justify-center rounded-full bg-error text-[10px] font-bold text-white shadow"
                            >
                                {{ cartCount }}
                            </span>
                        </Link>

                        <template v-if="auth?.user">
                            <div class="relative hidden lg:block">
                                <button
                                    type="button"
                                    class="group relative flex size-10 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-100 text-slate-700 transition-colors hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100 dark:focus:ring-accent/30"
                                    :aria-label="`Account menu — ${firstName}`"
                                    :aria-expanded="accountOpen"
                                    @click="toggleAccount"
                                >
                                    <span
                                        class="flex size-7 shrink-0 items-center justify-center font-bold text-primary"
                                    >
                                        {{ firstName.charAt(0).toUpperCase() }}
                                    </span>

                                    <!-- Online indicator -->
                                    <span
                                        class="absolute -right-0.5 -top-0.5 flex size-2.5"
                                    >
                                        <span
                                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-success opacity-75"
                                        ></span>
                                        <span
                                            class="relative inline-flex size-2.5 rounded-full border-2 border-white bg-success dark:border-navy-700"
                                        ></span>
                                    </span>
                                </button>

                                <div
                                    v-if="accountOpen"
                                    class="absolute right-0 mt-2 w-56 rounded-xl border border-slate-150 bg-white py-1.5 shadow-xl dark:border-navy-600 dark:bg-navy-750"
                                    @mouseleave="accountOpen = false"
                                >
                                    <Link
                                        href="/customer/account"
                                        v-if="auth?.user?.role != 'admin'"
                                        class="flex items-center gap-2.5 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-accent-light/20"
                                    >
                                        <div
                                            class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-secondary/10 dark:bg-secondary-light/15"
                                        >
                                            <NoteSVG />
                                        </div>
                                        My account
                                    </Link>

                                    <Link
                                        href="/customer/orders"
                                        v-if="auth?.user?.role != 'admin'"
                                        class="flex items-center gap-2.5 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-accent-light/20"
                                    >
                                        <div
                                            class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-info/10 dark:bg-info/15"
                                        >
                                            <BoxSVG />
                                        </div>
                                        Orders
                                    </Link>

                                    <Link
                                        href="/customer/addresses"
                                        v-if="auth?.user?.role != 'admin'"
                                        class="flex items-center gap-2.5 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-accent-light/20"
                                    >
                                        <div
                                            class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-warning/10 dark:bg-warning/15"
                                        >
                                            <ShippingSVG />
                                        </div>
                                        Addresses
                                    </Link>

                                    <Link
                                        href="/customer/reviews"
                                        v-if="auth?.user?.role != 'admin'"
                                        class="flex items-center gap-2.5 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-accent-light/20"
                                    >
                                        <div
                                            class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-warning/10 dark:bg-warning/15"
                                        >
                                            <MicrophoneSVG />
                                        </div>
                                        Reviews
                                    </Link>

                                    <Link
                                        v-if="auth?.user?.role === 'admin'"
                                        :href="admin_dashboard.url()"
                                        class="flex items-center gap-2.5 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-accent-light/20"
                                    >
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 dark:bg-navy-700"
                                        >
                                            <Settings
                                                class="size-4.5 text-slate-400"
                                            />
                                        </div>
                                        Admin panel
                                    </Link>

                                    <div
                                        class="my-1 border-t border-slate-150 dark:border-navy-600"
                                    ></div>

                                    <button
                                        type="button"
                                        class="flex w-full items-center gap-2.5 px-4 py-1.5 text-left text-sm font-medium text-error transition-colors hover:bg-error/10 cursor-pointer"
                                        @click="logout"
                                    >
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-error/10 dark:bg-error/15"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="size-4.5 text-rose-400 transition-colors hover:text-error"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"
                                                />
                                            </svg>
                                        </div>
                                        Sign out
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <Link
                                href="/login"
                                class="btn btn-sm hidden bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-navy-700 dark:text-navy-100 sm:flex"
                            >
                                Login</Link
                            >
                            <Link
                                href="/register"
                                class="btn btn-sm hidden bg-primary text-white hover:bg-primary-focus dark:bg-accent sm:flex"
                            >
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
                                class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />
                            <input
                                v-model="search"
                                type="search"
                                placeholder="Search products…"
                                class="form-input w-full rounded-lg bg-slate-150 px-3.5 py-2 pl-10 text-sm ring-primary/50 placeholder:text-slate-400 dark:bg-navy-900 dark:text-navy-100"
                            />
                        </div>
                    </form>
                </div>
            </div>

            <!-- Secondary Navbar (Categories) -->
            <nav
                class="border-t border-slate-150 bg-white/50 dark:border-navy-600 dark:bg-navy-900 dark:hover:bg-accent-light/20 dark:text-navy-100 md:block"
            >
                <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 lg:px-8">
                    <div
                        class="scrollbar-none flex h-11 items-center gap-2 overflow-x-auto whitespace-nowrap text-sm font-medium"
                    >
                        <Link
                            :href="home.url()"
                            class="flex items-center justify-center size-8 rounded-lg bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light"
                        >
                            <HomeSVG />
                        </Link>

                        <Link
                            :href="customer_products_index.url()"
                            class="shrink-0 rounded-lg px-3 py-1.5 transition-colors"
                            :class="
                                currentPath === '/customer/products'
                                    ? 'bg-primary/10 font-semibold text-primary dark:bg-accent-light/10 dark:text-accent-light'
                                    : 'text-slate-600 hover:bg-slate-150 dark:text-navy-200 dark:hover:bg-accent-light/20'
                            "
                        >
                            All Products
                        </Link>

                        <template v-if="categories.length">
                            <template
                                v-for="cat in categories.slice(0, 6)"
                                :key="cat.id"
                            >
                                <Link
                                    :href="categoryHref(cat.slug)"
                                    class="shrink-0 rounded-lg px-3 py-1.5 text-slate-600 transition-colors hover:bg-slate-150 hover:text-slate-900 dark:text-navy-200 dark:hover:bg-accent-light/20 dark:hover:text-navy-50"
                                >
                                    {{ cat.name }}
                                </Link>
                            </template>

                            <!-- More Categories Dropdown -->
                            <div
                                v-if="categories.length > 6"
                                class="relative shrink-0"
                            >
                                <button
                                    type="button"
                                    class="flex items-center gap-1 rounded-lg px-3 py-1.5 text-slate-600 transition-colors hover:bg-slate-150 hover:text-slate-900 dark:text-navy-200 dark:hover:bg-navy-750"
                                    @click="categoriesOpen = !categoriesOpen"
                                >
                                    More
                                    <ChevronDown class="size-3" />
                                </button>
                                <div
                                    v-if="categoriesOpen"
                                    class="absolute left-0 top-full z-50 mt-1 w-56 rounded-xl border border-slate-150 bg-white py-2 shadow-xl dark:border-navy-600 dark:bg-navy-750"
                                    @mouseleave="categoriesOpen = false"
                                >
                                    <Link
                                        v-for="cat in categories.slice(6)"
                                        :key="cat.id"
                                        :href="categoryHref(cat.slug)"
                                        class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-accent-light/20"
                                    >
                                        {{ cat.name }}
                                    </Link>
                                </div>
                            </div>
                        </template>

                        <div
                            class="ml-auto hidden shrink-0 items-center gap-4 text-xs lg:flex"
                        >
                            <span
                                v-if="auth?.user"
                                class="flex text-center text-slate-400 transition-colors hover:text-slate-700 dark:text-navy-300 dark:hover:text-navy-100"
                            >
                                <span class="truncate">
                                    <span>Hello, {{ auth.user.name }}</span>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Backdrop -->
        <div
            class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm transition-opacity duration-300 lg:hidden"
            :class="
                mobileMenuOpen ? 'opacity-100' : 'pointer-events-none opacity-0'
            "
            @click="mobileMenuOpen = false"
            aria-hidden="true"
        ></div>

        <!-- Drawer Panel -->
        <div
            class="fixed inset-y-0 left-0 z-50 flex w-80 max-w-[85vw] flex-col bg-white shadow-xl transition-transform duration-300 ease-in-out dark:border-navy-600 dark:bg-navy-750 dark:text-navy-300 lg:hidden"
            :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div
                class="flex items-center justify-between border-b border-slate-150 p-4 dark:border-navy-600"
            >
                <span class="font-bold text-slate-800 dark:text-navy-50"
                    >Menu</span
                >
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:border-navy-600 dark:bg-navy-750 dark:text-navy-300"
                    @click="mobileMenuOpen = false"
                >
                    <X class="size-5" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto">
                <template v-if="auth?.user">
                    <div
                        class="flex items-center gap-3 border-b border-slate-150 bg-slate-50 p-4 dark:border-navy-600 dark:bg-navy-750 dark:text-navy-300"
                    >
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-full bg-primary/10 text-lg font-bold text-primary dark:bg-accent-light/10 dark:text-accent-light"
                        >
                            {{ firstName.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-sm font-semibold text-slate-800 dark:text-navy-50"
                            >
                                {{ auth.user.name }}
                            </p>
                            <p
                                class="truncate text-xs text-slate-500 dark:text-navy-300"
                            >
                                {{ auth.user.email }}
                            </p>
                        </div>
                    </div>

                    <nav class="space-y-1 p-4">
                        <p
                            class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-400"
                        >
                            Account
                        </p>

                        <Link
                            href="/customer/account"
                            v-if="auth?.user?.role != 'admin'"
                            class="flex items-center gap-2.5 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-accent-light/20"
                        >
                            <div
                                class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-secondary/10 dark:bg-secondary-light/15"
                            >
                                <NoteSVG />
                            </div>
                            My account
                        </Link>

                        <Link
                            href="/customer/orders"
                            v-if="auth?.user?.role != 'admin'"
                            class="flex items-center gap-2.5 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-accent-light/20"
                        >
                            <div
                                class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-info/10 dark:bg-info/15"
                            >
                                <BoxSVG />
                            </div>
                            Orders
                        </Link>

                        <Link
                            href="/customer/addresses"
                            v-if="auth?.user?.role != 'admin'"
                            class="flex items-center gap-2.5 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-accent-light/20"
                        >
                            <div
                                class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-warning/10 dark:bg-warning/15"
                            >
                                <ShippingSVG />
                            </div>
                            Addresses
                        </Link>

                        <Link
                            href="/customer/reviews"
                            v-if="auth?.user?.role != 'admin'"
                            class="flex items-center gap-2.5 px-4 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-100 dark:text-navy-100 dark:hover:bg-accent-light/20"
                        >
                            <div
                                class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-warning/10 dark:bg-warning/15"
                            >
                                <MicrophoneSVG />
                            </div>
                            Reviews
                        </Link>

                        <Link
                            v-if="auth.user.role === 'admin'"
                            :href="admin_dashboard.url()"
                            @click="mobileMenuOpen = false"
                            class="mt-2 flex items-center gap-3 rounded-lg bg-slate-100 px-3 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-200 dark:border-navy-600 dark:bg-navy-750 dark:text-navy-300"
                        >
                            <Settings class="size-4.5 text-slate-500" /> Admin
                            Panel
                        </Link>
                    </nav>
                </template>

                <template v-else>
                    <nav class="space-y-4 p-4">
                        <div
                            class="space-y-1.5 border-b border-slate-150 pb-4 dark:border-navy-600"
                        >
                            <Link
                                href="/"
                                class="flex shrink-0 items-center gap-2.5"
                            >
                                <img
                                    v-if="page.props.store_logo_url"
                                    :src="page.props.store_logo_url"
                                    alt="Store logo"
                                    class="h-8 w-auto"
                                />
                                <span
                                    v-else
                                    class="flex size-9 items-center justify-center rounded-xl bg-primary text-white"
                                >
                                    <ShoppingBag class="size-4.5" />
                                </span>
                                <span
                                    class="hidden text-lg font-bold text-slate-800 dark:text-navy-50 sm:inline"
                                >
                                    {{ page.props.store_name ?? "My Store" }}
                                </span>
                            </Link>
                        </div>

                        <div class="space-y-3 pt-2">
                            <Link
                                href="/login"
                                @click="mobileMenuOpen = false"
                                class="btn w-full bg-slate-100 font-medium text-slate-700 hover:bg-slate-200 dark:bg-navy-700 dark:text-navy-100"
                            >
                                Log In
                            </Link>
                            <Link
                                href="/register"
                                @click="mobileMenuOpen = false"
                                class="btn w-full bg-primary font-medium text-white hover:bg-primary-focus dark:bg-accent"
                            >
                                Sign Up
                            </Link>
                        </div>
                    </nav>
                </template>
            </div>

            <div
                v-if="auth?.user"
                class="border-t border-slate-150 p-4 dark:border-navy-600 mt-auto"
            >
                <button
                    type="button"
                    @click="logout"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-error/10 px-4 py-3 text-sm font-semibold text-error transition-colors hover:bg-error/20"
                >
                    <LogOut class="size-4.5" /> Sign Out
                </button>
            </div>
        </div>

        <main
            class="mx-auto w-full max-w-[1400px] flex-1 px-4 py-6 sm:px-6 lg:px-8"
        >
            <slot />
        </main>
<!-- Global notifications -->
        <Toaster
            position="top-center"
            :close-button="true"
        />
        <!-- Global loading overlay -->
        <GlobalLoadingOverlay :show="isLoading" />
        <footer
            class="mt-auto border-t border-slate-150 bg-white py-10 text-xs text-slate-400 dark:border-navy-600 dark:bg-navy-750 dark:text-navy-300"
        >
            <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 lg:px-8">
                

                <div
                    class="flex flex-col items-center justify-between gap-4  dark:border-navy-600 sm:flex-row"
                >
                    <p>
                        &copy; {{ new Date().getFullYear() }} <span v-if="page.props.store_name">{{page.props.store_name}}</span>. All
                        rights reserved.
                    </p>
                    <div class="flex items-center gap-6">
                        <Link href="/policies/privacy" class="hover:underline"
                            >Privacy Policy</Link
                        >
                        <Link href="/policies/terms" class="hover:underline"
                            >Terms & Conditions</Link
                        >
                        <Link href="/policies/shipping" class="hover:underline"
                            >Shipping Policy</Link
                        >
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
