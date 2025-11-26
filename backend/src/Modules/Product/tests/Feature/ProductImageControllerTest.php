<?php

namespace Modules\Product\tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Domain\Models\Product;
class ProductImageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_image()
    {
        $this->withoutMiddleware(\Modules\Auth\Http\Middleware\JwtAuthenticate::class);

        $product = Product::factory()->create();

        $file = UploadedFile::fake()->image('photo.jpg');

        $this->mock(\Modules\Shared\Services\CloudinaryStorageService::class, function ($mock) {
            $mock->shouldReceive('upload')->andReturn(['url' => 'https://example.com/photo.jpg', 'public_id' => 'abc123']);
        });

        $response = $this->postJson("/api/products/{$product->id}/images", [
            'image' => $file
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('product_images', ['product_id' => $product->id]);
    }
}