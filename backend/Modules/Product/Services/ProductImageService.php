<?php

declare(strict_types=1);

namespace Modules\Product\Services;

use Modules\Product\Domain\Models\Product;
use Modules\Product\Domain\Models\ProductImage;
use Modules\Shared\Contracts\MediaServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductImageService
{
    public function __construct(
        protected MediaServiceInterface $storage
    ) {}

    public function upload(Product $product, UploadedFile $file, bool $isPrimary = false): ProductImage
    {
        return DB::transaction(function () use ($product, $file, $isPrimary) {
            $uploadData = $this->storage->upload($file, 'products/' . $product->uuid);

            if ($isPrimary) {
                $product->images()->update(['is_primary' => false]);
            }

            return ProductImage::create([
                'product_id' => $product->id,
                'image_url'  => $uploadData['url'],
                'public_id'  => $uploadData['public_id'] ?? null,
                'is_primary' => $isPrimary,
                'sort_order' => 0
            ]);
        });
    }

    public function delete(string $uuid): void
    {
        $image = ProductImage::where('uuid', $uuid)->firstOrFail();

        if ($image->public_id) {
            $this->storage->delete($image->public_id);
        }

        $image->delete();
    }
    
    public function findByUuidOrFail(string $uuid): ProductImage
    {
        $image = ProductImage::where('uuid', $uuid)->first();
        if (!$image) {
            throw new ModelNotFoundException("Image with UUID {$uuid} not found.");
        }
        return $image;
    }
}