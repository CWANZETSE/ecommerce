<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    public function __construct(private BrandService $brands) {}

        public function index(): Response
    {
        $this->authorize('viewAny', Brand::class);

        $brands = Brand::withCount('products')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Brand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'logo_url' => $brand->logo_path ? Storage::url($brand->logo_path) : null,
                'is_active' => $brand->is_active,
                'products_count' => $brand->products_count,
            ]);

        return Inertia::render('Admin/Brands/Index', [
            'brands' => $brands,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Brand::class);

        return Inertia::render('Admin/Brands/Create');
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $this->authorize('create', Brand::class);

        $data = $request->validated();
        unset($data['logo']);

        $this->brands->create($data, $request->file('logo'));

        return redirect()
            ->route('admin_brands_index')
            ->with('success', 'Brand created successfully.');
    }

    public function edit(Brand $brand): Response
    {
        $this->authorize('update', $brand);

        return Inertia::render('Admin/Brands/Edit', [
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'logo_url' => $brand->logo_path ? Storage::url($brand->logo_path) : null,
                'is_active' => $brand->is_active,
            ],
        ]);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->authorize('update', $brand);

        $data = $request->validated();
        unset($data['logo']);

        $this->brands->update($brand, $data, $request->file('logo'));

        return redirect()
            ->route('admin_brands_index')
            ->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->authorize('delete', $brand);

        if ($brand->products()->exists()) {
            return back()->with('error', 'You cannot delete a brand that has products — reassign them first.');
        }

        $this->brands->delete($brand);

        return redirect()
            ->route('admin_brands_index')
            ->with('success', 'Brand deleted successfully.');
    }
}