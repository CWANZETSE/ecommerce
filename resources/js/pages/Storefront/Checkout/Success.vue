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
            <!-- Success Icon Header -->
            <div
                class="inline-flex size-20 items-center justify-center">
                <svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="none">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                        <path fill="#2dd4bf" fill-rule="evenodd"
                            d="M3 10a7 7 0 019.307-6.611 1 1 0 00.658-1.889 9 9 0 105.98 7.501 1 1 0 00-1.988.22A7 7 0 113 10zm14.75-5.338a1 1 0 00-1.5-1.324l-6.435 7.28-3.183-2.593a1 1 0 00-1.264 1.55l3.929 3.2a1 1 0 001.38-.113l7.072-8z">
                        </path>
                    </g>
                </svg>
            </div>

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