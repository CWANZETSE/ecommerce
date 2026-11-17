<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import type { PageProps } from '@/types';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

interface Banner {
    id: string;
    title: string;
    subtitle: string | null;
    image_url: string;
    link_url: string | null;
    link_text: string | null;
}

interface Product {
    id: string;
    name: string;
    slug: string;
    price: string;
    primary_image_url: string | null;
}

interface Category {
    id: string;
    name: string;
    slug: string;
    image_url: string | null;
    children: { id: string; name: string; slug: string; image_url: string | null }[];
}

const props = defineProps<PageProps & {
    banners: Banner[];
    featured: Product[];
    newArrivals: Product[];
    categories: Category[];
}>();

function productCard(p: Product) {
    return {
        image: p.primary_image_url,
        name: p.name,
        price: p.price,
        href: `/customer/products/${p.slug}`,
    };
}

/* ---------- Banner carousel ---------- */
const currentSlide = ref(0);
const slideCount = computed(() => props.banners.length);
let timer: ReturnType<typeof setInterval> | null = null;

function nextSlide() {
    if (slideCount.value === 0) return;
    currentSlide.value = (currentSlide.value + 1) % slideCount.value;
}

function prevSlide() {
    if (slideCount.value === 0) return;
    currentSlide.value = (currentSlide.value - 1 + slideCount.value) % slideCount.value;
}

function goToSlide(i: number) {
    currentSlide.value = i;
    restartTimer();
}

function restartTimer() {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
    if (slideCount.value > 1) {
        timer = setInterval(nextSlide, 5000);
    }
}

onMounted(() => {
    restartTimer();
});

onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
});

