<?php

declare(strict_types=1);

namespace Modules\Shared\DTOs;

class UploadedFileDTO
{
    public function __construct(
        public string $url,
        public string $publicId,
        public ?string $format = null,
        public ?int $width = null,
        public ?int $height = null
    ) {}

    public static function fromCloudinary(mixed $response): self
    {
        $data = (array) $response;
        
        $url = (string) ($data['secure_url'] ?? $data['url'] ?? '');

        if ($url && !str_contains($url, 'f_auto,q_auto')) {
            $url = str_replace('/upload/', '/upload/f_auto,q_auto/', $url);
        }

        return new self(
            url: $url,
            publicId: (string) ($data['public_id'] ?? ''),
            format: $data['format'] ?? null,
            width: $data['width'] ?? null,
            height: $data['height'] ?? null
        );
    }
}