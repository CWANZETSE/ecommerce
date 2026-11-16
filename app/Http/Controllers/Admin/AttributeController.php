<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AttributeController extends Controller
{
    public function __construct(private SlugService $slugs) {}

    public function index(): Response
    {
        $attributes = ProductAttribute::with(['values' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Attributes/Index', [
            'attributes' => $attributes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($data) {
            ProductAttribute::create([
                'name' => $data['name'],
                'slug' => $this->slugs->generate($data['name'], ProductAttribute::class),
            ]);
        });

        return back()->with('success', 'Attribute created.');
    }

    public function update(Request $request, ProductAttribute $attribute): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $attribute->update([
            'name' => $data['name'],
            'slug' => $this->slugs->generate($data['name'], ProductAttribute::class, $attribute->id),
        ]);

        return back()->with('success', 'Attribute updated.');
    }

    public function destroy(ProductAttribute $attribute): RedirectResponse
    {
        $attribute->delete();
        return back()->with('success', 'Attribute deleted.');
    }

    public function storeValue(Request $request, ProductAttribute $attribute): RedirectResponse
    {
        $data = $request->validate([
            'value' => ['required', 'string', 'max:100'],
        ]);

        $exists = $attribute->values()->where('value', $data['value'])->exists();
        if ($exists) {
            return back()->withErrors(['value' => 'This value already exists for the attribute.']);
        }

        $attribute->values()->create([
            'value' => $data['value'],
            'sort_order' => $attribute->values()->count(),
        ]);

        return back()->with('success', 'Value added.');
    }

    public function destroyValue(ProductAttribute $attribute, ProductAttributeValue $value): RedirectResponse
    {
        abort_unless($value->attribute_id === $attribute->id, 404);
        $value->delete();
        return back()->with('success', 'Value removed.');
    }
}