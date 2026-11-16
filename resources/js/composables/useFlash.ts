import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { Flash, PageProps } from '@/types';

export function useFlash() {
    const page = usePage<PageProps>();

    const flash = computed<Flash>(() => page.props.flash ?? {});

    return { flash };
}