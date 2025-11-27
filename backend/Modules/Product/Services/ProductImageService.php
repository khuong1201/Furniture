<?php

namespace Modules\Product\Services;

use Illuminate\Support\Str;
use Modules\Product\Domain\Repositories\ProductImageRepositoryInterface;
use Modules\Shared\Services\CloudinaryStorageService;
use Modules\Product\Domain\Models\Product;

class ProductImageService
{
    public function __construct(
        protected ProductImageRepositoryInterface $imageRepo,
        protected CloudinaryStorageService $cloudinary
    ) {}

    public function uploadImages(Product $product, array $files): void
    {
        $this->imageRepo->unsetPrimary($product->id);

        foreach ($files as $index => $file) {
            $uploaded = $this->cloudinary->upload($file, 'products/'.$product->uuid);
            $this->imageRepo->create([
                'uuid' => Str::uuid()->toString(),
                'product_id' => $product->id,
                'image_url' => $uploaded['url'] ?? $uploaded['secure_url'] ?? null,
                'public_id' => $uploaded['public_id'] ?? null,
                'is_primary' => $index === 0,
            ]);
        }
    }

    public function uploadSingle(Product $product, $file, bool $isPrimary = false)
    {
        if ($isPrimary) {
            $this->imageRepo->unsetPrimary($product->id);
        }

        $uploaded = $this->cloudinary->upload($file, 'products/'.$product->uuid);

        return $this->imageRepo->create([
            'uuid' => Str::uuid()->toString(),
            'product_id' => $product->id,
            'image_url' => $uploaded['url'] ?? $uploaded['secure_url'] ?? null,
            'public_id' => $uploaded['public_id'] ?? null,
            'is_primary' => $isPrimary,
        ]);
    }

    public function deleteImages(Product $product): void
    {
        $images = $product->images;
        foreach ($images as $image) {
            if ($image->public_id) {
                $this->cloudinary->deleteImage($image->public_id);
            }
            $this->imageRepo->delete($image);
        }
    }

    public function deleteByUuid(string $uuid): bool
    {
        $image = $this->imageRepo->findByUuid($uuid);
        if (! $image) return false;
        if ($image->public_id) $this->cloudinary->deleteImage($image->public_id);
        $this->imageRepo->delete($image);
        return true;
    }
}