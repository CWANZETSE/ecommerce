<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Services\ImageService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $products,
        private ImageService $images,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::query()
            ->with(['category:id,name', 'brand:id,name', 'primaryImage', 'variations:id,product_id,stock_quantity'])
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            }))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->category))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => Category::select('id', 'name')->orderBy('name')->get(),
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Product::class);

        return Inertia::render('Admin/Products/Create', [
            'categories' => Category::active()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::active()->orderBy('name')->get(['id', 'name']),
            'attributes' => ProductAttribute::with('values')->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $data = $request->validated();
        $images = $data['images'] ?? [];
        unset($data['images']);

        $product = $this->products->create($data);

        foreach ($images as $image) {
            $this->images->store($product, $image);
        }

        return redirect()
            ->route('admin_products_index', $product)
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);

        $product->load(['images', 'variations.attributeValues.attribute']);

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => Category::active()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::active()->orderBy('name')->get(['id', 'name']),
            'attributes' => ProductAttribute::with('values')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = $request->validated();
        $images = $data['images'] ?? [];
        unset($data['images']);

        $this->products->update($product, $data);

        foreach ($images as $image) {
            $this->images->store($product, $image);
        }

        return redirect()
            ->route('admin_products_index')
        ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->products->delete($product);

        return redirect()
            ->route('admin_products_index')
            ->with('success', 'Product deleted.');
    }
}