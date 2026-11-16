<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    public function __construct(private SlugService $slugs) {}

    public function create(array $data, ?UploadedFile $image = null): Category
    {
        return DB::transaction(function () use ($data, $image) {
            $data['slug'] = $this->slugs->generate($data['name'], Category::class);

            if ($image) {
                $data['image_path'] = $image->store('categories', 'public');
            }

            $category = Category::create($data);
            $this->log('category.created', $category, $data);

            return $category;
        });
    }

    public function update(Category $category, array $data, ?UploadedFile $image = null): Category
    {
        return DB::transaction(function () use ($category, $data, $image) {
            if (isset($data['name']) && $data['name'] !== $category->name) {
                $data['slug'] = $this->slugs->generate($data['name'], Category::class, $category->id);
            }

            if ($image) {
                if ($category->image_path) {
                    Storage::disk('public')->delete($category->image_path);
                }
                $data['image_path'] = $image->store('categories', 'public');
            }

            $category->update($data);
            $this->log('category.updated', $category, $data);

            return $category->fresh();
        });
    }

    public function delete(Category $category): void
    {
        DB::transaction(function () use ($category) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }

            $this->log('category.deleted', $category, ['name' => $category->name]);
            $category->delete();
        });
    }

    private function log(string $action, Category $subject, array $props = []): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => Category::class,
            'subject_id' => $subject->id,
            'properties' => $props,
            'ip' => request()->ip(),
        ]);
    }
}