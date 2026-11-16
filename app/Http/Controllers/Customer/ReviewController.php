<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(): Response
    {
        $reviews = ProductReview::where('user_id', auth()->id())
            ->with(['product:id,name,slug'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('Customer/Reviews/Index', [
            'reviews' => $reviews,
        ]);
    }

    public function store(Request $request, Order $order, OrderItem $item): RedirectResponse
    {
        abort_unless($order->user_id === auth()->id(), 403);
        abort_unless($item->order_id === $order->id, 404);
        abort_unless($order->payment_status->value === 'paid', 403, 'You can only review paid orders.');
        abort_unless($order->fulfilment_status->value === 'delivered', 403, 'You can only review delivered orders.');
        abort_unless($item->variation_id !== null, 422, 'This item cannot be reviewed.');

        $productId = $item->variation->product_id;

        // Check for existing review on this exact order item
        $exists = ProductReview::where('user_id', auth()->id())
            ->where('order_item_id', $item->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'You have already reviewed this item.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['nullable', 'string', 'max:2000'],
        ], [
            'rating.required' => 'Please choose a star rating.',
            'rating.between' => 'The rating must be between 1 and 5 stars.',
        ]);

        ProductReview::create([
            'product_id' => $productId,
            'user_id' => auth()->id(),
            'order_item_id' => $item->id,
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
            'is_verified_purchase' => true,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Review submitted — it will appear once approved.');
    }
}