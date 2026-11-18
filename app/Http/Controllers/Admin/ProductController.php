<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Services\ImageService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
        ->with([
            'category:id,name',
            'brand:id,name',
            'primaryImage',
            'variations:id,product_id,stock_quantity',
        ])
        ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
            $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('sku', 'like', "%{$request->search}%");
        }))
        ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->category))
        ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
        ->latest()
        ->paginate(10)
        ->withQueryString()
        ->through(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'is_active' => $p->is_active,
            'is_featured' => $p->is_featured,
            'category' => $p->category ? [
                'id' => $p->category->id,
                'name' => $p->category->name,
            ] : null,
            'primary_image_url' => $p->primary_image_url,
            'variations' => $p->variations->map(fn ($v) => [
                'id' => $v->id,
                'product_id' => $v->product_id,
                'stock_quantity' => (int) $v->stock_quantity,
            ])->values(),
        ]);

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

        $defaultCount = collect($data['variations'])
            ->where('is_default', true)
            ->count();

        if ($defaultCount !== 1) {
            throw ValidationException::withMessages([
                'variations' => 'Exactly one variation must be selected as the default.',
            ]);
        }

        $product = $this->products->create($data);

        foreach ($images as $image) {
            $this->images->store($product, $image);
        }

        return redirect()
            ->route('admin_products_index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);

        $product->load(['images', 'variations.attributeValues.attribute']);

        return Inertia::render('Admin/Products/Edit', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category_id' => $product->category_id,
                'brand_id' => $product->brand_id,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'is_active' => $product->is_active,
                'is_featured' => $product->is_featured,
                'show_on_homepage' => $product->show_on_homepage,
                'images' => $product->images->map(fn (ProductImage $img) => [
                    'id' => $img->id,
                    'path' => $img->path,
                    'url' => $img->image_url,                 // accessor → Storage::url()
                    'is_primary' => $img->is_primary,
                    'alt' => $img->alt,
                ])->values(),
                'variations' => $product->variations->map(fn ($v) => [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'price' => $v->price,
                    'sale_price' => $v->sale_price,
                    'is_default' => $v->is_default,
                    'stock_quantity' => (int) $v->stock_quantity,
                    'low_stock_threshold' => $v->low_stock_threshold,
                    'attribute_values' => $v->attributeValues->map(fn ($av) => [
                        'id' => $av->id,
                        'value' => $av->value,
                        'attribute' => [
                            'name' => $av->attribute->name,
                        ],
                    ])->values(),
                ])->values(),
            ],
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

    $defaultCount = collect($data['variations'] ?? [])
        ->filter(fn ($variation) => filter_var(
            $variation['is_default'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        ))
        ->count();

    if ($defaultCount !== 1) {
        return back()->withInput()->with(
            'error',
            'Exactly one variation must be selected as the default.'
        );
    }

    try {
        $this->products->update($product, $data);

        foreach ($images as $image) {
            $this->images->store($product, $image);
        }

        return redirect()
            ->route('admin_products_index')
            ->with('success', 'Product updated successfully.');

    } catch (\Throwable $e) {
        report($e);

        return back()->withInput()->with(
            'error',
            $e instanceof \Illuminate\Validation\ValidationException
                ? $e->validator->errors()->first()
                : 'Unable to update product. Please try again.'
        );
    }
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
