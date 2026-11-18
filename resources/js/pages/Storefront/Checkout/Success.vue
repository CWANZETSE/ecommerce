<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import SuccessSVG from '@/components/SVG/successSVG.vue';
import { ArrowRight, ShoppingBag } from '@lucide/vue';

const props = defineProps<PageProps & {
    order: {
        ulid: string;
        order_number: string;
        grand_total: string;
        payment_status: string;
        customer_name: string;
        customer_phone: string;
        items: { product_name: string; sku: string; quantity: number; line_total: string }[];
    };
}>();

function badgeClass(status: string): string {
    switch (status) {
        case 'paid':
        case 'completed':
            return 'bg-success/10 text-success dark:bg-success/20';
        case 'pending':
            return 'bg-warning/10 text-warning dark:bg-warning/20';
        case 'failed':
        case 'cancelled':
            return 'bg-error/10 text-error dark:bg-error/20';
        default:
            return 'bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200';
    }
}
</script>

<template>
    <StorefrontLayout>

        <Head title="Order Received" />

        <div class="mx-auto max-w-2xl px-4 py-12 text-center">
            

            <span class="text-2xl font-bold text-teal-500 dark:text-teal-300">Order Placed Successfully!</span>
           

            <div
                class="inline-flex size-100 items-center justify-center">
                <SuccessSVG />
            </div>



            <!-- Action Button -->
            <div class="mt-2 flex flex-col sm:flex-row items-center justify-center gap-4">
                <Link href="/customer/products"
                    class="btn inline-flex items-center space-x-2 bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus px-6 py-3 rounded-xl shadow-lg transition-all duration-200">
                    <ShoppingBag class="size-4" />
                    <span>Continue shopping</span>
                    <ArrowRight class="size-4" />
                </Link>
                <Link href="/customer/orders"
                    class="btn inline-flex items-center space-x-2 bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 dark:bg-navy-700 dark:text-navy-100 dark:hover:bg-navy-600 px-6 py-3 rounded-xl transition-all duration-200">
                    <span>View all orders</span>
                </Link>
            </div>
        </div>
    </StorefrontLayout>
</template>