// resources/js/composables/useToast.ts
import { toast } from 'vue-sonner';
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface FlashProps {
    success?: string | null;
    error?: string | null;
    warning?: string | null;
    info?: string | null;
}

export function useToast() {
    const page = usePage();

    watch(
        () => (page.props as { flash?: FlashProps }).flash,
        (flash) => {
            if (!flash) return;

            if (flash.success) toast.success(flash.success);
            if (flash.error) toast.error(flash.error);
            if (flash.warning) toast.warning(flash.warning);
            if (flash.info) toast.info(flash.info);
        },
        { immediate: true }
    );

    return { toast };
}