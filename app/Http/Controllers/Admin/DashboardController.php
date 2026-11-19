<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderFulfilmentStatus;
use App\Enums\OrderPaymentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Brand::class);

        // Date Filtering (Defaults to Last 30 days if none provided)
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : now()->subDays(29)->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        // Basic Stats
        $revenue = Order::where('payment_status', OrderPaymentStatus::Paid)
            ->whereBetween('created_at', [$from, $to])
            ->sum('grand_total');

        $totalOrders = Order::whereBetween('created_at', [$from, $to])->count();
        $pendingOrders = Order::where('fulfilment_status', OrderFulfilmentStatus::Pending)
            ->whereBetween('created_at', [$from, $to])->count();

        // Inventory Stats
        $totalProducts = Product::active()->count();
        $lowStockCount = ProductVariation::where('stock_quantity', '>', 0)
            ->whereRaw('stock_quantity <= COALESCE(low_stock_threshold, 5)')->count();
        $outOfStockCount = ProductVariation::where('stock_quantity', '<=', 0)->count();

        // New Customers
        $newCustomers = User::where('role', UserRole::Customer)
            ->whereBetween('created_at', [$from, $to])->count();

        // --- Chart Data ---

        // 1. Revenue Trend (Group by Date)
        $revenueTrend = Order::where('payment_status', OrderPaymentStatus::Paid)
            ->whereBetween('created_at', [$from, $to])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(grand_total) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 2. Order Status Breakdown (Ensure ALL Enums are displayed)
        // 2. Order Status Breakdown

$rawOrderStatus = Order::whereBetween('created_at', [$from, $to])
    ->select('fulfilment_status', DB::raw('COUNT(*) as count'))
    ->groupBy('fulfilment_status')
    ->pluck('count', 'fulfilment_status')
    ->toArray();

$orderStatusBreakdown = collect(OrderFulfilmentStatus::cases())
    ->map(function ($status) use ($rawOrderStatus) {
        return [
            'status' => ucfirst($status->value),
            'count' => (int) ($rawOrderStatus[$status->value] ?? 0),
        ];
    })
    ->values();
        

        // 3. Payment Status Breakdown (Ensure ALL Enums are displayed)
        // 3. Payment Status Breakdown

$rawPaymentStatus = Order::whereBetween('created_at', [$from, $to])
    ->select('payment_status', DB::raw('COUNT(*) as count'))
    ->groupBy('payment_status')
    ->pluck('count', 'payment_status')
    ->toArray();

$paymentStatusBreakdown = collect(OrderPaymentStatus::cases())
    ->map(function ($status) use ($rawPaymentStatus) {
        return [
            'status' => ucfirst($status->value),
            'count' => (int) ($rawPaymentStatus[$status->value] ?? 0),
        ];
    })
    ->values();

        // 4. Recent Orders
        $recentOrders = Order::latest()->take(5)->get()->map(fn ($o) => [
            'ulid' => $o->ulid,
            'order_number' => $o->order_number,
            'customer_name' => $o->customer_name,
            'grand_total' => $o->grand_total,
            'payment_status' => $o->payment_status->value,
            'fulfilment_status' => $o->fulfilment_status->value,
            'created_at' => $o->created_at->format('M d, Y H:i'),
        ]);

        // 5. Top Selling Products
        $topProducts = OrderItem::whereHas('order', function ($q) use ($from, $to) {
            $q->where('payment_status', OrderPaymentStatus::Paid)
                ->whereBetween('created_at', [$from, $to]);
        })
            ->select('product_name', 'sku')
            ->selectRaw('SUM(quantity) as total_sold, SUM(line_total) as total_revenue')
            ->groupBy('product_name', 'sku')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        // 6. Recent Activity Log
        $recentActivity = ActivityLog::with('user:id,name')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'user_name' => $log->user?->name ?? 'System',
                'created_at' => $log->created_at->diffForHumans(),
            ]);

        // 7. Top Customers
        $topCustomers = Order::where('payment_status', OrderPaymentStatus::Paid)
            ->whereBetween('created_at', [$from, $to])
            ->select('customer_name', 'customer_email')
            ->selectRaw('SUM(grand_total) as total_spent, COUNT(id) as order_count')
            ->groupBy('customer_name', 'customer_email')
            ->orderByDesc('total_spent')
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard/Index', [
            'filters' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
            'stats' => [
                'revenue' => number_format($revenue, 2),
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'new_customers' => $newCustomers,
                'total_products' => $totalProducts,
                'low_stock' => $lowStockCount,
                'out_of_stock' => $outOfStockCount,
            ],
            'charts' => [
                'revenue_trend' => $revenueTrend,
                'order_status' => $orderStatusBreakdown,
                'payment_status' => $paymentStatusBreakdown,
            ],
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'recentActivity' => $recentActivity,
            'topCustomers' => $topCustomers,
        ]);
    }
}