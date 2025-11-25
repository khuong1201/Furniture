<?php

namespace Modules\Category\tests\Feature;

use Tests\TestCase;
use Modules\Category\Domain\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_category()
    {
        $category = Category::factory()->create();
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_can_get_all_categories()
    {
        Category::factory()->count(5)->create();
        $response = $this->getJson('/api/categories');
        $response->assertStatus(200);
    }
}
