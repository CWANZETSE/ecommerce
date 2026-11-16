<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

interface ReviewRow {
    id: string;
    rating: number;
    title: string | null;
    body: string | null;
    status: string;
    created_at: string;
    product: { id: string; name: string; slug: string };
}

const props = defineProps<PageProps & {
    reviews: {
        data: ReviewRow[];
        links: { url: string | null; label: string; active: boolean }[];
    };
}>();

function statusClass(status: string): string {
    switch (status) {
        case 'approved':
            return 'bg-success/10 text-success dark:bg-success/20';
        case 'rejected':
            return 'bg-error/10 text-error dark:bg-error/20';
        default:
            return 'bg-warning/10 text-warning dark:bg-warning/20';
    }
}
</script>

<template>
    <Head title="My Reviews" />

        <div class="mx-auto max-w-4xl px-4 py-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-150 pb-6 dark:border-navy-600">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-navy-50">My Reviews</h1>
                    <p class="text-sm text-slate-400 dark:text-navy-300 mt-1">Manage feedback and ratings you have shared for purchased products.</p>
                </div>
            </div>

            <!-- Empty Reviews State -->
            <div v-if="reviews.data.length === 0" class="card mt-6 px-6 py-12 text-center dark:bg-navy-750">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto size-12 text-slate-300 dark:text-navy-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <p class="text-base font-medium text-slate-600 dark:text-navy-200">You haven't written any reviews yet.</p>
                <p class="text-sm mt-1 text-slate-400 dark:text-navy-300">Check your past orders to share feedback on items you've bought.</p>
                <Link
                    href="/customer/orders"
                    class="btn mt-5 inline-flex items-center space-x-2 bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus"
                >
                    <span>View your orders</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </Link>
            </div>

            <!-- Reviews List Section -->
            <div v-else class="mt-6 space-y-4">
                <div
                    v-for="review in reviews.data"
                    :key="review.id"
                    class="card p-5 transition-all duration-300 hover:shadow-lg dark:bg-navy-750 border border-slate-150 dark:border-navy-600"
                >
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="space-y-1.5 flex-1">
                            <Link
                                :href="`/customer/products/${review.product.slug}`"
                                class="text-base font-semibold text-slate-800 hover:text-primary dark:text-navy-50 dark:hover:text-accent-light transition-colors"
                            >
                                {{ review.product.name }}
                            </Link>
                            
                            <!-- Stars Rating -->
                            <div class="flex items-center gap-0.5 text-warning">
                                <span
                                    v-for="star in 5"
                                    :key="star"
                                    class="text-base"
                                    :class="star <= review.rating ? 'text-warning' : 'text-slate-300 dark:text-navy-600'"
                                >
                                    ★
                                </span>
                                <span class="ml-2 text-xs font-semibold text-slate-600 dark:text-navy-300">({{ review.rating }}/5)</span>
                            </div>
                        </div>

                        <div>
                            <span class="badge px-2.5 py-0.5 rounded-full font-semibold capitalize text-xs" :class="statusClass(review.status)">
                                {{ review.status }}
                            </span>
                        </div>
                    </div>

                    <!-- Review Content -->
                    <div class="mt-4 pt-4 border-t border-slate-150 dark:border-navy-600 space-y-2">
                        <p v-if="review.title" class="text-sm font-semibold text-slate-700 dark:text-navy-100">{{ review.title }}</p>
                        <p v-if="review.body" class="text-sm text-slate-500 dark:text-navy-300 leading-relaxed">{{ review.body }}</p>
                        <p class="text-xs text-slate-400 dark:text-navy-400 pt-1">{{ review.created_at }}</p>
                    </div>
                </div>
            </div>

            <!-- Pagination Links -->
            <nav v-if="reviews.links.length > 3" class="mt-8 flex flex-wrap justify-center gap-1.5">
                <template v-for="link in reviews.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="btn min-w-9 h-9 px-3 rounded-lg border font-medium text-xs transition-colors"
                        :class="link.active 
                            ? 'bg-primary text-white border-primary dark:bg-accent dark:border-accent' 
                            : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-150 dark:border-navy-500 dark:bg-navy-750 dark:text-navy-100 dark:hover:bg-navy-700'"
                        v-html="link.label"
                    />
                    <span 
                        v-else 
                        class="btn min-w-9 h-9 px-3 rounded-lg border border-slate-200 bg-slate-100 text-slate-400 text-xs dark:border-navy-600 dark:bg-navy-800 dark:text-navy-400 opacity-50 cursor-not-allowed" 
                        v-html="link.label" 
                    />
                </template>
            </nav>
        </div>
</template>