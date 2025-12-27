<?php

declare(strict_types=1);

namespace Modules\Shared\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Modules\Shared\Contracts\StorageServiceInterface;
use Modules\Shared\DTOs\UploadedFileDTO;
use Modules\Shared\Exceptions\BusinessException;
use Throwable;

class CloudinaryStorageService implements StorageServiceInterface
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        if (empty(config('cloudinary.cloud_name'))) {
            throw new BusinessException(500990, 'Cloudinary config is missing');
        }

        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key'    => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => ['secure' => true]
        ]);
    }

    public function upload(UploadedFile $file, string $folder = 'general'): UploadedFileDTO
    {
        try {
            $response = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                [
                    'folder' => $folder,
                    'resource_type' => 'auto',
                    'quality' => 'auto:good',
                ]
            );

            return UploadedFileDTO::fromCloudinary((array)$response);

        } catch (Throwable $e) {
            Log::error('Cloudinary upload failed: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new BusinessException(500110, 'Failed to upload media: ' . $e->getMessage());
        }
    }

    public function delete(string $fileId): bool
    {
        try {
            $this->cloudinary->uploadApi()->destroy($fileId);
            return true;
        } catch (Throwable $e) {
            Log::warning("Cloudinary delete failed: {$fileId}", ['error' => $e->getMessage()]);
            return false;
        }
    }
}