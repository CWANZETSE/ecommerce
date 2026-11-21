<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BrandService
{
    public function __construct(private SlugService $slugs, private LogService $logs) {}

    public function create(array $data, ?UploadedFile $logo = null): Brand
    {
        return DB::transaction(function () use ($data, $logo) {
            $data['slug'] = $this->slugs->generate($data['name'], Brand::class);

            if ($logo) {
                $data['logo_path'] = $logo->store('brands', 'public');
            }

            $brand = Brand::create($data);
            $this->logs->log(
                'Created',
                $brand,
                $data,
                "Created brand \"{$brand->name}\""
            );

            return $brand;
        });
    }

    public function update(Brand $brand, array $data, ?UploadedFile $logo = null): Brand
    {
        return DB::transaction(function () use ($brand, $data, $logo) {
            if (isset($data['name']) && $data['name'] !== $brand->name) {
                $data['slug'] = $this->slugs->generate(
                    $data['name'],
                    Brand::class,
                    $brand->id
                );
            }

            if ($logo) {
                // Remove the previous logo if it exists
                if ($brand->logo_path) {
                    Storage::disk('public')->delete($brand->logo_path);
                }

                $data['logo_path'] = $logo->store('brands', 'public');
            }

            // Apply changes without saving
            $brand->fill($data);

            // Capture only changed attributes
            $changes = $brand->getDirty();

            $fromRecords = [];
            foreach ($changes as $attribute => $newValue) {
                $fromRecords[$attribute] = $brand->getOriginal($attribute);
            }

            // Save changes
            $brand->save();

            // Capture new values of changed attributes
            $toRecords = $brand->getChanges();

            // Log update
            $this->logs->log(
                'Updated',
                $brand,
                $data,
                "Updated brand \"{$brand->name}\"",
                $fromRecords,
                $toRecords
            );

            return $brand->fresh();
        });
    }

    public function delete(Brand $brand): void
    {
        DB::transaction(function () use ($brand) {
            if ($brand->logo_path) {
                Storage::disk('public')->delete($brand->logo_path);
            }
            $name = $brand->name;
            $this->logs->log(
                'Deleted',
                $brand,
                ['name' => $name],
                "Deleted brand \"{$name}\""
            );
            $brand->delete();
        });
    }
}
