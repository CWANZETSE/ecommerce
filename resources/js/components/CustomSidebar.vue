<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    User as UserIcon,
    ShoppingBag,
    ArrowRight,
} from '@lucide/vue';
import MenuSVG from './SVG/menuSVG.vue';
import PackageSVG from './SVG/packageSVG.vue';
import StripsSVG from './SVG/stripsSVG.vue';
import CirclesSVG from './SVG/circlesSVG.vue';
import AttributeSVG from './SVG/attributeSVG.vue';
import BoxSVG from './SVG/boxSVG.vue';
import ShoppingSVG from './SVG/shoppingSVG.vue';
import ShippingSVG from './SVG/shippingSVG.vue';
import CouponSVG from './SVG/couponSVG.vue';
import ReportsSVG from './SVG/reportsSVG.vue';
import BannerSVG from './SVG/bannerSVG.vue';
import SettingSVG from './SVG/settingSVG.vue';
import UserSVG from './SVG/userSVG.vue';

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
    const storeName = computed(() => (page.props as any).store_name ?? 'My Store');
const storeLogoUrl = computed(() => (page.props as any).store_logo_url ?? null);

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
        <!-- Logo -->
<div class="flex h-[61px] shrink-0 items-center border-b border-slate-150 dark:border-navy-700">
    <Link
        v-if="storeLogoUrl"
        href="/admin/dashboard"
        class="flex h-full w-full items-center px-4"
        :aria-label="storeName"
    >
        <img
            :src="storeLogoUrl"
            :alt="storeName"
            class="h-30 w-auto object-contain object-left"
        />
    </Link>

    <Link
        v-else
        href="/admin/dashboard"
        class="flex h-full w-full items-center gap-3 px-4"
    >
        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary text-white dark:bg-accent">
            <span class="text-base font-bold">A</span>
        </span>
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold text-slate-800 dark:text-navy-50">
                Admin Panel
            </p>
            <p class="truncate text-tiny text-slate-500 dark:text-navy-300">
                {{ storeName }}
            </p>
        </div>
    </Link>
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
                            class="relative flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
                        <MenuSVG/>
                        </span>
                        <span class="truncate">Dashboard</span>
                    </Link>
                </li>

                <!-- Products -->
                <li>
                    <Link href="/admin/products"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/products') ? 'bg-primary/20 font-medium text-primary dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
                        <PackageSVG/>
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
                            class="relative flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
                        <StripsSVG/>
                        </span>
                        <span class="truncate">Categories</span>
                    </Link>
                </li>

                <!-- Brands -->
                <li>
                    <Link href="/admin/brands"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/brands') ? 'bg-primary/20 font-medium text-primary dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
                        <CirclesSVG/>
                        </span>
                        <span class="truncate">Brands</span>
                    </Link>
                </li>

                <!-- Attributes -->
                <li>
                    <Link href="/admin/attributes"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/attributes') ? 'bg-primary/20 font-medium text-primary dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
                        <AttributeSVG/>
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
                            class="relative flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
                        <BoxSVG/>
                        </span>
                        <span class="truncate">Inventory</span>
                    </Link>
                </li>

                <!-- Orders -->
                <li>
                    <Link href="/admin/orders"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/orders') ? 'bg-primary/20 font-medium text-primary dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
                        <ShoppingSVG/>
                        </span>
                        <span class="truncate">Orders</span>
                    </Link>
                </li>

                <!-- Shipping -->
                <li>
                    <Link href="/admin/shipping"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/shipping') ? 'bg-primary/20 font-medium text-primary dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
                        <ShippingSVG/>
                        </span>
                        <span class="truncate">Shipping</span>
                    </Link>
                </li>

                <!-- Coupons -->
                <li>
                    <Link href="/admin/coupons"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/coupons') ? 'bg-primary/20 font-medium text-primary dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
                        <CouponSVG/>
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
                            class="relative flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
                        <ReportsSVG/>
                        </span>
                        <span class="truncate">Reports</span>
                    </Link>
                </li>

                <!-- Banners -->
                <li>
                    <Link href="/admin/banners"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-xs-plus tracking-wide outline-hidden transition-colors duration-200 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                        :class="isActive('/admin/banners') ? 'bg-primary/20 font-medium text-primary dark:bg-navy-300/20 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-200 dark:hover:text-navy-50'"
                        @click="emit('close')">
                        <span
                            class="relative flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
                        <BannerSVG/>
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
                            class="relative flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
                            <SettingSVG/>
                        </span>

                        <span class="truncate">Settings</span>
                    </Link>
                </li>
            </ul>
        </nav>

        <!-- Footer -->
        <footer class="shrink-0 border-t border-slate-150 p-3 dark:border-navy-700">
            
            <Link href="/"
                class="group flex items-center gap-3 rounded-lg px-3 py-2 mt-2 text-xs-plus font-medium text-slate-600 transition-colors duration-200 hover:bg-primary/20 hover:text-primary focus:bg-primary/20 focus:text-primary active:bg-primary/25 dark:text-navy-200 dark:hover:bg-navy-300/20 dark:hover:text-accent-light dark:focus:bg-navy-300/20 dark:focus:text-accent-light dark:active:bg-navy-300/25">
                <span
                            class="relative flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 dark:bg-primary">
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