<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    public function __construct(private SlugService $slugs, private LogService $logs) {}

    public function create(array $data, ?UploadedFile $image = null): Category
    {
        return DB::transaction(function () use ($data, $image) {
            $data['slug'] = $this->slugs->generate($data['name'], Category::class);

            if ($image) {
                $data['image_path'] = $image->store('categories', 'public');
            }

            $category = Category::create($data);
            $this->logs->log(
                'Created',
                $category,
                $data,
                "Created category \"{$category->name}\""
            );

            return $category;
        });
    }

    public function update(Category $category, array $data, ?UploadedFile $image = null): Category
    {
        return DB::transaction(function () use ($category, $data, $image) {
            if (isset($data['name']) && $data['name'] !== $category->name) {
                $data['slug'] = $this->slugs->generate(
                    $data['name'],
                    Category::class,
                    $category->id
                );
            }

            if ($image) {
                if ($category->image_path) {
                    Storage::disk('public')->delete($category->image_path);
                }

                $data['image_path'] = $image->store('categories', 'public');
            }

            // Apply incoming changes without saving yet
            $category->fill($data);

            // Get only attributes that have actually changed
            $changes = $category->getDirty();

            // Capture only the old values of changed attributes
            $fromRecords = [];
            foreach ($changes as $attribute => $newValue) {
                $fromRecords[$attribute] = $category->getOriginal($attribute);
            }

            // Save the changes
            $category->save();

            // Capture only the new values of changed attributes
            $toRecords = $category->getChanges();

            $this->logs->log(
                'Updated',
                $category,
                $data,
                "Updated category \"{$category->name}\"",
                $fromRecords,
                $toRecords
            );

            return $category->fresh();
        });
    }

    public function delete(Category $category): void
    {
        DB::transaction(function () use ($category) {
            $name = $category->name;

            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }

            $this->logs->log(
                'Deleted',
                $category,
                ['name' => $name],
                "Deleted category \"{$name}\""
            );

            $category->delete();
        });
    }
}
