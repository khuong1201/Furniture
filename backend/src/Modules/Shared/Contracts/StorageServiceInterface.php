<?php

namespace Modules\Shared\Contracts;

use Illuminate\Http\UploadedFile;
use Modules\Shared\DTOs\UploadedFileDTO;

interface StorageServiceInterface
{
    public function upload(UploadedFile $file, string $folder): UploadedFileDTO;
    public function delete(string $publicId): bool;
}