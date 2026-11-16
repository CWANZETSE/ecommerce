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
        return Inertia::render('Admin/Banners/Index', [
            'banners' => Banner::orderBy('sort_order')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Banners/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['image_path'] = $request->file('image')->store('banners', 'public');
        unset($data['image']);

        Banner::create($data);

        return redirect()->route('admin_banners_index')->with('success', 'Banner created.');
    }

    public function edit(Banner $banner): Response
    {
        return Inertia::render('Admin/Banners/Edit', ['banner' => $banner]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image_path);
            $data['image_path'] = $request->file('image')->store('banners', 'public');
        }
        unset($data['image']);

        $banner->update($data);

        return redirect()->route('admin_banners_index')->with('success', 'Banner updated.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        Storage::disk('public')->delete($banner->image_path);
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