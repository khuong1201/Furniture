<?php

declare(strict_types=1);

namespace Modules\Shared\Services;

use Modules\Shared\Contracts\ImageStorageInterface;
use Illuminate\Http\UploadedFile;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use InvalidArgumentException;

class CloudinaryStorageService implements ImageStorageInterface
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        $config = config('cloudinary');
        if (empty($config['cloud_name']) || empty($config['api_key'])) {
             Log::warning("Cloudinary config is missing.");
        }

        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key'    => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);
    }

    public function upload(UploadedFile $file, string $folder): array
    {
        $this->validateFile($file);

        try {
            $uploaded = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                [
                    'folder' => $folder,
                    'resource_type' => 'auto',
                    'quality' => 'auto:good',
                    'fetch_format' => 'auto',
                ]
            );

            return [
                'url' => (string) $uploaded['secure_url'],
                'public_id' => (string) $uploaded['public_id'],
                'format' => $uploaded['format'] ?? null,
                'width' => $uploaded['width'] ?? null,
                'height' => $uploaded['height'] ?? null,
            ];
            
        } catch (\Exception $e) {
            Log::error('Cloudinary upload failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            
            throw new RuntimeException('Failed to upload image: ' . $e->getMessage());
        }
    }

    public function delete(?string $publicId): void
    {
        if (!$publicId) {
            return;
        }

        try {
            $this->cloudinary->uploadApi()->destroy($publicId);
        } catch (\Exception $e) {
            Log::warning('Cloudinary delete failed', [
                'public_id' => $publicId,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function validateFile(UploadedFile $file): void
    {
        $maxSize = 10 * 1024 * 1024; 
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

        if ($file->getSize() > $maxSize) {
            throw new InvalidArgumentException('File size exceeds 10MB limit.');
        }

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new InvalidArgumentException('Invalid file type. Only images are allowed.');
        }
    }
}