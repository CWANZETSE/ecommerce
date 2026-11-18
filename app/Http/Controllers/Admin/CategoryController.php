<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categories) {}

   
public function index(): Response
{
    $this->authorize('viewAny', Category::class);

    $roots = Category::roots()
        ->with([
            'children' => fn ($q) => $q
                ->withCount('products')
                ->orderBy('sort_order'),
        ])
        ->withCount('products')
        ->orderBy('sort_order')
        ->paginate(10)
        ->withQueryString()
        ->through(function (Category $root) {
            $root->setAttribute(
                'image_url',
                $root->image_path ? Storage::url($root->image_path) : null
            );

            $root->children->each(function (Category $child) {
                $child->setAttribute(
                    'image_url',
                    $child->image_path ? Storage::url($child->image_path) : null
                );
            });

            return $root;
        });

    return Inertia::render('Admin/Categories/Index', [
        'categories' => $roots,
    ]);
}


    public function create(): Response
    {
        $this->authorize('create', Category::class);

        return Inertia::render('Admin/Categories/Create', [
            'parents' => Category::roots()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $data = $request->validated();
        unset($data['image']);

        $this->categories->create($data, $request->file('image'));

        return redirect()
            ->route('admin_categories_index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category): Response
    {
        $this->authorize('update', $category);

        return Inertia::render('Admin/Categories/Edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'parent_id' => $category->parent_id,
                'description' => $category->description,
                'image_url' => $category->image_path ? Storage::url($category->image_path) : null,
                'is_active' => $category->is_active,
                'sort_order' => $category->sort_order,
                'seo_title' => $category->seo_title,
                'seo_description' => $category->seo_description,
            ],
            'parents' => Category::roots()
                ->where('id', '!=', $category->id)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $data = $request->validated();
        unset($data['image']);

        $this->categories->update($category, $data, $request->file('image'));

        return redirect()
            ->route('admin_categories_index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->children()->exists()) {
            return back()->with('error', 'You cannot delete a category that has subcategories — remove or reassign them first.');
        }

        if ($category->products()->exists()) {
            return back()->with('error', 'You cannot delete a category that has products — move those products first.');
        }

        $this->categories->delete($category);

        return redirect()
            ->route('admin_categories_index')
            ->with('success', 'Category deleted successfully.');
    }
}