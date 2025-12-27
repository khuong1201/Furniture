<?php

declare(strict_types=1);

namespace Modules\Media\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Modules\Media\Domain\Repositories\MediaRepositoryInterface;
use Modules\Shared\Contracts\StorageServiceInterface;
use Modules\Shared\Services\BaseService;
use Modules\Shared\Exceptions\BusinessException;

class MediaService extends BaseService
{
    public function __construct(
        MediaRepositoryInterface $repository, 
        protected StorageServiceInterface $storageService 
    ) {
        parent::__construct($repository);
    }

    public function createMedia(Model $relatedModel, UploadedFile $file, string $collection = 'default'): Model
    {
        $folder = strtolower(class_basename($relatedModel)) . 's/' . ($relatedModel->uuid ?? 'global');

        try {
            $dto = $this->storageService->upload($file, $folder);
        } catch (Throwable $e) {
            Log::error("Media Upload Failed: " . $e->getMessage());
            
            throw new BusinessException(500112);
        }

        return $this->repository->create([
            'model_type'      => get_class($relatedModel),
            'model_id'        => $relatedModel->getKey(),
            'collection_name' => $collection,
            'file_name'       => $file->getClientOriginalName(),
            'mime_type'       => $file->getMimeType(),
            'disk'            => 'cloudinary',
            'size'            => $file->getSize(),
            'url'             => $dto->url,      
            'public_id'       => $dto->publicId, 
            'custom_properties' => [
                'width'  => $dto->width,
                'height' => $dto->height,
                'format' => $dto->format
            ]
        ]);
    }

    public function deleteMedia(string $uuid): bool
    {
        $media = $this->findByUuidOrFail($uuid);

        if ($media->public_id) {
            try {
                $this->storageService->delete($media->public_id);
            } catch (Throwable $e) {
                Log::warning("Failed to delete media on cloud: {$media->public_id}");
            }
        }

        return $this->repository->delete($media);
    }
}