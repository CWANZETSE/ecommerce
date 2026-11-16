<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    links: PaginationLink[];
    /** Hide the component entirely when there are fewer pages than this (default 3). */
    minLinks?: number;
    /** Custom wrapper classes (e.g. spacing). */
    class?: string;
}

const props = withDefaults(defineProps<Props>(), {
    minLinks: 3,
    class: 'space-x-1.5',
});

const visible = computed(() => props.links.length > props.minLinks);

/**
 * Split Inertia's links into:
 *  - prev: the first item (label usually "&laquo; Previous")
 *  - pages: the middle items
 *  - next: the last item (label usually "Next &raquo;")
 */
const prev = computed(() => props.links[0] ?? null);
const pages = computed(() =>
    props.links.length > 2 ? props.links.slice(1, -1) : [],
);
const next = computed(() => props.links[props.links.length - 1] ?? null);

function decodeLabel(label: string): string {
    return label
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/&amp;/g, '&')
        .trim();
}
</script>

<template>
    <ol v-if="visible" class="pagination flex flex-wrap items-center" :class="props.class">
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
            <span
                v-else
                class="flex size-8 cursor-not-allowed items-center justify-center rounded-full bg-slate-150 text-slate-300 dark:bg-navy-500 dark:text-navy-500"
                aria-disabled="true"
            >
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
            <span
                v-else
                class="flex h-8 min-w-[2rem] cursor-not-allowed items-center justify-center rounded-full bg-slate-100 px-3 leading-tight text-slate-400 dark:bg-navy-600 dark:text-navy-500"
                v-html="decodeLabel(link.label)"
            />
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
            <span
                v-else
                class="flex size-8 cursor-not-allowed items-center justify-center rounded-full bg-slate-150 text-slate-300 dark:bg-navy-500 dark:text-navy-500"
                aria-disabled="true"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </span>
        </li>
    </ol>
</template>