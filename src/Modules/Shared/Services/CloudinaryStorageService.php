<?php

namespace Modules\Shared\Services;

use Modules\Shared\Contracts\ImageStorageInterface;
use Illuminate\Http\UploadedFile;
use Cloudinary\Cloudinary;

class CloudinaryStorageService implements ImageStorageInterface
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
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
        $uploaded = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder,
        ]);

        return [
            'url' => $uploaded['secure_url'] ?? null,
            'public_id' => $uploaded['public_id'] ?? null,
        ];
    }

    public function delete(?string $publicId): void
    {
        if ($publicId) {
            $this->cloudinary->uploadApi()->destroy($publicId);
        }
    }
}
