<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Services\SlugService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlugServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_unique_slug(): void
    {
        Category::factory()->create(['slug' => 'electronics']);
        $slugs = app(SlugService::class);

        $this->assertSame('electronics-2', $slugs->generate('Electronics', Category::class));
    }

    public function test_ignores_own_id_on_update(): void
    {
        $category = Category::factory()->create(['slug' => 'electronics', 'name' => 'Electronics']);
        $slugs = app(SlugService::class);

        $this->assertSame('electronics', $slugs->generate('Electronics', Category::class, $category->id));
    }
}