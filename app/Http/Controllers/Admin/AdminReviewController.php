<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $reviews = ProductReview::with(['product:id,name,slug', 'user:id,name,email'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'filters' => ['status' => $request->get('status')],
            'counts' => [
                'pending' => ProductReview::where('status', 'pending')->count(),
                'approved' => ProductReview::where('status', 'approved')->count(),
                'rejected' => ProductReview::where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function approve(ProductReview $review): RedirectResponse
    {
        $review->update(['status' => 'approved', 'approved_at' => now()]);
        return back()->with('success', 'Review approved.');
    }

    public function reject(ProductReview $review): RedirectResponse
    {
        $review->update(['status' => 'rejected']);
        return back()->with('success', 'Review rejected.');
    }
}