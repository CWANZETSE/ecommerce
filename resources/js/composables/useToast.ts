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

    let toastCounter = 0;

    watch(
        [
            () => page.url,
            () => (page.props as { flash?: FlashProps }).flash,
        ],
        ([, flash]) => {
            if (!flash) return;

            const uniqueId = () => `flash-${Date.now()}-${++toastCounter}`;

            // Dismiss currently visible notifications
            toast.dismiss();

            if (flash.success) {
                toast.success(flash.success, {
                    id: uniqueId(),
                });
            }

            if (flash.error) {
                toast.error(flash.error, {
                    id: uniqueId(),
                });
            }

            if (flash.warning) {
                toast.warning(flash.warning, {
                    id: uniqueId(),
                });
            }

            if (flash.info) {
                toast.info(flash.info, {
                    id: uniqueId(),
                });
            }
        },
        {
            immediate: true,
            deep: true,
        }
    );

    return { toast };
}