const formatMoney = (amount: number | string) =>
    new Intl.NumberFormat('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(amount) || 0);
</script>

<template>
    <Head title="Home" />

    <StorefrontLayout>
        <!-- Hero Banners Section -->
        <section v-if="banners.length" class="relative overflow-hidden">
            <div
                class="flex transition-transform duration-700 ease-in-out"
                :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
            >
                <div
                    v-for="b in banners"
                    :key="b.id"
                    class="group relative min-w-full overflow-hidden rounded-2xl border border-slate-150 bg-card shadow-soft dark:border-navy-600 dark:bg-navy-800"
                >
                    <img
                        :src="b.image_url"
                        :alt="b.title"
                        class="h-72 w-full object-cover object-center transition-transform duration-500 ease-in-out group-hover:scale-105 md:h-[26rem]"
                    />
                    <div class="absolute inset-0 flex items-end bg-gradient-to-t from-black/80 via-black/30 to-transparent p-6 md:p-8">
                        <div class="max-w-lg text-white">
                            <span class="mb-3 inline-block rounded-full bg-primary/20 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-accent-light backdrop-blur-md">
                                Special Offer
                            </span>
                            <h2 class="text-2xl font-bold tracking-tight text-white md:text-4xl">{{ b.title }}</h2>
                            <p v-if="b.subtitle" class="mt-2 line-clamp-2 text-sm text-slate-200">{{ b.subtitle }}</p>
                            <Link
                                v-if="b.link_url"
                                :href="b.link_url"
                                class="mt-5 inline-flex items-center space-x-2 rounded-full bg-primary px-6 py-2.5 text-sm font-medium text-white shadow-lg transition-all hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus"
                            >
                                <span>{{ b.link_text ?? 'Shop Now' }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dots -->
            <div v-if="slideCount > 1" class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2">
                <button
                    v-for="(b, i) in banners"
                    :key="b.id"
                    type="button"
                    class="size-2.5 rounded-full transition-colors"
                    :class="currentSlide === i ? 'bg-white' : 'bg-white/50 hover:bg-white/75'"
                    :aria-label="`Go to slide ${i + 1}`"
                    @click="goToSlide(i)"
                />
            </div>
        </section>

        <!-- Categories Strip -->
        <section v-if="categories?.length" class="mt-10">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-800 dark:text-navy-50">Shop by Category</h2>
                <Link href="/customer/products" class="text-xs-plus font-medium text-primary hover:text-primary-focus dark:text-accent-light">
                    View All Categories
                </Link>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <Link
                    v-for="c in categories.slice(0, 4)"
                    :key="c.id"
                    :href="`/customer/products?category=${c.slug}`"
                    class="group relative flex items-center justify-between gap-3 rounded-lg border border-slate-150 bg-white p-3 shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-navy-600 dark:bg-navy-750"
                >
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-medium text-slate-700 group-hover:text-primary dark:text-navy-100 dark:group-hover:text-accent-light">
                            {{ c.name }}
                        </h3>
                        <p class="mt-1 line-clamp-2 text-[11px] text-slate-400 dark:text-navy-300">
                            {{ c.children.map((ch) => ch.name).join(', ') || 'Explore collection' }}
                        </p>
                        <div class="mt-2 flex items-center text-[11px] font-semibold text-primary dark:text-accent-light">
                            <span>Browse Store</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 size-3.5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>

                    <div class="relative size-16 shrink-0 overflow-hidden rounded-md bg-slate-150 dark:bg-navy-900">
                        <img
                            v-if="c.image_url ?? c.children[0]?.image_url"
                            :src="(c.image_url ?? c.children[0]?.image_url)!"
                            :alt="c.name"
                            class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center text-primary dark:text-accent-light">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </div>
                    </div>
                </Link>
            </div>
        </section>

        <!-- Featured Products -->
        <section v-if="featured.length" class="mt-12">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-800 dark:text-navy-50">Featured Products</h2>
                <Link
                    href="/customer/products"
                    class="group badge space-x-2.5 rounded-full bg-warning/10 text-warning transition-colors hover:bg-warning/20 dark:bg-warning/15 dark:hover:bg-warning/25"
                >
                    <span>View All Products</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 transition-transform duration-200 group-hover:translate-x-1 group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </Link>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-5 lg:grid-cols-4">
                <div
                    v-for="p in featured"
                    :key="p.id"
                    class="card group flex flex-col justify-between p-3 transition-all duration-300 hover:shadow-lg dark:bg-navy-750"
                >
                    <div>
                        <div class="relative aspect-square overflow-hidden rounded-lg bg-slate-150 dark:bg-navy-900">
                            <img
                                v-if="p.primary_image_url"
                                :src="p.primary_image_url"
                                :alt="p.name"
                                class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                            />
                            <div class="absolute right-2 top-2">
                                <span class="badge rounded-full bg-primary/90 px-2 py-0.5 text-xs text-white backdrop-blur-sm">
                                    Featured
                                </span>
                            </div>
                        </div>
                        <div class="mt-4 px-1">
                            <Link
                                :href="productCard(p).href"
                                class="line-clamp-2 text-sm font-medium text-slate-700 hover:text-primary focus:text-primary dark:text-navy-100 dark:hover:text-accent-light"
                            >
                                {{ p.name }}
                            </Link>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-150 px-1 pt-3 dark:border-navy-600">
                        <div>
                            <span class="block text-xs text-slate-400 dark:text-navy-300">Price</span>
                            <span class="text-base font-bold text-slate-800 dark:text-navy-50">KES {{ p.price }}</span>
                        </div>
                        <Link
                            :href="productCard(p).href"
                            class="btn size-9 rounded-full bg-primary/10 p-0 text-primary hover:bg-primary hover:text-white focus:bg-primary focus:text-white dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent dark:hover:text-white"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- New Arrivals -->
        <section v-if="newArrivals.length" class="mt-12">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-800 dark:text-navy-50">New Arrivals</h2>
                <Link
                    href="/customer/products"
                    class="group badge space-x-2.5 rounded-full bg-warning/10 text-warning transition-colors hover:bg-warning/20 dark:bg-warning/15 dark:hover:bg-warning/25"
                >
                    <span>View All Arrivals</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 transition-transform duration-200 group-hover:translate-x-1 group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </Link>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-5 lg:grid-cols-4">
                <div
                    v-for="p in newArrivals"
                    :key="p.id"
                    class="card group flex flex-col justify-between p-3 transition-all duration-300 hover:shadow-lg dark:bg-navy-750"
                >
                    <div>
                        <div class="relative aspect-square overflow-hidden rounded-lg bg-slate-150 dark:bg-navy-900">
                            <img
                                v-if="p.primary_image_url"
                                :src="p.primary_image_url"
                                :alt="p.name"
                                class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                            />
                            <div class="absolute right-2 top-2">
                                <span class="badge rounded-full bg-success/90 px-2 py-0.5 text-xs text-white backdrop-blur-sm">
                                    New
                                </span>
                            </div>
                        </div>
                        <div class="mt-4 px-1">
                            <Link
                                :href="productCard(p).href"
                                class="line-clamp-2 text-sm font-medium text-slate-700 hover:text-primary focus:text-primary dark:text-navy-100 dark:hover:text-accent-light"
                            >
                                {{ p.name }}
                            </Link>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-150 px-1 pt-3 dark:border-navy-600">
                        <div>
                            <span class="block text-xs text-slate-400 dark:text-navy-300">Price</span>
                            <span class="text-base font-bold text-slate-800 dark:text-navy-50">KES {{ p.price }}</span>
                        </div>
                        <Link
                            :href="productCard(p).href"
                            class="btn size-9 rounded-full bg-primary/10 p-0 text-primary hover:bg-primary hover:text-white focus:bg-primary focus:text-white dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent dark:hover:text-white"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </StorefrontLayout>
</template>