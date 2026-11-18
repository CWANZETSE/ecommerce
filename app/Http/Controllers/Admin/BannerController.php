<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
        public function index(): Response
    {
        $banners = Banner::orderBy('sort_order')
            ->paginate(9)
            ->withQueryString()
            ->through(fn (Banner $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'subtitle' => $b->subtitle,
                'image_url' => $b->image_url, // accessor → Storage::url()
                'is_active' => $b->is_active,
                'sort_order' => $b->sort_order,
            ]);

        return Inertia::render('Admin/Banners/Index', [
            'banners' => $banners,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Banners/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['image_path'] = Storage::putFile('banners', $request->file('image'));
        unset($data['image']);

        Banner::create($data);

        return redirect()->route('admin_banners_index')->with('success', 'Banner created.');
    }

    public function edit(Banner $banner): Response
    {
        return Inertia::render('Admin/Banners/Edit', [
            'banner' => [
                'id' => $banner->id,
                'title' => $banner->title,
                'subtitle' => $banner->subtitle,
                'image_url' => $banner->image_url,     // accessor → Storage::url()
                'link_url' => $banner->link_url,
                'link_text' => $banner->link_text,
                'sort_order' => $banner->sort_order,
                'is_active' => $banner->is_active,
            ],
        ]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($banner->image_path) {
                Storage::delete($banner->image_path);
            }

            $data['image_path'] = Storage::putFile('banners', $request->file('image'));
        }
        unset($data['image']);

        $banner->update($data);

        return redirect()->route('admin_banners_index')->with('success', 'Banner updated.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        if ($banner->image_path) {
            Storage::delete($banner->image_path);
        }

        $banner->delete();

        return redirect()->route('admin_banners_index')->with('success', 'Banner deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:500'],
            'link_text' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => [$request->isMethod('post') ? 'required' : 'nullable', 'image', 'max:4096'],
        ], [
            'title.required' => 'Please enter a banner title.',
            'image.required' => 'Please upload a banner image.',
        ]);
    }
}