<?php

use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ShippingZoneController;
use App\Http\Controllers\Admin\StoreSettingController;
use App\Http\Controllers\Customer\AccountController;
use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\CouponController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ShopController;
use App\Http\Controllers\Storefront\SingleProductPageController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsCustomer;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/disk-check', function () {
    return [
        'default' => config('filesystems.default'),
        'aws_url_env' => env('AWS_URL'),
        'config_url' => config('filesystems.disks.bazario_cloud_disk.url'),
        'storage_url' => Storage::url('test.png'),
    ];
});

Route::get('/checkout/status/{payment:ulid}', [CheckoutController::class, 'status'])
    ->name('checkout_status');
Route::get('/checkout/waiting/{order:ulid}', [CheckoutController::class, 'waiting'])
    ->name('checkout_waiting');

Route::get('/', HomeController::class)->name('home');

// Storefront browsing (public — guests can browse)
Route::get('/customer/products', [ShopController::class, 'index'])
    ->name('customer_products_index');

Route::get('/customer/products/{slug}', [SingleProductPageController::class, 'show'])
    ->name('customer_products_show');

/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/

Route::prefix('cart')->name('cart_')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/items', [CartController::class, 'store'])->name('items_store');
    Route::patch('/items/{item}', [CartController::class, 'update'])->name('items_update');
    Route::delete('/items/{item}', [CartController::class, 'destroy'])->name('items_destroy');
});

Route::post('/cart/coupon', [CouponController::class, 'apply'])->name('cart_coupon_apply');
Route::delete('/cart/coupon', [CouponController::class, 'remove'])->name('cart_coupon_remove');

/*
|--------------------------------------------------------------------------
| Checkout
|--------------------------------------------------------------------------
*/

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout_index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout_store');
Route::get('/checkout/success/{order:ulid}', [CheckoutController::class, 'success'])
    ->name('checkout_success');

