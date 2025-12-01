<?php

namespace Modules\Product\Services;

use Modules\Shared\Services\BaseService; 
use Modules\Product\Domain\Repositories\ProductImageRepositoryInterface;
use Modules\Shared\Services\CloudinaryStorageService;
use Modules\Product\Domain\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;

class ProductImageService extends BaseService
{
    public function __construct(
        ProductImageRepositoryInterface $repository,
        protected CloudinaryStorageService $storage
    ) {
        parent::__construct($repository);
    }

    public function upload(Product $product, UploadedFile $file, bool $isPrimary = false): Model
    {
        if ($isPrimary) {
            $this->repository->unsetPrimary($product->id);
        }

        $result = $this->storage->upload($file, "products/{$product->uuid}");

        return $this->repository->create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(), 
            'product_id' => $product->id,
            'image_url' => $result['url'],
            'public_id' => $result['public_id'],
            'is_primary' => $isPrimary
        ]);
    }

    public function uploadMultiple(Product $product, array $files): void
    {
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($files as $index => $file) {
            $isPrimary = (!$hasPrimary && $index === 0);
            $this->upload($product, $file, $isPrimary);
        }
    }

    public function delete(string $uuid): bool
    {
        $image = $this->repository->findByUuid($uuid);
        
        if (!$image) return false;

        if ($image->public_id) {
            $this->storage->delete($image->public_id);
        }
        
        return $this->repository->delete($image);
    }
}