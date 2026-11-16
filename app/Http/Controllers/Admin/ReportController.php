<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $from = $request->filled('from') ? $request->date('from') : now()->subDays(30);
        $to = $request->filled('to') ? $request->date('to') : now();

        $sales = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);

        $summary = [
            'gross_sales' => (clone $sales)->sum('subtotal'),
            'shipping' => (clone $sales)->sum('shipping_fee'),
            'discounts' => (clone $sales)->sum('discount_total'),
            'net_revenue' => (clone $sales)->sum('grand_total'),
            'orders_paid' => (clone $sales)->count(),
            'orders_pending' => Order::where('payment_status', 'pending')
                ->whereBetween('created_at', [$from, $to])->count(),
            'orders_cancelled' => Order::where('fulfilment_status', 'cancelled')
                ->whereBetween('created_at', [$from, $to])->count(),
        ];

        $daily = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date, SUM(grand_total) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('Admin/Reports/Index', [
            'summary' => $summary,
            'daily' => $daily,
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'topProducts' => $this->topProducts($from, $to),
            'topCategories' => $this->topCategories($from, $to),
            'topCustomers' => $this->topCustomers($from, $to),
            'paymentMethods' => $this->paymentMethods($from, $to),
            'couponUsage' => $this->couponUsage($from, $to),
            'inventory' => $this->inventorySummary(),
        ]);
    }

    private function topProducts($from, $to)
    {
        return OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw('order_items.product_name, order_items.sku,
                         SUM(order_items.quantity) as units_sold,
                         SUM(order_items.line_total) as revenue')
            ->groupBy('order_items.product_name', 'order_items.sku')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();
    }

    private function topCategories($from, $to)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variations', 'order_items.variation_id', '=', 'product_variations.id')
            ->join('products', 'product_variations.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw('categories.name as category,
                         SUM(order_items.quantity) as units_sold,
                         SUM(order_items.line_total) as revenue')
            ->groupBy('categories.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();
    }

    private function topCustomers($from, $to)
    {
        return Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('customer_email, customer_name, COUNT(*) as orders, SUM(grand_total) as total_spent')
            ->groupBy('customer_email', 'customer_name')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();
    }

    private function paymentMethods($from, $to)
    {
        return Payment::where('status', 'succeeded')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('provider, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('provider')
            ->get();
    }

    private function couponUsage($from, $to)
    {
        return DB::table('coupon_usages')
            ->join('coupons', 'coupon_usages.coupon_id', '=', 'coupons.id')
            ->whereBetween('coupon_usages.created_at', [$from, $to])
            ->selectRaw('coupons.code, coupons.type, COUNT(*) as redemptions,
                         SUM(coupon_usages.discount_amount) as total_discount')
            ->groupBy('coupons.code', 'coupons.type')
            ->orderByDesc('total_discount')
            ->get();
    }

    private function inventorySummary(): array
    {
        return [
            'total_variations' => ProductVariation::count(),
            'out_of_stock' => ProductVariation::where('stock_quantity', '<=', 0)->count(),
            'low_stock' => ProductVariation::where('stock_quantity', '>', 0)
                ->whereRaw('stock_quantity <= COALESCE(low_stock_threshold, 5)')
                ->count(),
            'stock_value' => (float) DB::table('product_variations')
                ->join('products', 'product_variations.product_id', '=', 'products.id')
                ->selectRaw('SUM(product_variations.stock_quantity * COALESCE(product_variations.price, products.price)) as value')
                ->value('value') ?? 0,
        ];
    }

    public function exportSales(Request $request)
    {
        $from = $request->filled('from') ? $request->date('from') : now()->subDays(30);
        $to = $request->filled('to') ? $request->date('to') : now();

        $filename = "sales-{$from->toDateString()}-to-{$to->toDateString()}.csv";

        return response()->streamDownload(function () use ($from, $to) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, ['Date', 'Order #', 'Customer', 'Email', 'Payment', 'Fulfilment', 'Subtotal', 'Shipping', 'Discount', 'Total']);

            Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$from, $to])
                ->orderBy('created_at')
                ->chunk(500, function ($orders) use ($fh) {
                    foreach ($orders as $o) {
                        fputcsv($fh, [
                            $o->created_at->toDateString(),
                            $o->order_number,
                            $o->customer_name,
                            $o->customer_email,
                            $o->payment_status->value,
                            $o->fulfilment_status->value,
                            $o->subtotal,
                            $o->shipping_fee,
                            $o->discount_total,
                            $o->grand_total,
                        ]);
                    }
                });

            fclose($fh);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportInventory()
    {
        return response()->streamDownload(function () {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, ['Product', 'SKU', 'Variation SKU', 'Stock', 'Low threshold', 'Unit price', 'Stock value']);

            ProductVariation::with('product:id,name,price')
                ->orderBy('sku')
                ->chunk(500, function ($variations) use ($fh) {
                    foreach ($variations as $v) {
                        $price = (float) ($v->price ?? $v->product->price);
                        fputcsv($fh, [
                            $v->product->name,
                            $v->product->sku,
                            $v->sku,
                            $v->stock_quantity,
                            $v->low_stock_threshold ?? 5,
                            number_format($price, 2, '.', ''),
                            number_format($price * $v->stock_quantity, 2, '.', ''),
                        ]);
                    }
                });

            fclose($fh);
        }, 'inventory-' . now()->toDateString() . '.csv', ['Content-Type' => 'text/csv']);
    }
}