/*
|--------------------------------------------------------------------------
| Authenticated Customer Routes
|--------------------------------------------------------------------------
| Customers only — admins are redirected to the admin dashboard.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', EnsureUserIsCustomer::class])
    ->prefix('customer')
    ->name('customer_')
    ->group(function () {

        // Account
        Route::get('/account', [AccountController::class, 'index'])->name('account_index');
        Route::patch('/account', [AccountController::class, 'update'])->name('account_update');
        Route::patch('/account/password', [AccountController::class, 'updatePassword'])->name('account_password');

        // Orders
        Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders_index');
        Route::get('/orders/{order:ulid}', [CustomerOrderController::class, 'show'])->name('orders_show');

        // Addresses
        Route::get('/addresses', [AddressController::class, 'index'])->name('addresses_index');
        Route::post('/addresses', [AddressController::class, 'store'])->name('addresses_store');
        Route::patch('/addresses/{address}', [AddressController::class, 'update'])->name('addresses_update');
        Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses_destroy');
        Route::patch('/addresses/{address}/default', [AddressController::class, 'setDefault'])
            ->name('addresses_default');

        // Reviews
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews_index');
        Route::post('/orders/{order:ulid}/items/{item}/review', [ReviewController::class, 'store'])
            ->name('reviews_store');
    });

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->name('admin_')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports_index');
        Route::get('/reports/export/sales', [ReportController::class, 'exportSales'])->name('reports_export_sales');
        Route::get('/reports/export/inventory', [ReportController::class, 'exportInventory'])->name('reports_export_inventory');

        Route::resource('banners', BannerController::class)
            ->names([
                'index' => 'banners_index',
                'create' => 'banners_create',
                'store' => 'banners_store',
                'edit' => 'banners_edit',
                'update' => 'banners_update',
                'destroy' => 'banners_destroy',
            ]);

        // Reviews moderation
        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews_index');
        Route::patch('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])
            ->name('reviews_approve');
        Route::patch('/reviews/{review}/reject', [AdminReviewController::class, 'reject'])
            ->name('reviews_reject');

        // Categories
        Route::resource('categories', CategoryController::class)
            ->names([
                'index' => 'categories_index',
                'create' => 'categories_create',
                'store' => 'categories_store',
                'show' => 'categories_show',
                'edit' => 'categories_edit',
                'update' => 'categories_update',
                'destroy' => 'categories_destroy',
            ]);

        // Brands
        Route::resource('brands', BrandController::class)
            ->names([
                'index' => 'brands_index',
                'create' => 'brands_create',
                'store' => 'brands_store',
                'show' => 'brands_show',
                'edit' => 'brands_edit',
                'update' => 'brands_update',
                'destroy' => 'brands_destroy',
            ]);

        // Products
        Route::resource('products', ProductController::class)
            ->names([
                'index' => 'products_index',
                'create' => 'products_create',
                'store' => 'products_store',
                'show' => 'products_show',
                'edit' => 'products_edit',
                'update' => 'products_update',
                'destroy' => 'products_destroy',
            ]);

        // Product images
        Route::prefix('products/{product}/images')->name('products_images_')->group(function () {
            Route::post('/', [ProductImageController::class, 'store'])->name('store');
            Route::patch('/{image}/primary', [ProductImageController::class, 'setPrimary'])->name('primary');
            Route::delete('/{image}', [ProductImageController::class, 'destroy'])->name('destroy');
        });

        // Shipping
        Route::get('/shipping', [ShippingZoneController::class, 'index'])->name('shipping_index');
        Route::post('/shipping', [ShippingZoneController::class, 'store'])->name('shipping_store');
        Route::post('/shipping/bulk', [ShippingZoneController::class, 'bulkUpdate'])->name('shipping_bulk');
        Route::patch('/shipping/{zone}', [ShippingZoneController::class, 'update'])->name('shipping_update');
        Route::patch('/shipping/{zone}/toggle', [ShippingZoneController::class, 'toggle'])->name('shipping_toggle');
        Route::delete('/shipping/{zone}', [ShippingZoneController::class, 'destroy'])->name('shipping_destroy');

        // Coupons
        Route::resource('coupons', AdminCouponController::class)
            ->names([
                'index' => 'coupons_index',
                'create' => 'coupons_create',
                'store' => 'coupons_store',
                'show' => 'coupons_show',
                'edit' => 'coupons_edit',
                'update' => 'coupons_update',
                'destroy' => 'coupons_destroy',
            ]);

        // Store settings
        Route::get('/settings', [StoreSettingController::class, 'index'])->name('settings_index');
        Route::patch('/settings', [StoreSettingController::class, 'update'])->name('settings_update');

        // Orders
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders_index');
        Route::get('/orders/{order:ulid}', [AdminOrderController::class, 'show'])->name('orders_show');
        Route::get('/orders/{order:ulid}/receipt/pdf', [AdminOrderController::class, 'orderPrintReceipt'])
            ->name('orders_print_receipt');
        Route::get('/orders/receipt/{order:ulid}', [AdminOrderController::class, 'showReceipt'])->name('orders_show_receipt');
        Route::patch('/orders/{order:ulid}/fulfilment', [AdminOrderController::class, 'updateFulfilment'])
            ->name('orders_fulfilment');
        Route::post('/orders/{order:ulid}/notes', [AdminOrderController::class, 'addNote'])
            ->name('orders_notes_store');
        Route::post('/orders/{order:ulid}/cancel', [AdminOrderController::class, 'cancel'])
            ->name('orders_cancel');

        // Attributes
        Route::prefix('attributes')->name('attributes_')->group(function () {
            Route::get('/', [AttributeController::class, 'index'])->name('index');
            Route::post('/', [AttributeController::class, 'store'])->name('store');
            Route::patch('/{attribute}', [AttributeController::class, 'update'])->name('update');
            Route::delete('/{attribute}', [AttributeController::class, 'destroy'])->name('destroy');
            Route::post('/{attribute}/values', [AttributeController::class, 'storeValue'])->name('values_store');
            Route::delete('/{attribute}/values/{value}', [AttributeController::class, 'destroyValue'])
                ->name('values_destroy');
        });

        // Inventory
        Route::prefix('inventory')->name('inventory_')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::get('/{variation}/movements', [InventoryController::class, 'movements'])->name('movements');
            Route::post('/{variation}/adjust', [InventoryController::class, 'adjust'])->name('adjust');
        });
    });

/*
|--------------------------------------------------------------------------
| Webhooks
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';
