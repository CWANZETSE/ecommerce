<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BrandService
{
    public function __construct(private SlugService $slugs) {}

    public function create(array $data, ?UploadedFile $logo = null): Brand
    {
        return DB::transaction(function () use ($data, $logo) {
            $data['slug'] = $this->slugs->generate($data['name'], Brand::class);

            if ($logo) {
                $data['logo_path'] = $logo->store('brands', 'public');
            }

            $brand = Brand::create($data);
            $this->log('brand.created', $brand, $data);

            return $brand;
        });
    }

    public function update(Brand $brand, array $data, ?UploadedFile $logo = null): Brand
    {
        return DB::transaction(function () use ($brand, $data, $logo) {
            if (isset($data['name']) && $data['name'] !== $brand->name) {
                $data['slug'] = $this->slugs->generate($data['name'], Brand::class, $brand->id);
            }

            if ($logo) {
                // Remove the previous logo if it exists
                if ($brand->logo_path) {
                    Storage::disk('public')->delete($brand->logo_path);
                }
                $data['logo_path'] = $logo->store('brands', 'public');
            }

            $brand->update($data);
            $this->log('brand.updated', $brand, $data);

            return $brand->fresh();
        });
    }

    public function delete(Brand $brand): void
    {
        DB::transaction(function () use ($brand) {
            if ($brand->logo_path) {
                Storage::disk('public')->delete($brand->logo_path);
            }

            $this->log('brand.deleted', $brand, ['name' => $brand->name]);
            $brand->delete();
        });
    }

    private function log(string $action, Brand $subject, array $props = []): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => Brand::class,
            'subject_id' => $subject->id,
            'properties' => $props,
            'ip' => request()->ip(),
        ]);
    }
}