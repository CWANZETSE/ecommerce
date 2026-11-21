<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import {
    Bell,
    ChevronDown,
    LogOut,
    Moon,
    Search,
    Settings,
    Sun,
    User as UserIcon,
} from '@lucide/vue';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
] as const;

interface Breadcrumb {
    label: string;
    href?: string;
}

interface Props {
    breadcrumbs?: Breadcrumb[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const emit = defineEmits<{
    (e: 'toggle-sidebar'): void;
}>();

const page = usePage();
const user = computed(() => (page.props as any).auth?.user ?? null);
const currentPath = computed(() => page.url.split('?')[0]);

/* -------- Search -------- */
const searchOpen = ref(false);
const search = ref('');

function submitSearch() {
    const trimmed = search.value.trim();
    if (!trimmed) return;
    router.get('/customer/products', { search: trimmed }, {
        preserveState: false,
        replace: true,
    });
}

/* -------- Theme -------- */
const isDark = ref(false);


onMounted(() => {
    // isDark.value = document.documentElement.classList.contains('dark');
});

/* -------- User dropdown -------- */
const userMenuOpen = ref(false);

function closeUserMenu() {
    userMenuOpen.value = false;
}

function logout() {
    router.post('/logout');
}

const dropdownRef = ref<HTMLElement | null>(null);

function onDocumentClick(e: MouseEvent) {
    if (!dropdownRef.value) return;
    if (!dropdownRef.value.contains(e.target as Node)) {
        closeUserMenu();
    }
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') closeUserMenu();
}

onMounted(() => {
    document.addEventListener('click', onDocumentClick);
    document.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);
    document.removeEventListener('keydown', onKeydown);
});

const userInitial = computed(() => {
    const name = user.value?.name ?? '';
    return name.charAt(0).toUpperCase() || '?';
});
</script>

<template>
    <nav class="header before:bg-white dark:before:bg-navy-750 print:hidden">
        <div class="header-container relative flex w-full print:hidden">
            <div class="flex w-full items-center justify-between">

                <!-- Left: hamburger + breadcrumbs -->
                <div class="flex items-center gap-3">

                    <!-- Hamburger (mobile only) -->
                    <button
                        type="button"
                        class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25 md:hidden"
                        aria-label="Toggle menu"
                        @click="emit('toggle-sidebar')"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-slate-500 dark:text-navy-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                    </button>
                </div>

                <!-- Right: actions (unchanged from your version) -->
                <div class="-mr-1.5 flex items-center space-x-2">

                    

                    


                    <!-- Display-mode toggle -->
                    
<div
    class="inline-flex items-center gap-1 rounded-full bg-slate-100 p-1 dark:bg-navy-750"
    role="group"
    aria-label="Display mode"
>
    <button
        v-for="{ value, Icon, label } in tabs"
        :key="value"
        type="button"
        :aria-pressed="appearance === value"
        :aria-label="`Switch to ${label} mode`"
        @click="updateAppearance(value)"
        :class="[
            'inline-flex h-8 items-center justify-center gap-1.5 rounded-full px-3 text-sm transition-all duration-200',
            appearance === value
                ? 'bg-white text-slate-900 shadow-sm dark:bg-navy-700 dark:text-navy-200'
                : 'text-slate-500 hover:bg-slate-200/70 hover:text-slate-800 dark:text-slate-300 dark:hover:bg-slate-700/50 dark:hover:text-slate-100',
        ]"
    >
        <component
            :is="Icon"
            :class="[
                'size-4 shrink-0',
                value === 'dark'
                    ? 'text-amber-500'
                    : 'text-orange-500',
            ]"
        />

        <span>{{ label }}</span>
    </button>
</div>




                    <!-- User dropdown -->
                    <div v-if="user" ref="dropdownRef" class="relative">
                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-full px-2 py-1 transition-colors hover:bg-slate-300/20 dark:hover:bg-navy-300/20"
                            @click.stop="userMenuOpen = !userMenuOpen"
                        >
                            <div class="flex size-8 items-center justify-center rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light">
                                <span class="text-sm font-semibold">{{ userInitial }}</span>
                            </div>
                            <span class="hidden text-xs-plus font-medium text-slate-700 dark:text-navy-100 sm:inline">
                                {{ user.name }}
                            </span>
                            <span class="absolute -right-px -top-px flex size-3 items-center justify-center">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-secondary opacity-80"></span>
                            <span class="inline-flex size-2 rounded-full bg-secondary"></span>
                        </span>
                            <ChevronDown class="hidden size-3 text-slate-500 dark:text-navy-300 sm:inline" />
                        </button>

                        <div v-if="userMenuOpen" class="popper-root show absolute right-0 top-full z-50 mt-2">
                            <div class="popper-box w-64 overflow-hidden rounded-lg border border-slate-150 bg-white shadow-soft dark:border-navy-600 dark:bg-navy-700 dark:shadow-soft-dark">
                                <div class="flex items-center space-x-3 border-b border-slate-150 bg-slate-100 px-4 py-4 dark:border-navy-600 dark:bg-navy-800">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light">
                                        <UserIcon class="size-5" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-slate-800 dark:text-navy-50">{{ user.name }}</p>
                                        <p class="truncate text-xs text-slate-500 dark:text-navy-300">{{ user.email }}</p>
                                    </div>
                                </div>

                                

                                <div class=" p-2 dark:border-navy-600">
                                    <button type="button"
                                        class="flex w-full items-center gap-2.5 px-4 py-1.5 text-left text-sm font-medium text-error transition-colors hover:bg-error/10"
                                        @click="logout">
                                        <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-error/10 dark:bg-error/15">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5 text-rose-400 transition-colors hover:text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                            </svg>
                                        </div> Logout
    
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <template v-else>
                        <Link
                            href="/login"
                            class="btn h-8 rounded-full bg-primary px-4 text-xs-plus font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus"
                        >
                            Sign in
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </nav>
</template>