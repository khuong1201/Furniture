<?php

namespace Modules\Shared\Contracts;

use Illuminate\Http\UploadedFile;

interface ImageStorageInterface
{
    public function upload(UploadedFile $file, string $folder): array;
    public function delete(?string $publicId): void;
}
