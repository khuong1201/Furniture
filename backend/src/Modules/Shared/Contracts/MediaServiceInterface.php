<?php

declare(strict_types=1);

namespace Modules\Shared\Contracts;

use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;

interface MediaServiceInterface
{

    public function upload(UploadedFile $file, string $folder = 'general'): array;

    public function delete(string $path): bool;
}