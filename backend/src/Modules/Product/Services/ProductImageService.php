<?php

declare(strict_types=1);

namespace Modules\Product\Services;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Domain\Models\ProductImage;
use Modules\Shared\Contracts\StorageServiceInterface;
use Modules\Shared\Exceptions\BusinessException;
use Throwable;

class ProductImageService
{
    public function __construct(
        protected StorageServiceInterface $storageService
    ) {}

    public function upload(Product $product, UploadedFile $file, bool $isPrimary = false): ProductImage
    {
        try {
            $dto = $this->storageService->upload($file, 'products/' . $product->uuid);
        } catch (Throwable $e) {
            Log::error("Image Upload Failed: " . $e->getMessage());
            throw new BusinessException(500112); 
        }

        return DB::transaction(function () use ($product, $dto, $isPrimary) {
            if ($isPrimary) {
                $product->images()->update(['is_primary' => false]);
            }

            return ProductImage::create([
                'product_id' => $product->id,
                'image_url'  => $dto->url,
                'public_id'  => $dto->publicId,
                'is_primary' => $isPrimary,
                'sort_order' => 0 
            ]);
        });
    }

    public function delete(string $uuid): void
    {
        $image = $this->findByUuidOrFail($uuid);

        if ($image->public_id) {
            try {
                $this->storageService->delete($image->public_id);
            } catch (Throwable $e) {
                Log::warning("Failed to delete product image on cloud: {$image->public_id}");
            }
        }

        $image->delete();
    }
    
    public function findByUuidOrFail(string $uuid): ProductImage
    {
        $image = ProductImage::where('uuid', $uuid)->first();
        if (!$image) {
            throw new BusinessException(404110); 
        }
        return $image;
    }
}