<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryDepthTest extends TestCase
{
    use RefreshDatabase;

    public function test_third_level_category_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $root = Category::factory()->create(['parent_id' => null]);
        $child = Category::factory()->create(['parent_id' => $root->id]);

        $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Grandchild',
            'parent_id' => $child->id,
            'is_active' => true,
        ])->assertSessionHasErrors('parent_id');
    }

    public function test_second_level_category_is_accepted(): void
    {
        $admin = User::factory()->admin()->create();
        $root = Category::factory()->create(['parent_id' => null]);

        $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Child',
            'parent_id' => $root->id,
            'is_active' => true,
        ])->assertSessionHasNoErrors();
    }
}