<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator {
    data: any[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
}

interface Props {
    paginator: Paginator;
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    class: 'space-x-1.5',
});

// Safely access links
const links = computed(() => props.paginator?.links ?? []);

const prev = computed(() => links.value[0] ?? null);

const pages = computed(() =>
    links.value.length > 2 ? links.value.slice(1, -1) : [],
);

const next = computed(() => links.value[links.value.length - 1] ?? null);

// Safe fallbacks for the summary text
const from = computed(() => props.paginator?.from ?? 0);
const to = computed(() => props.paginator?.to ?? 0);
const total = computed(() => props.paginator?.total ?? 0);

function decodeLabel(label: string): string {
    return label
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/&amp;/g, '&')
        .trim();
}
</script>

<template>
    <!-- REMOVED v-if="visible" so it ALWAYS shows -->
    <div
        class="flex w-full flex-row flex-wrap items-center justify-between gap-4 border-t border-slate-150 px-4 py-4 dark:border-slate-150/15"
    >
        <!-- Pagination controls (Left) -->
        <ol
            class="pagination m-0 flex flex-nowrap items-center gap-1.5 p-0"
            :class="props.class"
        >
            <!-- Previous -->
            <li v-if="prev">
                <Link
                    v-if="prev.url"
                    :href="prev.url"
                    class="flex size-8 items-center justify-center rounded-full bg-slate-150 text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:bg-navy-500 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                    aria-label="Previous page"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <span v-else class="flex size-8 cursor-not-allowed items-center justify-center rounded-full bg-slate-150 text-slate-300 dark:bg-navy-500 dark:text-navy-500" aria-disabled="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </span>
            </li>

            <!-- Numbered pages -->
            <li v-for="(link, i) in pages" :key="`page-${i}-${link.label}`">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="flex h-8 min-w-[2rem] items-center justify-center rounded-full px-3 leading-tight transition-colors"
                    :class="
                        link.active
                            ? 'bg-primary text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90'
                            : 'bg-slate-150 hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:bg-navy-500 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90'
                    "
                    :aria-current="link.active ? 'page' : undefined"
                    v-html="decodeLabel(link.label)"
                />
                <span v-else class="flex h-8 min-w-[2rem] cursor-not-allowed items-center justify-center rounded-full bg-slate-100 px-3 leading-tight text-slate-400 dark:bg-navy-600 dark:text-navy-500" v-html="decodeLabel(link.label)" />
            </li>

            <!-- Next -->
            <li v-if="next">
                <Link
                    v-if="next.url"
                    :href="next.url"
                    class="flex size-8 items-center justify-center rounded-full bg-slate-150 text-slate-500 transition-colors hover:bg-slate-300 focus:bg-slate-300 active:bg-slate-300/80 dark:bg-navy-500 dark:text-navy-200 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                    aria-label="Next page"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>
                <span v-else class="flex size-8 cursor-not-allowed items-center justify-center rounded-full bg-slate-150 text-slate-300 dark:bg-navy-500 dark:text-navy-500" aria-disabled="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            </li>
        </ol>

        <!-- Results summary (Right) -->
        <p class="m-0 text-center text-xs font-medium text-slate-500 dark:text-navy-300 sm:text-right">
            Showing
            <span class="font-semibold text-slate-700 dark:text-navy-100">
                {{ from }}–{{ to }}
            </span>
            of
            <span class="font-semibold text-slate-700 dark:text-navy-100">
                {{ total }}
            </span>
            results
        </p>
    </div>
</template>