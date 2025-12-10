<?php

declare(strict_types=1);

namespace Modules\Media\Services;

use Modules\Media\Domain\Models\Media;
use Modules\Shared\Contracts\MediaServiceInterface;
use Modules\Shared\Contracts\ImageStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MediaService implements MediaServiceInterface
{
    public function __construct(
        protected ImageStorageInterface $storageService
    ) {}

    /**
     * IMPLEMENTATION CỦA INTERFACE MediaServiceInterface
     */
    public function upload(UploadedFile $file, string $folder = 'general'): array
    {
        return $this->storageService->upload($file, $folder);
    }

    /**
     * IMPLEMENTATION CỦA INTERFACE MediaServiceInterface
     * FIX: Đổi return type từ void -> bool để khớp Interface
     */
    public function delete(string $publicId): bool
    {
        try {
            $this->storageService->delete($publicId);
            return true;
        } catch (\Exception $e) {
            // Log error nếu cần
            return false;
        }
    }

    // --- DOMAIN LOGIC CỦA MODULE MEDIA ---

    public function createMedia(Model $model, UploadedFile $file, string $collection = 'default'): Media
    {
        $folder = strtolower(class_basename($model)) . 's/' . ($model->uuid ?? 'global');
        
        $result = $this->upload($file, $folder);

        return Media::create([
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'collection_name' => $collection,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'disk' => 'cloudinary',
            'size' => $file->getSize(),
            'url' => $result['url'],
            'public_id' => $result['public_id'] ?? null,
        ]);
    }

    public function deleteMedia(string $uuid): bool
    {
        $media = Media::where('uuid', $uuid)->firstOrFail();

        if ($media->public_id) {
            $this->delete($media->public_id);
        }

        return $media->delete();
    }
    
    public function clearCollection(Model $model, string $collection): void
    {
        $medias = $model->media()->where('collection_name', $collection)->get();
        foreach ($medias as $media) {
            $this->deleteMedia($media->uuid);
        }
    }
    
    public function findByUuidOrFail(string $uuid): Media
    {
        $media = Media::where('uuid', $uuid)->first();
        if (!$media) throw new ModelNotFoundException("Media not found");
        return $media;
    }
}